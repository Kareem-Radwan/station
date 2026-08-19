<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ConcreteMixController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplierPurchaseController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\EquipmentFuelLogController;
use App\Http\Controllers\EquipmentMaintenanceController;
use App\Http\Controllers\RentalContractController;
use App\Http\Controllers\RentalMaintenanceController;
use App\Http\Controllers\RentalShiftController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ContributorController;
use App\Http\Controllers\ContributorPaymentController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\LandRentController;
use App\Http\Controllers\WeeklyScheduleController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\TreasuryController;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CustomerPaymentController;
use App\Http\Controllers\SupplierPaymentController;
use App\Http\Controllers\DatabaseBackupController;
use App\Http\Controllers\NeighboringStationController;
use App\Http\Controllers\AccountingController;

// ─── Authentication ───────────────────────────────────────────────────────────
Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('login', [AuthController::class, 'login'])->name('login.post');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

// ─── Protected Routes ─────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // ─── Dashboard ────────────────────────────────────────────────────────────────
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard')->middleware('role:accountant,admin');

    // ─── Customers ────────────────────────────────────────────────────────────────
    Route::resource('customers', CustomerController::class);
    Route::post('customers/{customer}/add-cement', [CustomerController::class, 'addCement'])->name('customers.add-cement');
    Route::get('customers/{customer}/payments', [CustomerController::class, 'payments'])->name('customers.payments');

    // ─── Customer Payments ────────────────────────────────────────────────────────
    Route::resource('customer-payments', CustomerPaymentController::class)->except(['show']);

    // ─── Orders ───────────────────────────────────────────────────────────────────
    Route::resource('orders', OrderController::class);
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::post('orders/mix-costs', [OrderController::class, 'mixCosts'])->name('orders.mix-costs');

    // ─── Concrete Mixes ───────────────────────────────────────────────────────────
    Route::resource('concrete-mixes', ConcreteMixController::class)->only(['index', 'create', 'store']);
    Route::patch('concrete-mixes/{concreteMix}/toggle-active', [ConcreteMixController::class, 'toggleActive'])->name('concrete-mixes.toggle-active');

    // ─── Mix Recipes ──────────────────────────────────────────────────────────────
    Route::get('mix-recipes', [\App\Http\Controllers\MixRecipeController::class, 'index'])->name('mix-recipes.index');
    Route::post('mix-recipes', [\App\Http\Controllers\MixRecipeController::class, 'store'])->name('mix-recipes.store');
    Route::put('mix-recipes/{mixRecipe}', [\App\Http\Controllers\MixRecipeController::class, 'update'])->name('mix-recipes.update');
    Route::delete('mix-recipes/{mixRecipe}', [\App\Http\Controllers\MixRecipeController::class, 'destroy'])->name('mix-recipes.destroy');
    Route::put('mix-recipes/densities/update', [\App\Http\Controllers\MixRecipeController::class, 'updateDensities'])->name('mix-recipes.densities.update');

    // ─── Suppliers ────────────────────────────────────────────────────────────────
    Route::resource('suppliers', SupplierController::class);
    Route::resource('supplier-purchases', SupplierPurchaseController::class);
    Route::post('supplier-purchases/{supplierPurchase}/upload-invoice', [SupplierPurchaseController::class, 'uploadInvoiceImage'])->name('supplier-purchases.upload-invoice');
    Route::resource('supplier-payments', SupplierPaymentController::class)->except(['show']);

    // ─── Inventory ────────────────────────────────────────────────────────────────
    // Accessible by both inventory_manager and accountant
    Route::middleware('role:inventory_manager,accountant,admin')->group(function () {
        Route::get('inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
        Route::post('inventory', [InventoryController::class, 'store'])->name('inventory.store');
        Route::get('inventory/{item}/stock-in', [InventoryController::class, 'stockInForm'])->name('inventory.stock-in');
        Route::post('inventory/{item}/stock-in', [InventoryController::class, 'stockIn'])->name('inventory.stock-in.store');
        Route::get('inventory/{item}/stock-out', [InventoryController::class, 'stockOutForm'])->name('inventory.stock-out');
        Route::post('inventory/{item}/stock-out', [InventoryController::class, 'stockOut'])->name('inventory.stock-out.store');
        Route::get('inventory/{item}/update-price', [InventoryController::class, 'updatePriceForm'])->name('inventory.update-price');
        Route::post('inventory/{item}/update-price', [InventoryController::class, 'updatePrice'])->name('inventory.update-price.store');
        Route::get('inventory/{item}/movements', [InventoryController::class, 'movements'])->name('inventory.movements');
        Route::resource('inventory', InventoryController::class)->only(['index', 'show']);
    });

    // ─── Equipment Tools Inventory (accessible to inventory_manager, accountant, admin, and rental) ──
    Route::middleware('role:inventory_manager,accountant,admin,rental')->group(function () {
        Route::get('equipment-tools', [\App\Http\Controllers\EquipmentToolController::class, 'index'])->name('equipment-tools.index');
        Route::get('equipment-tools/create', [\App\Http\Controllers\EquipmentToolController::class, 'create'])->name('equipment-tools.create');
        Route::post('equipment-tools', [\App\Http\Controllers\EquipmentToolController::class, 'store'])->name('equipment-tools.store');
        Route::get('equipment-tools/{equipmentTool}', [\App\Http\Controllers\EquipmentToolController::class, 'show'])->name('equipment-tools.show');
        Route::get('equipment-tools/{equipmentTool}/stock-in', [\App\Http\Controllers\EquipmentToolController::class, 'stockInForm'])->name('equipment-tools.stock-in');
        Route::post('equipment-tools/{equipmentTool}/stock-in', [\App\Http\Controllers\EquipmentToolController::class, 'stockIn'])->name('equipment-tools.stock-in.store');
        Route::get('equipment-tools/{equipmentTool}/stock-out', [\App\Http\Controllers\EquipmentToolController::class, 'stockOutForm'])->name('equipment-tools.stock-out');
        Route::post('equipment-tools/{equipmentTool}/stock-out', [\App\Http\Controllers\EquipmentToolController::class, 'stockOut'])->name('equipment-tools.stock-out.store');
    });

    // ─── Equipment & Rentals (accessible to accountant, admin, and rental) ──────────
    Route::middleware('role:accountant,admin,rental')->group(function () {
        // ─── Equipment ────────────────────────────────────────────────────────────────
        Route::resource('equipment', EquipmentController::class);
        Route::resource('equipment.fuel-logs', EquipmentFuelLogController::class)->shallow();
        Route::resource('equipment.maintenance', EquipmentMaintenanceController::class)->shallow();

        // ─── Rentals ──────────────────────────────────────────────────────────────
        Route::resource('rentals', RentalContractController::class);
        Route::resource('rentals.maintenance', RentalMaintenanceController::class)->shallow();
        Route::post('rentals/{rental}/shifts', [RentalShiftController::class, 'store'])->name('rentals.shifts.store');
        Route::delete('rental-shifts/{shift}', [RentalShiftController::class, 'destroy'])->name('rentals.shifts.destroy');
    });

    // ─── Accountant Only Routes ──────────────────────────────────────────────────
    // All other routes are accessible only by accountant and admin
    Route::middleware('role:accountant,admin')->group(function () {

        // ─── Database Backup ──────────────────────────────────────────────────────────
        Route::get('backup/download', [DatabaseBackupController::class, 'downloadSql'])->name('backup.download');

        // ─── Neighboring Stations ─────────────────────────────────────────────────────
        Route::resource('neighboring-stations', NeighboringStationController::class);
        Route::get('neighboring-stations/{neighboringStation}/transactions/create', [NeighboringStationController::class, 'createTransaction'])->name('neighboring-stations.create-transaction');
        Route::post('neighboring-stations/{neighboringStation}/transactions', [NeighboringStationController::class, 'storeTransaction'])->name('neighboring-stations.store-transaction');
        Route::get('neighboring-stations/{neighboringStation}/transactions/{transaction}/edit', [NeighboringStationController::class, 'editTransaction'])->name('neighboring-stations.edit-transaction');
        Route::put('neighboring-stations/{neighboringStation}/transactions/{transaction}', [NeighboringStationController::class, 'updateTransaction'])->name('neighboring-stations.update-transaction');
        Route::delete('neighboring-stations/{neighboringStation}/transactions/{transaction}', [NeighboringStationController::class, 'destroyTransaction'])->name('neighboring-stations.destroy-transaction');
        Route::post('neighboring-stations/{neighboringStation}/transactions/{transaction}/payment', [NeighboringStationController::class, 'recordPayment'])->name('neighboring-stations.record-payment');

        // ─── Employees ────────────────────────────────────────────────────────────────
        Route::resource('employees', EmployeeController::class);
        Route::post('employees/{employee}/borrows', [\App\Http\Controllers\EmployeeBorrowController::class, 'store'])->name('employees.borrows.store');
        Route::delete('employees/{employee}/borrows/{borrow}', [\App\Http\Controllers\EmployeeBorrowController::class, 'destroy'])->name('employees.borrows.destroy');
        Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('attendance/create', [AttendanceController::class, 'create'])->name('attendance.create');
        Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::get('attendance/{attendance}/edit', [AttendanceController::class, 'edit'])->name('attendance.edit');
        Route::put('attendance/{attendance}', [AttendanceController::class, 'update'])->name('attendance.update');

        // ─── Payroll ──────────────────────────────────────────────────────────────────
        Route::get('payroll', [PayrollController::class, 'index'])->name('payroll.index');
        Route::get('payroll/calculate', [PayrollController::class, 'calculateForm'])->name('payroll.calculate');
        Route::post('payroll/calculate', [PayrollController::class, 'calculate'])->name('payroll.calculate.store');
        Route::patch('payroll/{payroll}/mark-paid', [PayrollController::class, 'markPaid'])->name('payroll.mark-paid');
        Route::get('payroll/{payroll}', [PayrollController::class, 'show'])->name('payroll.show');

        // ─── Contributors ──────────────────────────────────────────────────────────────────
        Route::resource('contributors', ContributorController::class);
        Route::post('contributors/{contributor}/add-to-share', [ContributorController::class, 'addToShare'])
            ->name('contributors.add-to-share');
        Route::resource('contributor-payments', ContributorPaymentController::class)
            ->except(['show', 'index']);
        
        Route::get('/api/contributors/active', function() {
            return \App\Models\Contributor::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn($c) => ['id' => $c->id, 'name' => $c->name]);
        });

        // ─── Expenses ─────────────────────────────────────────────────────────────────
        Route::resource('expenses', ExpenseController::class);

        // ─── Land Rent ────────────────────────────────────────────────────────────────
        Route::resource('land-rent', LandRentController::class);
        Route::post('land-rent/{landRent}/pay', [LandRentController::class, 'pay'])->name('land-rent.pay');

        // ─── Weekly Schedule ──────────────────────────────────────────────────────────
        Route::resource('schedules', WeeklyScheduleController::class);
        Route::post('schedules/{schedule}/entries', [WeeklyScheduleController::class, 'addEntry'])->name('schedules.entries.store');
        Route::delete('schedules/entries/{entry}', [WeeklyScheduleController::class, 'deleteEntry'])->name('schedules.entries.destroy');

        // ─── Receipts ─────────────────────────────────────────────────────────────────
        Route::resource('receipts', ReceiptController::class);
        Route::patch('receipts/{receipt}/mark-done', [ReceiptController::class, 'markDone'])->name('receipts.mark-done');
        Route::post('receipts/{receipt}/upload-signed', [ReceiptController::class, 'uploadSignedImage'])->name('receipts.upload-signed');

        // ─── Treasury ─────────────────────────────────────────────────────────────────
        Route::get('treasury', [TreasuryController::class, 'index'])->name('treasury.index');
        Route::get('treasury/debug-data', [TreasuryController::class, 'debugData'])->name('treasury.debug');
        Route::get('treasury/create', [TreasuryController::class, 'create'])->name('treasury.create');
        Route::post('treasury', [TreasuryController::class, 'store'])->name('treasury.store');
        Route::post('treasury/recalculate', [TreasuryController::class, 'recalculateBalances'])->name('treasury.recalculate');
        Route::get('treasury/{treasury}/edit', [TreasuryController::class, 'edit'])->name('treasury.edit');
        Route::put('treasury/{treasury}', [TreasuryController::class, 'update'])->name('treasury.update');
        Route::delete('treasury/{treasury}', [TreasuryController::class, 'destroy'])->name('treasury.destroy');

        // ─── Credits ──────────────────────────────────────────────────────────────────
        Route::resource('credits', CreditController::class)->only(['index', 'create', 'store', 'show']);
        Route::patch('credits/{credit}/mark-paid', [CreditController::class, 'markPaid'])->name('credits.mark-paid');

        // ─── Reports ──────────────────────────────────────────────────────────────────
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('customer-balance', [ReportController::class, 'customerBalance'])->name('customer-balance');
            Route::get('supplier-balance', [ReportController::class, 'supplierBalance'])->name('supplier-balance');
            Route::get('inventory', [ReportController::class, 'inventoryStatus'])->name('inventory');
            Route::get('treasury', [ReportController::class, 'treasury'])->name('treasury');
            Route::get('expenses', [ReportController::class, 'expenses'])->name('expenses');
            Route::get('equipment', [ReportController::class, 'equipment'])->name('equipment');
            Route::get('payroll', [ReportController::class, 'payroll'])->name('payroll');
            Route::get('credits', [ReportController::class, 'credits'])->name('credits');
            Route::get('monthly-profit', [ReportController::class, 'monthlyProfit'])->name('monthly-profit');
            Route::get('annual-profit', [ReportController::class, 'annualProfit'])->name('annual-profit');
            Route::get('contributor-balance', [ReportController::class, 'contributorBalance'])->name('contributor-balance');
            Route::get('rental-shifts', [ReportController::class, 'rentalShifts'])->name('rental-shifts');
            Route::get('orders', [ReportController::class, 'orders'])->name('orders');
            Route::get('schedules', [ReportController::class, 'schedules'])->name('schedules');
            Route::get('general', [ReportController::class, 'generalReport'])->name('general');
            // Exports
            Route::get('export/customer-balance', [ReportController::class, 'exportCustomerBalance'])->name('export.customer-balance');
            Route::get('export/supplier-balance', [ReportController::class, 'exportSupplierBalance'])->name('export.supplier-balance');
            Route::get('export/monthly-profit', [ReportController::class, 'exportMonthlyProfit'])->name('export.monthly-profit');
            Route::get('export/annual-profit', [ReportController::class, 'exportAnnualProfit'])->name('export.annual-profit');
            Route::get('export/inventory', [ReportController::class, 'exportInventory'])->name('export.inventory');
            Route::get('export/inventory-movements/{item}', [ReportController::class, 'exportInventoryMovements'])->name('export.inventory-movements');
            Route::get('export/payroll', [ReportController::class, 'exportPayroll'])->name('export.payroll');
            Route::get('export/credits', [ReportController::class, 'exportCredits'])->name('export.credits');
            Route::get('export/expenses', [ReportController::class, 'exportExpenses'])->name('export.expenses');
            Route::get('export/equipment', [ReportController::class, 'exportEquipment'])->name('export.equipment');
            Route::get('export/treasury', [ReportController::class, 'exportTreasury'])->name('export.treasury');
            Route::get('export/contributor-balance', [ReportController::class, 'exportContributorBalance'])->name('export.contributor-balance');
            Route::get('export/rental-shifts', [ReportController::class, 'exportRentalShifts'])->name('export.rental-shifts');
            Route::get('export/orders', [ReportController::class, 'exportOrders'])->name('export.orders');
            Route::get('neighboring-stations', [ReportController::class, 'neighboringStations'])->name('neighboring-stations');
            Route::get('export/neighboring-stations', [ReportController::class, 'exportNeighboringStations'])->name('export.neighboring-stations');
            Route::get('trial-balance', [ReportController::class, 'trialBalance'])->name('trial-balance');
        });
    }); // End of accountant-only middleware group

    // ─── Accounting (Double-Entry) ─────────────────────────────────────────────
    // Reads exclusively from journal_entry_lines — never from business tables.
    Route::prefix('accounting')
        ->name('accounting.')
        ->middleware('role:admin,accountant')
        ->group(function () {
            Route::get('trial-balance',   [AccountingController::class, 'trialBalance'])->name('trial-balance');
            Route::get('general-ledger',  [AccountingController::class, 'generalLedger'])->name('general-ledger');
            Route::get('balance-sheet',   [AccountingController::class, 'balanceSheet'])->name('balance-sheet');
            Route::get('income-statement',[AccountingController::class, 'incomeStatement'])->name('income-statement');

            // Rebuild Accounting
            Route::post('rebuild', [AccountingController::class, 'rebuild'])->name('rebuild');

            // Excel Export Routes
            Route::get('trial-balance/export',    [AccountingController::class, 'exportTrialBalance'])->name('trial-balance.export');
            Route::get('general-ledger/export',   [AccountingController::class, 'exportGeneralLedger'])->name('general-ledger.export');
            Route::get('balance-sheet/export',    [AccountingController::class, 'exportBalanceSheet'])->name('balance-sheet.export');
            Route::get('income-statement/export', [AccountingController::class, 'exportIncomeStatement'])->name('income-statement.export');
            Route::get('journal-book',            [AccountingController::class, 'journalBook'])->name('journal-book');
            Route::get('journal-book/export',     [AccountingController::class, 'exportJournalBook'])->name('journal-book.export');
        });

}); // End of auth middleware group
