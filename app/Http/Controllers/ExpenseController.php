<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Services\TreasuryService;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function __construct(private TreasuryService $treasuryService) {}

    public function index(Request $request)
    {
        $expenses = Expense::query()
            ->when($request->category, fn($q,$v) => $q->where('category',$v))
            ->when($request->from_date, fn($q,$v) => $q->where('expense_date','>=',$v))
            ->when($request->to_date, fn($q,$v) => $q->where('expense_date','<=',$v))
            ->latest('expense_date')->paginate(25)->withQueryString();

        $categories = Expense::categoryList();
        $totalThisMonth = Expense::where('expense_date','>=',now()->startOfMonth()->toDateString())->sum('amount');

        return view('expenses.index', compact('expenses','categories','totalThisMonth'));
    }

    public function create()
    {
        $categories = Expense::categoryList();
        $contributors = \App\Models\Contributor::where('is_active', true)->orderBy('name')->get();
        return view('expenses.create', compact('categories', 'contributors'));
    }

    public function store(Request $request)
    {
        // Get all available categories including custom ones
        $allCategories = \App\Models\ExpenseCategory::getAllCategories();
        $allCategoryValues = array_keys($allCategories);
        $allCategoryValues[] = '__custom__';

        $validated = $request->validate([
            'category'       => 'required|in:' . implode(',', $allCategoryValues),
            'custom_category' => 'required_if:category,__custom__|nullable|string|max:255',
            'amount'         => 'required|numeric|min:0.01',
            'expense_date'   => 'required|date',
            'description'    => 'required|string',
            'payment_method' => 'required|in:cash,transfer,contributor',
            'contributor_id' => 'required_if:payment_method,contributor|nullable|exists:contributors,id',
            'notes'          => 'nullable|string',
        ]);

        // Handle custom category
        $category = $request->category;
        if ($category === '__custom__' && $request->filled('custom_category')) {
            $customCategoryName = $request->custom_category;
            
            // Create new custom category
            \App\Models\ExpenseCategory::firstOrCreate(
                ['name' => $customCategoryName],
                ['type' => 'custom', 'is_active' => true]
            );
            
            $category = $customCategoryName;
        }
        
        // Map Arabic categories to DB categories if needed
        $mapping = Expense::getArabicCategoryMapping();
        if (array_key_exists($category, $mapping)) {
            $category = $mapping[$category];
        }
        if ($category === 'salary') {
            $category = 'salaries';
        }

        \Illuminate\Support\Facades\DB::transaction(function() use ($request, $category) {
            $expense = Expense::create([
                'category'       => $category,
                'amount'         => $request->amount,
                'expense_date'   => $request->expense_date,
                'description'    => $request->description,
                'notes'          => $request->notes,
                'reference_type' => $request->payment_method === 'contributor' ? 'contributor' : null,
                'reference_id'   => $request->payment_method === 'contributor' ? $request->contributor_id : null,
                'recorded_by'    => auth()->id(),
            ]);

            $treasuryDescription = $expense->description;
            if (!empty($expense->notes)) {
                $treasuryDescription .= ' (' . $expense->notes . ')';
            }

            // If paid by contributor, create a treasury transaction and contributor payment
            if ($request->payment_method === 'contributor' && $request->contributor_id) {
                $contributor = \App\Models\Contributor::lockForUpdate()->findOrFail($request->contributor_id);
                
                // Record treasury IN from contributor using TreasuryService
                $this->treasuryService->recordIncoming(
                    amount: (float)$request->amount,
                    category: 'contributor_payment',
                    description: 'دفعة مساهم لتغطية مصروف: ' . $contributor->name,
                    referenceType: 'contributor_payment',
                    referenceId: null, // Will be updated after ContributorPayment is created
                    transactionDate: $request->expense_date
                );

                // Get the treasury transaction that was just created
                $treasury = \App\Models\TreasuryTransaction::where('category', 'contributor_payment')
                    ->where('transaction_date', $request->expense_date)
                    ->where('amount', $request->amount)
                    ->latest('id')
                    ->first();

                // Create contributor payment record
                $contributorPayment = \App\Models\ContributorPayment::create([
                    'contributor_id'           => $contributor->id,
                    'amount'                   => $request->amount,
                    'payment_date'             => $request->expense_date,
                    'payment_method'           => 'cash',
                    'notes'                    => 'دفعة لتغطية: ' . $expense->description,
                    'treasury_transaction_id'  => $treasury?->id,
                ]);

                // Update the treasury transaction reference
                if ($treasury && $contributorPayment) {
                    $treasury->update([
                        'reference_type' => 'contributor_payment',
                        'reference_id' => $contributorPayment->id,
                    ]);
                }

                // Increase contributor's share_amount (we owe them more)
                $contributor->increment('share_amount', $request->amount);
            }

            // Record the expense OUT from treasury
            $this->treasuryService->recordOutgoing(
                amount: (float)$request->amount,
                category: 'expense',
                description: $expense->category_label . ': ' . $treasuryDescription,
                transactionDate: $request->expense_date,
                referenceType: 'expense',
                referenceId: $expense->id
            );
        });

        return redirect()->route('expenses.index')->with('success', 'تم تسجيل المصروف بنجاح');
    }

    public function edit(Expense $expense)
    {
        $categories = Expense::categoryList();
        return view('expenses.edit', compact('expense','categories'));
    }

    public function update(Request $request, Expense $expense)
    {
        $arabicCategories = array_keys(Expense::getArabicCategoryMapping());
        $dbCategories = array_keys(Expense::categoryList());
        $dbCategories[] = 'salaries';
        
        // Get all available categories including custom ones
        $allCategories = \App\Models\ExpenseCategory::getAllCategories();
        $allCategoryValues = array_merge(array_keys($allCategories), ['__custom__']);

        $request->validate([
            'category'        => 'required|in:' . implode(',', $allCategoryValues),
            'custom_category' => 'required_if:category,__custom__|nullable|string|max:255',
            'amount'          => 'required|numeric|min:0.01',
            'expense_date'    => 'required|date',
            'description'     => 'required|string',
            'notes'           => 'nullable|string',
        ]);

        // Handle custom category
        $category = $request->category;
        if ($category === '__custom__' && $request->filled('custom_category')) {
            $customCategoryName = $request->custom_category;
            
            // Create new custom category
            \App\Models\ExpenseCategory::firstOrCreate(
                ['name' => $customCategoryName],
                ['type' => 'custom', 'is_active' => true]
            );
            
            $category = $customCategoryName;
        }

        // Map Arabic categories to DB categories
        $mapping = Expense::getArabicCategoryMapping();
        if (array_key_exists($category, $mapping)) {
            $category = $mapping[$category];
        }
        if ($category === 'salary') {
            $category = 'salaries';
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $expense, $category) {
            $expense->update([
                'category'     => $category,
                'amount'       => $request->amount,
                'expense_date' => $request->expense_date,
                'description'  => $request->description,
                'notes'        => $request->notes,
            ]);

            $treasuryDescription = $expense->description;
            if (!empty($expense->notes)) {
                $treasuryDescription .= ' (' . $expense->notes . ')';
            }

            // Re-record treasury transaction
            $this->treasuryService->deleteTransaction('expense', $expense->id);
            $this->treasuryService->recordOutgoing(
                amount: (float)$request->amount,
                category: 'expense',
                description: $expense->category_label . ': ' . $treasuryDescription,
                transactionDate: $request->expense_date,
                referenceType: 'expense',
                referenceId: $expense->id
            );
        });

        return redirect()->route('expenses.index')->with('success', 'تم تحديث المصروف');
    }

    public function destroy(Expense $expense)
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($expense) {
            // If this expense was paid by a contributor, reverse the share_amount increase
            if ($expense->reference_type === 'contributor' && $expense->reference_id) {
                $contributor = \App\Models\Contributor::lockForUpdate()->find($expense->reference_id);
                if ($contributor) {
                    // Decrease the contributor's share_amount (reverse the increment)
                    $contributor->decrement('share_amount', $expense->amount);
                }
                
                // Delete the contributor payment record
                \App\Models\ContributorPayment::where('notes', 'LIKE', '%' . $expense->description . '%')
                    ->where('contributor_id', $expense->reference_id)
                    ->where('amount', $expense->amount)
                    ->delete();
                    
                // Delete the treasury IN transaction from contributor
                \App\Models\TreasuryTransaction::where('category', 'contributor_payment')
                    ->where('amount', $expense->amount)
                    ->where('transaction_date', $expense->expense_date)
                    ->delete();
            }
            
            // Delete treasury transactions and recalculate balance
            $this->treasuryService->deleteTransaction('expense', $expense->id);
            
            // Delete the expense itself
            $expense->delete();
        });

        return redirect()->route('expenses.index')->with('success', 'تم حذف المصروف');
    }

    public function show(Expense $expense) { return view('expenses.show', compact('expense')); }
}
