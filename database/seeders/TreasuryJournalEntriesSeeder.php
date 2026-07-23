<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

use App\Models\TreasuryTransaction;
use App\Models\Contributor;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\ContributorPayment;
use App\Models\CustomerPayment;
use App\Models\SupplierPayment;
use App\Models\Expense;
use App\Models\User;

class TreasuryJournalEntriesSeeder extends Seeder
{
    /**
     * Entity cache to avoid repeated DB queries
     */
    private $contributors = [];
    private $customers = [];
    private $suppliers = [];
    private $currentBalance = 0.00;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting Treasury Journal Entries Import...');

        // Load existing entities into memory
        $this->loadExistingEntities();

        // Read the Excel file
        $filePath = storage_path('app/قيود.xlsx');
        
        if (!file_exists($filePath)) {
            $this->command->error("File not found: $filePath");
            $this->command->info("Please copy قيود.xlsx to: " . storage_path('app/'));
            return;
        }

        $sheets = Excel::toArray(
            new class implements \Maatwebsite\Excel\Concerns\WithCalculatedFormulas {},
            $filePath
        );

        if (empty($sheets)) {
            $this->command->error('No sheets found in the Excel file');
            return;
        }

        // Use the first sheet (القيود sheet)
        $rows = $sheets[0];

        if (empty($rows)) {
            $this->command->error('Sheet is empty');
            return;
        }

        // Find header row (usually contains: التاريخ, البيان, له (دائن), منه (مدين), الرصيد, etc.)
        $headerRowIndex = $this->findHeaderRow($rows);
        
        if ($headerRowIndex === null) {
            $this->command->error('Could not find header row');
            return;
        }

        $header = $this->normalizeHeader($rows[$headerRowIndex]);
        $this->command->info("Found header at row $headerRowIndex: " . implode(', ', array_filter($header)));

        // Process data rows
        $dataRows = array_slice($rows, $headerRowIndex + 1);
        $processedCount = 0;
        $errorCount = 0;

        DB::transaction(function () use ($dataRows, $header, &$processedCount, &$errorCount) {
            foreach ($dataRows as $index => $row) {
                // Skip empty rows
                if ($this->isEmptyRow($row)) {
                    continue;
                }

                try {
                    $rowData = $this->parseRow($row, $header);
                    
                    if ($this->shouldProcessRow($rowData)) {
                        $this->processJournalEntry($rowData, $index + 1);
                        $processedCount++;
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    $this->command->warn("Error processing row " . ($index + 1) . ": " . $e->getMessage());
                }
            }
        });

        $this->command->info("✓ Import completed!");
        $this->command->info("Processed: $processedCount entries");
        if ($errorCount > 0) {
            $this->command->warn("Errors: $errorCount entries");
        }
    }

    /**
     * Load existing entities from database
     */
    private function loadExistingEntities(): void
    {
        $this->contributors = Contributor::pluck('id', 'name')->toArray();
        $this->customers = Customer::pluck('id', 'name')->toArray();
        $this->suppliers = Supplier::pluck('id', 'name')->toArray();

        $this->command->info('Loaded entities:');
        $this->command->info('  Contributors: ' . count($this->contributors));
        $this->command->info('  Customers: ' . count($this->customers));
        $this->command->info('  Suppliers: ' . count($this->suppliers));
    }

    /**
     * Find the header row in the Excel sheet
     */
    private function findHeaderRow(array $rows): ?int
    {
        foreach ($rows as $index => $row) {
            // Look for key columns
            $rowText = implode(' ', array_map('strval', $row));
            
            // Check for typical header keywords
            if (
                (stripos($rowText, 'التاريخ') !== false || stripos($rowText, 'تاريخ') !== false) &&
                (stripos($rowText, 'البيان') !== false || stripos($rowText, 'بيان') !== false)
            ) {
                return $index;
            }
        }
        
        return 0; // Default to first row if not found
    }

    /**
     * Normalize header column names
     */
    private function normalizeHeader(array $headerRow): array
    {
        return array_map(function ($col) {
            $col = trim((string) $col);
            $col = preg_replace('/\s+/', ' ', $col);
            return $col;
        }, $headerRow);
    }

    /**
     * Check if row is empty
     */
    private function isEmptyRow(array $row): bool
    {
        return empty(array_filter($row, function ($cell) {
            return !is_null($cell) && trim((string) $cell) !== '';
        }));
    }

    /**
     * Parse a row into structured data
     */
    private function parseRow(array $row, array $header): array
    {
        // Combine header with row data
        $data = [];
        foreach ($header as $index => $columnName) {
            $data[$columnName] = $row[$index] ?? null;
        }

        // Extract key fields with common variations
        return [
            'date' => $this->extractField($data, ['التاريخ', 'تاريخ', 'Date']),
            'description' => $this->extractField($data, ['البيان', 'بيان', 'الوصف', 'وصف', 'Description']),
            'credit' => $this->extractField($data, ['له', 'دائن', 'Credit', 'له (دائن)']),
            'debit' => $this->extractField($data, ['منه', 'مدين', 'Debit', 'منه (مدين)']),
            'balance' => $this->extractField($data, ['الرصيد', 'رصيد', 'Balance']),
            'notes' => $this->extractField($data, ['ملاحظات', 'Notes']),
            'raw' => $data,
        ];
    }

    /**
     * Extract field value from data using multiple possible keys
     */
    private function extractField(array $data, array $possibleKeys): mixed
    {
        foreach ($possibleKeys as $key) {
            if (isset($data[$key])) {
                return $data[$key];
            }
        }
        return null;
    }

    /**
     * Check if row should be processed
     */
    private function shouldProcessRow(array $rowData): bool
    {
        // Must have either credit or debit amount
        $credit = $this->parseAmount($rowData['credit']);
        $debit = $this->parseAmount($rowData['debit']);
        
        return ($credit > 0 || $debit > 0) && !empty($rowData['description']);
    }

    /**
     * Process a journal entry and create appropriate records
     */
    private function processJournalEntry(array $rowData, int $rowNumber): void
    {
        $date = $this->parseDate($rowData['date']);
        $description = $this->cleanText($rowData['description']);
        $credit = $this->parseAmount($rowData['credit']);
        $debit = $this->parseAmount($rowData['debit']);
        $notes = $this->cleanText($rowData['notes']);

        // Determine transaction type and amount
        $type = 'out'; // default
        $amount = 0;

        if ($credit > 0) {
            $type = 'in';
            $amount = $credit;
        } elseif ($debit > 0) {
            $type = 'out';
            $amount = $debit;
        }

        if ($amount <= 0) {
            return;
        }

        // Detect category and entity from description
        $category = $this->detectCategory($description);
        $entity = $this->extractEntityFromDescription($description, $category);

        // Update balance
        if ($type === 'in') {
            $this->currentBalance += $amount;
        } else {
            $this->currentBalance -= $amount;
        }

        // Create treasury transaction
        $transaction = TreasuryTransaction::create([
            'type' => $type,
            'category' => $category,
            'amount' => $amount,
            'balance_after' => $this->currentBalance,
            'transaction_date' => $date,
            'description' => $description,
            'reference_type' => null,
            'reference_id' => null,
            'recorded_by' => 1, // Default admin user
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create related payment records if entity is identified
        if ($entity) {
            $this->createRelatedPaymentRecord($entity, $type, $amount, $date, $description, $transaction->id);
        }

        $this->command->line("Row $rowNumber: $type $amount - $description");
    }

    /**
     * Detect transaction category from description
     */
    private function detectCategory(string $description): string
    {
        $desc = mb_strtolower($description);

        // Common category keywords
        $categories = [
            'customer_payment' => ['من عميل', 'تحصيل من', 'دفعة من عميل', 'سداد من عميل'],
            'supplier_payment' => ['لمورد', 'سداد لمورد', 'دفعة لمورد', 'للمورد'],
            'contributor_payment' => ['لمساهم', 'سداد لمساهم', 'دفعة لمساهم', 'للمساهم'],
            'salary' => ['راتب', 'رواتب', 'أجور', 'مرتب'],
            'fuel' => ['وقود', 'ديزل', 'بنزين', 'سولار'],
            'maintenance' => ['صيانة', 'إصلاح', 'قطع غيار'],
            'rent' => ['إيجار', 'ايجار'],
            'utilities' => ['كهرباء', 'مياه', 'خدمات'],
            'expense' => ['مصروف', 'مصاريف'],
        ];

        foreach ($categories as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (stripos($desc, $keyword) !== false) {
                    return $category;
                }
            }
        }

        return 'other';
    }

    /**
     * Extract entity (contributor, customer, supplier) from description
     */
    private function extractEntityFromDescription(string $description, string $category): ?array
    {
        // Try to match against existing entities
        foreach ($this->contributors as $name => $id) {
            if (stripos($description, $name) !== false) {
                return ['type' => 'contributor', 'id' => $id, 'name' => $name];
            }
        }

        foreach ($this->customers as $name => $id) {
            if (stripos($description, $name) !== false) {
                return ['type' => 'customer', 'id' => $id, 'name' => $name];
            }
        }

        foreach ($this->suppliers as $name => $id) {
            if (stripos($description, $name) !== false) {
                return ['type' => 'supplier', 'id' => $id, 'name' => $name];
            }
        }

        // Try to extract name and create if needed
        if (in_array($category, ['customer_payment', 'supplier_payment', 'contributor_payment'])) {
            $extractedName = $this->extractNameFromDescription($description, $category);
            if ($extractedName) {
                return $this->createEntityIfNeeded($extractedName, $category);
            }
        }

        return null;
    }

    /**
     * Extract entity name from description using patterns
     */
    private function extractNameFromDescription(string $description, string $category): ?string
    {
        $patterns = [
            'customer_payment' => ['/من\s+عميل\s+(.+?)(?:\s|$)/u', '/تحصيل\s+من\s+(.+?)(?:\s|$)/u'],
            'supplier_payment' => ['/لمورد\s+(.+?)(?:\s|$)/u', '/للمورد\s+(.+?)(?:\s|$)/u'],
            'contributor_payment' => ['/لمساهم\s+(.+?)(?:\s|$)/u', '/للمساهم\s+(.+?)(?:\s|$)/u'],
        ];

        if (isset($patterns[$category])) {
            foreach ($patterns[$category] as $pattern) {
                if (preg_match($pattern, $description, $matches)) {
                    return $this->cleanText($matches[1]);
                }
            }
        }

        return null;
    }

    /**
     * Create entity if it doesn't exist
     */
    private function createEntityIfNeeded(string $name, string $category): ?array
    {
        $name = $this->cleanText($name);
        
        if (empty($name)) {
            return null;
        }

        $type = null;
        $id = null;

        if (str_contains($category, 'customer')) {
            if (!isset($this->customers[$name])) {
                $customer = Customer::create([
                    'name' => $name,
                    'phone' => null,
                    'address' => null,
                    'cement_balance' => 0,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->customers[$name] = $customer->id;
                $this->command->info("  Created new customer: $name");
            }
            $type = 'customer';
            $id = $this->customers[$name];

        } elseif (str_contains($category, 'supplier')) {
            if (!isset($this->suppliers[$name])) {
                $supplier = Supplier::create([
                    'name' => $name,
                    'phone' => null,
                    'address' => null,
                    'materials' => json_encode([]),
                    'payment_type' => 'credit',
                    'balance' => 0,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->suppliers[$name] = $supplier->id;
                $this->command->info("  Created new supplier: $name");
            }
            $type = 'supplier';
            $id = $this->suppliers[$name];

        } elseif (str_contains($category, 'contributor')) {
            if (!isset($this->contributors[$name])) {
                $contributor = Contributor::create([
                    'name' => $name,
                    'phone' => null,
                    'share_percentage' => 0,
                    'share_amount' => 0,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->contributors[$name] = $contributor->id;
                $this->command->info("  Created new contributor: $name");
            }
            $type = 'contributor';
            $id = $this->contributors[$name];
        }

        return $type ? ['type' => $type, 'id' => $id, 'name' => $name] : null;
    }

    /**
     * Create related payment record
     */
    private function createRelatedPaymentRecord(
        array $entity,
        string $type,
        float $amount,
        string $date,
        string $description,
        int $treasuryTransactionId
    ): void {
        try {
            switch ($entity['type']) {
                case 'customer':
                    if ($type === 'in') {
                        CustomerPayment::create([
                            'customer_id' => $entity['id'],
                            'amount' => $amount,
                            'payment_date' => $date,
                            'payment_method' => 'cash',
                            'notes' => $description,
                            'treasury_transaction_id' => $treasuryTransactionId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                    break;

                case 'supplier':
                    if ($type === 'out') {
                        SupplierPayment::create([
                            'supplier_id' => $entity['id'],
                            'amount' => $amount,
                            'payment_date' => $date,
                            'payment_method' => 'cash',
                            'notes' => $description,
                            'treasury_transaction_id' => $treasuryTransactionId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                    break;

                case 'contributor':
                    if ($type === 'out') {
                        ContributorPayment::create([
                            'contributor_id' => $entity['id'],
                            'amount' => $amount,
                            'payment_date' => $date,
                            'payment_method' => 'cash',
                            'notes' => $description,
                            'treasury_transaction_id' => $treasuryTransactionId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                    break;
            }
        } catch (\Exception $e) {
            $this->command->warn("  Could not create payment record: " . $e->getMessage());
        }
    }

    /**
     * Parse amount from various formats
     */
    private function parseAmount($value): float
    {
        if (is_null($value) || $value === '') {
            return 0.0;
        }

        // Remove thousands separators and convert
        $value = str_replace([',', ' '], '', (string) $value);
        
        return (float) $value;
    }

    /**
     * Parse date from various formats
     */
    private function parseDate($date): string
    {
        if (!$date) {
            return now()->format('Y-m-d');
        }

        $date = trim((string) $date);

        // Handle Excel serial date numbers
        if (is_numeric($date) && strlen($date) <= 6) {
            try {
                return Carbon::create(1900, 1, 1)
                    ->addDays((int) $date - 2)
                    ->format('Y-m-d');
            } catch (\Exception $e) {
                return now()->format('Y-m-d');
            }
        }

        // Try various date formats
        $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'm/d/Y', 'Y/m/d'];
        
        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $date)->format('Y-m-d');
            } catch (\Exception $e) {
                continue;
            }
        }

        return now()->format('Y-m-d');
    }

    /**
     * Clean text
     */
    private function cleanText($value): string
    {
        if (is_null($value)) {
            return '';
        }
        
        return preg_replace('/\s+/', ' ', trim((string) $value));
    }
}
