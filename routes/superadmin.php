<?php

use App\Http\Controllers\SuperAdmin\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdmin\TenantController;

Route::prefix('super-admin')->name('superadmin.')->group(function () {

    // Login - accesible sin autenticación
    Route::middleware('guest:super_admin')->group(function () {
        Route::view('/login', 'superadmin.login')->name('login');
        Route::post('/login', [AuthController::class, 'store'])->name('login.store');
    });

    // Rutas protegidas - requieren estar autenticado como super_admin
    Route::middleware('auth:super_admin')->group(function () {
        Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
        Route::get('/clientes', function () { return view('superadmin.index');})->name('clientes.index');
        Route::resource('inquilinos', TenantController::Class);
        Route::get('TotalInquilinos', [TenantController::Class,'totalClientes']);
        Route::get('InquilinosActivos', [TenantController::Class,'totalActivos']);
        Route::get('InquilinosSuspendidos', [TenantController::Class,'totalSuspendidos']);
        
        // Aquí agregaremos las rutas de gestión de tenants en el siguiente paso
        // Route::get('/tenants', [TenantController::class, 'index'])->name('tenants.index');
        
    });

    
       

});