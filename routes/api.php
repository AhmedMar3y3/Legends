<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\TaskController;

//public routes
Route::post('/register-manager', [AuthController::class, 'registerManager']);
Route::post('/register-employee', [AuthController::class, 'registerEmployee']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// private routes
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/profile', [AuthController::class, 'getProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::resource('/shifts', ShiftController::class)->except(['update', 'destroy', 'store']);
    Route::post('/shifts', [ShiftController::class, 'store'])->middleware('manager');
    Route::put('/shifts/{shift}', [ShiftController::class, 'update'])->middleware('manager');
    Route::delete('/shifts/{shift}', [ShiftController::class, 'destroy'])->middleware('manager');
    Route::get('/shifts/{shift}/shift-employees', [ShiftController::class, 'shiftEmployees'])->middleware('manager');
    Route::resource('/tasks', TaskController::class)->except(['update', 'destroy', 'store']);
    Route::post('/tasks', [TaskController::class, 'store'])->middleware('manager');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->middleware('manager');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->middleware('manager');
    Route::post('/employees-by-department', [DepartmentController::class, 'getEmployeesByDepartment'])->middleware('manager');
    Route::get('/employees-by-department/{employee_id}', [DepartmentController::class, 'getEmployeeDetails'])->middleware('manager');
    Route::get('/departments', [DepartmentController::class, 'getAllDepartments'])->middleware('manager');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
