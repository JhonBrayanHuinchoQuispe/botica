<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\ProductoApiController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Rutas públicas de autenticación móvil
Route::prefix('mobile')->group(function () {
    Route::post('/login', [MobileAuthController::class, 'login']);
    Route::post('/verify-token', [MobileAuthController::class, 'verifyToken']);
    
    // Rutas de recuperación de contraseña
    Route::post('/forgot-password', [MobileAuthController::class, 'forgotPassword']);
    Route::post('/verify-reset-code', [MobileAuthController::class, 'verifyResetCode']);
    Route::post('/reset-password', [MobileAuthController::class, 'resetPasswordWithCode']);
    Route::post('/resend-reset-code', [MobileAuthController::class, 'resendResetCode']);
});

// Rutas protegidas de autenticación móvil
Route::middleware('auth:sanctum')->prefix('mobile')->group(function () {
    Route::post('/logout', [MobileAuthController::class, 'logout']);
    Route::get('/me', [MobileAuthController::class, 'me']);
    Route::post('/change-password', [MobileAuthController::class, 'changePassword']);
    Route::post('/update-profile', [MobileAuthController::class, 'updateProfile']);
    
    // Rutas de dashboard
    Route::get('/dashboard', [DashboardController::class, 'getDashboardData']);
    
    // Rutas de productos
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductoApiController::class, 'index']);
        Route::get('/search', [ProductoApiController::class, 'searchByName']);
        Route::get('/critical', [ProductoApiController::class, 'getCriticalProducts']);
        Route::get('/low-stock', [ProductoApiController::class, 'lowStock']);
        Route::get('/expiring', [ProductoApiController::class, 'expiring']);
        Route::get('/expired', [ProductoApiController::class, 'expired']);
        Route::get('/alerts', [ProductoApiController::class, 'alerts']);
    });
    
    // Rutas de notificaciones
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread', [NotificationController::class, 'unread']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
        Route::get('/type/{type}', [NotificationController::class, 'getByType']);
        Route::get('/{id}', [ProductoApiController::class, 'show']);
        Route::get('/{id}/detalles', [ProductoApiController::class, 'getDetallesConLotes']);
        Route::post('/', [ProductoApiController::class, 'store']);
        Route::put('/{id}', [ProductoApiController::class, 'update']);
        Route::delete('/{id}', [ProductoApiController::class, 'destroy']);
        Route::get('/barcode/{barcode}', [ProductoApiController::class, 'findByBarcode']);
        Route::post('/{id}/add-stock', [ProductoApiController::class, 'addStock']);
        Route::post('/{id}/adjust-stock', [ProductoApiController::class, 'adjustStock']);
    });
});

// Rutas públicas de productos (para interfaz web)
Route::get('/productos/{id}/detalles', [ProductoApiController::class, 'getDetallesConLotes']);

// Rutas públicas de notificaciones (para interfaz web)
Route::prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index']);
    Route::get('/unread', [NotificationController::class, 'unread']);
    Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/{id}/mark-read', [NotificationController::class, 'markAsRead']);
    Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::get('/type/{type}', [NotificationController::class, 'getByType']);
});

// Rutas de punto de venta
Route::prefix('punto-venta')->group(function () {
    Route::get('/buscar-alternativas', [\App\Http\Controllers\PuntoVenta\PuntoVentaController::class, 'buscarAlternativas']);
});

// Ruta de prueba para verificar que la API funciona
Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'API funcionando correctamente',
        'timestamp' => now()
    ]);
});