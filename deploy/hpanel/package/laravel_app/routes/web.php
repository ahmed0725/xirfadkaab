<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\AdditionalFeeController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InventoryItemController;
use App\Http\Controllers\CourseTypeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('role:admin,user')->group(function () {
        Route::resource('students', StudentController::class)->except(['destroy']);
        Route::get('classes/create', [ClassController::class, 'create'])->name('classes.create');
        Route::post('classes', [ClassController::class, 'store'])->name('classes.store');
        Route::get('classes/{class}/edit', [ClassController::class, 'edit'])->name('classes.edit');
        Route::put('classes/{class}', [ClassController::class, 'update'])->name('classes.update');
        Route::resource('subjects', SubjectController::class)->except(['destroy']);
        Route::get('fees', [FeeController::class, 'index'])->name('fees.index');
        Route::get('fees/{fee}/receipt', [FeeController::class, 'receipt'])->name('fees.receipt');
        Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('expenses/{expense}', [ExpenseController::class, 'show'])->whereNumber('expense')->name('expenses.show');
        Route::get('inventory-items', [InventoryItemController::class, 'index'])->name('inventory-items.index');
        Route::get('inventory-items/{inventory_item}', [InventoryItemController::class, 'show'])->whereNumber('inventory_item')->name('inventory-items.show');
        Route::get('exams/create', [ExamController::class, 'create'])->name('exams.create');
        Route::post('exams', [ExamController::class, 'store'])->name('exams.store');
        Route::get('exams/{exam}/edit', [ExamController::class, 'edit'])->name('exams.edit');
        Route::patch('exams/{exam}', [ExamController::class, 'update'])->name('exams.update');
        Route::delete('exams/{exam}', [ExamController::class, 'destroy'])->name('exams.destroy');
    });

    Route::middleware('role:admin,user,teacher')->group(function () {
        Route::get('classes', [ClassController::class, 'index'])->name('classes.index');
        Route::get('classes/{class}', [ClassController::class, 'show'])->name('classes.show');
    });

    Route::middleware('role:admin,user,teacher')->group(function () {
        Route::get('exams', [ExamController::class, 'index'])->name('exams.index');
        Route::get('exams/{exam}', [ExamController::class, 'show'])->name('exams.show');
        Route::post('exams/{exam}/results', [ExamController::class, 'storeResults'])->name('exams.results.store');
    });

    Route::middleware('role:admin,user,teacher')->group(function () {
        Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('attendance/class-data', [AttendanceController::class, 'classData'])->name('attendance.class-data');
        Route::get('attendance/mark', [AttendanceController::class, 'create'])->name('attendance.create');
        Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::get('attendance/{attendance}/edit', [AttendanceController::class, 'edit'])->name('attendance.edit');
        Route::patch('attendance/{attendance}', [AttendanceController::class, 'update'])->name('attendance.update');
    });

    Route::middleware('role:admin')->group(function () {
        Route::resource('course-types', CourseTypeController::class)->except(['show']);
        Route::resource('users', UserController::class)->except(['show']);
        Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::post('settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::delete('students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
        Route::delete('classes/{class}', [ClassController::class, 'destroy'])->name('classes.destroy');
        Route::delete('subjects/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');
        Route::get('fees/create', [FeeController::class, 'create'])->name('fees.create');
        Route::post('fees', [FeeController::class, 'store'])->name('fees.store');
        Route::get('fees/{fee}/edit', [FeeController::class, 'edit'])->name('fees.edit');
        Route::patch('fees/{fee}', [FeeController::class, 'update'])->name('fees.update');
        Route::delete('fees/{fee}', [FeeController::class, 'destroy'])->name('fees.destroy');
        Route::get('additional-fees/create', [AdditionalFeeController::class, 'create'])->name('additional-fees.create');
        Route::post('additional-fees', [AdditionalFeeController::class, 'store'])->name('additional-fees.store');
        Route::get('additional-fees/{additional_fee_charge}/edit', [AdditionalFeeController::class, 'edit'])->name('additional-fees.edit');
        Route::patch('additional-fees/{additional_fee_charge}', [AdditionalFeeController::class, 'update'])->name('additional-fees.update');
        Route::delete('additional-fees/{additional_fee_charge}', [AdditionalFeeController::class, 'destroy'])->name('additional-fees.destroy');
        Route::get('additional-fees/{additional_fee_charge}/receipt', [AdditionalFeeController::class, 'receipt'])->name('additional-fees.receipt');
        Route::get('expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('expenses', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::get('expenses/{expense}/edit', [ExpenseController::class, 'edit'])->whereNumber('expense')->name('expenses.edit');
        Route::patch('expenses/{expense}', [ExpenseController::class, 'update'])->whereNumber('expense')->name('expenses.update');
        Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy'])->whereNumber('expense')->name('expenses.destroy');
        Route::get('inventory-items/create', [InventoryItemController::class, 'create'])->name('inventory-items.create');
        Route::post('inventory-items', [InventoryItemController::class, 'store'])->name('inventory-items.store');
        Route::get('inventory-items/{inventory_item}/edit', [InventoryItemController::class, 'edit'])->whereNumber('inventory_item')->name('inventory-items.edit');
        Route::patch('inventory-items/{inventory_item}', [InventoryItemController::class, 'update'])->whereNumber('inventory_item')->name('inventory-items.update');
        Route::delete('inventory-items/{inventory_item}', [InventoryItemController::class, 'destroy'])->whereNumber('inventory_item')->name('inventory-items.destroy');
        Route::delete('attendance/{attendance}', [AttendanceController::class, 'destroy'])->name('attendance.destroy');
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/print', [ReportController::class, 'print'])->name('reports.print');
        Route::get('reports/pdf', [ReportController::class, 'pdf'])->name('reports.pdf');
    });
});

require __DIR__.'/auth.php';
