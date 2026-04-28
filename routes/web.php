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
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('role:admin,user')->group(function () {
        Route::resource('students', StudentController::class)->except(['destroy']);
        Route::resource('classes', ClassController::class)->except(['destroy']);
        Route::resource('subjects', SubjectController::class)->except(['destroy']);
        Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('attendance/mark', [AttendanceController::class, 'create'])->name('attendance.create');
        Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::get('attendance/{attendance}/edit', [AttendanceController::class, 'edit'])->name('attendance.edit');
        Route::patch('attendance/{attendance}', [AttendanceController::class, 'update'])->name('attendance.update');
        Route::get('fees', [FeeController::class, 'index'])->name('fees.index');
        Route::get('fees/{fee}/receipt', [FeeController::class, 'receipt'])->name('fees.receipt');
    });

    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::delete('students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
        Route::delete('classes/{class}', [ClassController::class, 'destroy'])->name('classes.destroy');
        Route::delete('subjects/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');
        Route::get('fees/create', [FeeController::class, 'create'])->name('fees.create');
        Route::post('fees', [FeeController::class, 'store'])->name('fees.store');
        Route::get('fees/{fee}/edit', [FeeController::class, 'edit'])->name('fees.edit');
        Route::patch('fees/{fee}', [FeeController::class, 'update'])->name('fees.update');
        Route::delete('fees/{fee}', [FeeController::class, 'destroy'])->name('fees.destroy');
        Route::delete('attendance/{attendance}', [AttendanceController::class, 'destroy'])->name('attendance.destroy');
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/print', [ReportController::class, 'print'])->name('reports.print');
        Route::get('reports/pdf', [ReportController::class, 'pdf'])->name('reports.pdf');
    });
});

require __DIR__.'/auth.php';
