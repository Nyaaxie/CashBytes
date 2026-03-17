<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\BankTransferController;
use App\Http\Controllers\SavingsController;
use App\Http\Controllers\LoadController;
use App\Http\Controllers\BillsController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ProfileController;

// ── PUBLIC ───────────────────────────────────────────────────────
Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : view('welcome');
})->name('welcome');

// ── GUEST ONLY ───────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',     [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',    [AuthController::class, 'login']);
    Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// ── AUTHENTICATED ────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── CashBytes Fund Transfer
    Route::get('/transfer',                          [TransferController::class, 'index'])->name('transfer.index');
    Route::post('/transfer',                         [TransferController::class, 'send'])->name('transfer.send');
    Route::get('/transfer/receipt/{transactionId}',  [TransferController::class, 'receipt'])->name('transfer.receipt');

     // ── Bank Transfer — receipt & banks BEFORE dynamic routes
    Route::get('/transfer/bank',                          [BankTransferController::class, 'index'])->name('transfer.bank');
    Route::post('/transfer/bank/account',                 [BankTransferController::class, 'addAccount'])->name('transfer.bank.addAccount');
    Route::delete('/transfer/bank/account/{id}',          [BankTransferController::class, 'deleteAccount'])->name('transfer.bank.deleteAccount');
    Route::patch('/transfer/bank/account/{id}/default',   [BankTransferController::class, 'setDefault'])->name('transfer.bank.setDefault');
    Route::post('/transfer/bank/confirm',                 [BankTransferController::class, 'confirm'])->name('transfer.bank.confirm');
    Route::post('/transfer/bank/send',                    [BankTransferController::class, 'send'])->name('transfer.bank.send');
    Route::get('/transfer/bank/receipt/{transactionId}',  [BankTransferController::class, 'receipt'])->name('transfer.bank.receipt');

    // ── Savings
    Route::get('/savings',                           [SavingsController::class, 'index'])->name('savings.index');
    Route::get('/savings/create',                    [SavingsController::class, 'create'])->name('savings.create');
    Route::post('/savings',                          [SavingsController::class, 'store'])->name('savings.store');
    Route::get('/savings/receipt/{transactionId}',   [SavingsController::class, 'receipt'])->name('savings.receipt');
    Route::get('/savings/{goalId}',                  [SavingsController::class, 'show'])->name('savings.show');
    Route::post('/savings/{goalId}/allocate',        [SavingsController::class, 'allocate'])->name('savings.allocate');
    Route::post('/savings/{goalId}/withdraw',        [SavingsController::class, 'withdraw'])->name('savings.withdraw');
    Route::delete('/savings/{goalId}',               [SavingsController::class, 'destroy'])->name('savings.destroy');

    // ── Buy Load
    Route::get('/load',                              [LoadController::class, 'index'])->name('load.index');
    Route::post('/load',                             [LoadController::class, 'buy'])->name('load.buy');
    Route::get('/load/receipt/{transactionId}',      [LoadController::class, 'receipt'])->name('load.receipt');

    // ── Pay Bills
    Route::get('/bills',                             [BillsController::class, 'index'])->name('bills.index');
    Route::get('/bills/receipt/{transactionId}',     [BillsController::class, 'receipt'])->name('bills.receipt');
    Route::get('/bills/{billerId}',                  [BillsController::class, 'pay'])->name('bills.pay');
    Route::post('/bills/{billerId}',                 [BillsController::class, 'process'])->name('bills.process');

    // ── Transactions
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');

    // ── Profile
    Route::get('/profile',           [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile',          [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});