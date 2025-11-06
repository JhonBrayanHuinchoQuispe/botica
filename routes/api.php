<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\ProductoApiController;
use App\Http\Controllers\Api\FacturacionController;
use App\Http\Controllers\Api\PdfController;
use App\Http\Controllers\Api\ConfiguracionController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\PosOptimizadoController;
use App\Http\Controllers\ImageOptimizationController;

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
});

// Rutas de búsqueda instantánea (con autenticación)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/buscar-productos', function (Request $request) {
        $termino = $request->get('q', '');
        $limit = min($request->get('limit', 20), 50); // Máximo 50 resultados
        
        $productos = \App\Services\CacheService::buscarProductos($termino, $limit);
        
        return response()->json([
            'success' => true,
            'data' => $productos,
            'count' => $productos->count()
        ]);
    });
    
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
    Route::post('/procesar-venta', [\App\Http\Controllers\PuntoVenta\PuntoVentaController::class, 'procesarVenta']);
});

// Rutas de facturación electrónica
Route::prefix('facturacion')->middleware('auth:sanctum')->group(function () {
    // Generar documentos electrónicos
    Route::post('/factura', [FacturacionController::class, 'generarFactura']);
    Route::post('/boleta', [FacturacionController::class, 'generarBoleta']);
    Route::post('/ticket', [FacturacionController::class, 'generarTicket']);
    
    // Consultar estado de documentos
    Route::post('/consultar-estado', [FacturacionController::class, 'consultarEstado']);
    
    // Listar documentos
    Route::get('/facturas', [FacturacionController::class, 'listarFacturas']);
    Route::get('/boletas', [FacturacionController::class, 'listarBoletas']);
    
    // Descargar PDF
    Route::post('/descargar-pdf', [FacturacionController::class, 'descargarPdf']);
});

// Rutas de generación de PDFs
Route::prefix('pdf')->middleware('auth:sanctum')->group(function () {
    // Generar PDFs de facturas
    Route::get('/factura/{id}/a4', [PdfController::class, 'generarFacturaPdfA4']);
    Route::get('/factura/{id}/ticket', [PdfController::class, 'generarFacturaPdfTicket']);
    Route::get('/factura/{id}/descargar', [PdfController::class, 'descargarFacturaPdf']);
    Route::post('/factura/{id}/guardar', [PdfController::class, 'guardarFacturaPdf']);
    
    // Generar PDFs de boletas
    Route::get('/boleta/{id}/a4', [PdfController::class, 'generarBoletaPdfA4']);
    Route::get('/boleta/{id}/ticket', [PdfController::class, 'generarBoletaPdfTicket']);
    Route::get('/boleta/{id}/descargar', [PdfController::class, 'descargarBoletaPdf']);
    Route::post('/boleta/{id}/guardar', [PdfController::class, 'guardarBoletaPdf']);
    
    // Gestión de PDFs almacenados
    Route::get('/listar', [PdfController::class, 'listarPdfs']);
    Route::delete('/eliminar', [PdfController::class, 'eliminarPdf']);
});

// Rutas de configuración SUNAT
Route::prefix('configuracion')->middleware('auth:sanctum')->group(function () {
    // Configuración de empresa
    Route::get('/empresa', [ConfiguracionController::class, 'obtenerEmpresa']);
    Route::put('/empresa', [ConfiguracionController::class, 'actualizarEmpresa']);
    Route::post('/empresa/certificado', [ConfiguracionController::class, 'subirCertificado']);
    
    // Gestión de sucursales
    Route::get('/sucursales', [ConfiguracionController::class, 'listarSucursales']);
    Route::post('/sucursales', [ConfiguracionController::class, 'crearSucursal']);
    
    // Gestión de correlativos
    Route::get('/correlativos', [ConfiguracionController::class, 'listarCorrelativos']);
    Route::post('/correlativos', [ConfiguracionController::class, 'crearCorrelativo']);
    Route::put('/correlativos/{id}', [ConfiguracionController::class, 'actualizarCorrelativo']);
    
    // Prueba de conexión SUNAT
    Route::get('/probar-sunat', [ConfiguracionController::class, 'probarConexionSunat']);
});

// Rutas de WhatsApp para envío de boletas
// Rutas de WhatsApp (sin auth:sanctum para compatibilidad con POS web)
Route::prefix('whatsapp')->group(function () {
    Route::post('/enviar-boleta', [WhatsAppController::class, 'enviarBoleta']);
    Route::get('/venta/{ventaId}/info', [WhatsAppController::class, 'obtenerInfoVenta']);
});

// Rutas públicas de facturación (sin autenticación para consultas externas)
Route::prefix('facturacion-publica')->group(function () {
    Route::post('/consultar-documento', [FacturacionController::class, 'consultarEstado']);
});

// Rutas del POS Optimizado - Ultra rápidas
Route::prefix('pos-optimizado')->group(function () {
    Route::get('/buscar', [PosOptimizadoController::class, 'buscarProductos']);
    Route::get('/populares', [PosOptimizadoController::class, 'productosPopulares']);
    Route::get('/categoria', [PosOptimizadoController::class, 'productosPorCategoria']);
    Route::get('/scroll-infinito', [PosOptimizadoController::class, 'scrollInfinito']);
    Route::get('/estadisticas', [PosOptimizadoController::class, 'estadisticas']);
    Route::get('/codigo-barras', [PosOptimizadoController::class, 'obtenerPorCodigoBarras']);
    Route::get('/categorias', [PosOptimizadoController::class, 'obtenerCategorias']);
    Route::get('/marcas', [PosOptimizadoController::class, 'obtenerMarcas']);
    Route::post('/limpiar-cache', [PosOptimizadoController::class, 'limpiarCache']);
    Route::post('/precargar-cache', [PosOptimizadoController::class, 'precargarCache']);
});

// Rutas de optimización de imágenes
Route::prefix('image-optimization')->group(function () {
    Route::post('/optimize-all', [ImageOptimizationController::class, 'optimizarTodasLasImagenes']);
    Route::get('/statistics', [ImageOptimizationController::class, 'obtenerEstadisticas']);
    Route::post('/clean-unused', [ImageOptimizationController::class, 'limpiarImagenesNoUtilizadas']);
    Route::post('/generate-placeholders', [ImageOptimizationController::class, 'generarPlaceholders']);
});

// Ruta de prueba para verificar que la API funciona
Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'API funcionando correctamente',
        'timestamp' => now()
    ]);
});