<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Venta\VentaController;
use App\Http\Controllers\Inventario\InventarioController;
use App\Http\Controllers\Inventario\ProductoOptimizadoController;
use App\Http\Controllers\Inventario\BackupController;
use App\Http\Controllers\Ubicacion\UbicacionController;
use App\Http\Controllers\Compra\CompraController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Inventario\categoria\CategoriaController;
use App\Http\Controllers\Inventario\presentacion\PresentacionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

// ============================================
// RUTAS PÚBLICAS (SIN AUTENTICACIÓN)
// ============================================

Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
// Alias para /login (Render accede a /login por defecto). Si ya estás autenticado, redirige al dashboard.
Route::get('/login', function() {
    return Auth::check() ? redirect()->route('dashboard.analisis') : app(\App\Http\Controllers\Auth\AuthController::class)->showLoginForm();
})->name('login.page');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas de recuperación de contraseña
Route::get('/password/reset', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/password/email', [AuthController::class, 'sendPasswordResetLink'])->name('password.email');

// Nuevas rutas para sistema de códigos
Route::get('/password/verify-code', [AuthController::class, 'showVerifyCodeForm'])->name('password.verify-code-form');
Route::post('/password/verify-code', [AuthController::class, 'verifyResetCode'])->name('password.verify-code');
Route::get('/password/reset-with-code', [AuthController::class, 'showResetFormWithCode'])->name('password.reset-form-with-code');
Route::post('/password/update-with-code', [AuthController::class, 'updatePasswordWithCode'])->name('password.update-with-code');
Route::post('/password/resend-code', [AuthController::class, 'resendResetCode'])->name('password.resend-code');

// Rutas legacy (mantenidas para compatibilidad)
Route::get('/password/reset/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/password/reset', [AuthController::class, 'resetPassword'])->name('password.store');

// Diagnóstico del entorno (público solo lectura)
Route::get('/diagnostico', [\App\Http\Controllers\Admin\DiagnosticoController::class, 'index'])->name('diagnostico');

// ============================================
// RUTAS PROTEGIDAS CON PERMISOS
// ============================================

Route::middleware('auth')->group(function () {
    
    // Dashboard - con permisos restaurados
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->can('dashboard.view');
    Route::get('/dashboard/analisis', [DashboardController::class, 'analisis'])->name('dashboard.analisis')->can('dashboard.view');


    // ============================================
    // MÓDULO DE VENTAS - SIN MIDDLEWARES TEMPORALMENTE
    // ============================================
    
    // Ventas - ver historial y clientes
    Route::controller(VentaController::class)->group(function () {
        Route::get('/ventas/historial', 'historial')->name('ventas.historial');
        Route::get('/ventas/clientes', 'clientes')->name('ventas.clientes');
        Route::get('/ventas/nueva', 'nueva')->name('ventas.nueva');
        Route::get('/ventas/devoluciones', 'devoluciones')->name('ventas.devoluciones');
        Route::post('/ventas/procesar-devolucion', 'procesarDevolucion')->name('ventas.procesar-devolucion');
        Route::get('/ventas/reportes', 'reportes')->name('ventas.reportes');
        Route::get('/ventas/reportes/datos', 'obtenerDatosReporteAPI')->name('ventas.reportes.datos');
        Route::get('/ventas/detalle/{id}', 'obtenerDetalle')->name('ventas.detalle');
    });
    
    // Punto de Venta (POS)
    Route::controller(\App\Http\Controllers\PuntoVenta\PuntoVentaController::class)->prefix('punto-venta')->name('punto-venta.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/buscar-productos', 'buscarProductos')->name('buscar-productos');
        Route::post('/consultar-dni', 'consultarDni')->name('consultar-dni');
        Route::post('/procesar-venta', 'procesarVenta')->name('procesar-venta');
        Route::get('/vista-previa/{venta}', 'vistaPrevia')->name('vista-previa');
        Route::get('/estadisticas-hoy', 'estadisticasHoy')->name('estadisticas-hoy');
        Route::post('/verificar-estado-comprobante', 'verificarEstadoComprobante')->name('verificar-estado');
        Route::post('/regenerar-comprobante', 'regenerarComprobante')->name('regenerar-comprobante');
        
        // Rutas para generar boletas térmicas y A4
        Route::get('/boleta/{venta}', 'boleta')->name('boleta');
        Route::get('/ticket/{venta}', 'ticket')->name('ticket');
        Route::get('/pdf/{venta}', 'pdf')->name('pdf');
        Route::get('/descargar-comprobante/{venta}', 'descargarComprobante')->name('descargar-comprobante');
    });

    // API para Punto de Venta Profesional
    Route::prefix('api/punto-venta')->name('api.punto-venta.')->group(function () {
        Route::get('/productos-mas-vendidos', [\App\Http\Controllers\PuntoVenta\PuntoVentaController::class, 'productosMasVendidos'])->name('productos-mas-vendidos');
        Route::get('/buscar-productos', [\App\Http\Controllers\PuntoVenta\PuntoVentaController::class, 'buscarProductos'])->name('buscar-productos');
        Route::get('/buscar-alternativas', [\App\Http\Controllers\PuntoVenta\PuntoVentaController::class, 'buscarAlternativas'])->name('buscar-alternativas');
        Route::get('/estadisticas-hoy', [\App\Http\Controllers\PuntoVenta\PuntoVentaController::class, 'estadisticasHoy'])->name('estadisticas-hoy');
        Route::post('/procesar-venta', [\App\Http\Controllers\PuntoVenta\PuntoVentaController::class, 'procesarVenta'])->name('procesar-venta');
    });

    // API para filtros del POS
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/categorias', [\App\Http\Controllers\Inventario\InventarioController::class, 'obtenerCategorias'])->name('categorias');
        Route::get('/ubicaciones', [\App\Http\Controllers\Ubicacion\UbicacionController::class, 'obtenerUbicacionesParaFiltros'])->name('ubicaciones');
    });

    // ============================================
    // MÓDULO DE UBICACIONES/ALMACÉN - SIN MIDDLEWARES TEMPORALMENTE
    // ============================================

    Route::controller(UbicacionController::class)->group(function () {
        Route::get('/ubicaciones/mapa', 'mapa')->name('ubicaciones.mapa');
        Route::get('/ubicaciones/estante/{id}', 'detalleEstante')->name('ubicaciones.estante.detalle');
        Route::get('/ubicaciones/estantes', 'estantes')->name('ubicaciones.estantes');
        
        // Crear y editar estantes
        Route::post('/ubicaciones/estantes/crear', 'crearEstante')->name('ubicaciones.estantes.crear');
        Route::put('/ubicaciones/estantes/{id}/editar', 'editarEstante')->name('ubicaciones.estantes.editar');
        Route::delete('/ubicaciones/estantes/{id}/eliminar', 'eliminarEstante')->name('ubicaciones.estantes.eliminar');
        
        // APIs del almacén
        Route::get('/api/ubicaciones/estantes', 'obtenerEstantes')->name('api.ubicaciones.estantes');
        Route::get('/api/ubicaciones/estante/{id}', 'obtenerEstante')->name('api.ubicaciones.estante.obtener');
        Route::get('/api/ubicaciones/productos-sin-ubicar', 'productosSinUbicar')->name('api.productos.sin-ubicar');
        Route::get('/api/ubicaciones/productos-ubicados', 'productosUbicados')->name('api.productos.ubicados');
        Route::get('/api/ubicaciones/todos-los-productos', 'todosLosProductos')->name('api.productos.todos');
        Route::get('/api/productos/{id}/vencimiento', 'obtenerFechaVencimiento')->name('api.producto.vencimiento');
        
        // APIs para gestión de estantes
        Route::post('/api/ubicaciones/crear-estante', 'crearEstante')->name('api.ubicaciones.crear-estante');
        Route::put('/api/ubicaciones/editar-estante/{id}', 'editarEstante')->name('api.ubicaciones.editar-estante');
        Route::delete('/api/ubicaciones/eliminar-estante/{id}', 'eliminarEstante')->name('api.ubicaciones.eliminar-estante');
        
        // API para fusión de slots
        Route::post('/api/ubicaciones/fusionar-slots', 'fusionarSlots')->name('api.ubicaciones.fusionar-slots');
        Route::post('/api/ubicaciones/separar-slots', 'separarSlots')->name('api.ubicaciones.separar-slots');
        Route::post('/api/ubicaciones/actualizar-estructura-estante/{id}', 'actualizarEstructuraEstante')->name('api.ubicaciones.actualizar-estructura');
        
        // APIs para ubicar productos
        Route::post('/api/ubicaciones/ubicar-producto', 'ubicarProducto')->name('api.ubicaciones.ubicar-producto');
        Route::post('/api/ubicaciones/distribuir-producto-multiple', 'distribuirProductoMultiple')->name('api.ubicaciones.distribuir-producto-multiple');
        Route::post('/api/ubicaciones/actualizar-producto', 'actualizarProducto')->name('api.ubicaciones.actualizar-producto');
        Route::post('/api/ubicaciones/eliminar-producto', 'eliminarProductoDeUbicacion')->name('api.ubicaciones.eliminar-producto');
        Route::post('/api/ubicaciones/quitar-producto', 'quitarProducto')->name('api.ubicaciones.quitar-producto');
        
        // API para información de stock de productos
        Route::get('/api/productos/{id}/informacion-stock', 'obtenerInformacionStock')->name('api.productos.informacion-stock');
    });

    // ============================================
    // MÓDULO DE COMPRAS - SIN MIDDLEWARES TEMPORALMENTE
    // ============================================
    
    // Compras - todas las rutas sin middlewares
    Route::controller(\App\Http\Controllers\Compra\CompraController::class)->group(function () {
        Route::get('/compras/historial', 'historial')->name('compras.historial');
        Route::get('/compras/proveedores', 'proveedores')->name('compras.proveedores');
        Route::get('/compras/nueva', 'nueva')->name('compras.nueva');
        Route::post('/compras/guardar', 'store')->name('compras.store');
        Route::post('/compras/procesar', 'procesarEntrada')->name('compras.procesar');
        Route::post('/compras/proveedores/guardar', 'guardarProveedor')->name('compras.guardar-proveedor');
        Route::put('/compras/proveedores/{id}', 'actualizarProveedor')->name('compras.actualizar-proveedor');
        Route::post('/compras/proveedores/{id}/cambiar-estado', 'cambiarEstadoProveedor')->name('compras.cambiar-estado-proveedor');
        Route::delete('/compras/proveedores/{id}', 'eliminarProveedor')->name('compras.eliminar-proveedor');
        Route::get('/api/compras/buscar-proveedores', 'buscarProveedores')->name('api.compras.buscar-proveedores');
        Route::get('/api/compras/proveedor/{id}', 'obtenerProveedor')->name('api.compras.obtener-proveedor');
        Route::get('/api/compras/buscar-productos', 'buscarProductos')->name('api.compras.buscar-productos');
        Route::get('/compras/proveedores/api', 'apiProveedores')->name('compras.proveedores.api');
        Route::get('/compras/test-ubicaciones', 'testUbicaciones')->name('compras.test-ubicaciones');
    });

    // ============================================
    // MÓDULO DE ADMINISTRACIÓN - SIN MIDDLEWARES TEMPORALMENTE
    // ============================================
    
    // Usuarios - todas las rutas sin middlewares
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::controller(\App\Http\Controllers\Admin\UserController::class)->prefix('usuarios')->name('usuarios.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{user}', 'show')->name('show');
            Route::get('/crear', 'create')->name('crear');
            Route::post('/', 'store')->name('store');
            Route::get('/{user}/editar', 'edit')->name('editar');
            Route::put('/{user}', 'update')->name('update');
            Route::post('/{user}/cambiar-estado', 'cambiarEstado')->name('cambiar-estado');
            Route::post('/{user}/resetear-password', 'resetearPassword')->name('resetear-password');
            Route::delete('/{user}', 'destroy')->name('destroy');
            Route::get('/api', 'apiUsuarios')->name('api');
        });
        
        // Roles y Permisos
        Route::controller(\App\Http\Controllers\Admin\RoleController::class)->prefix('roles')->name('roles.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/crear', 'create')->name('crear');
            Route::post('/', 'store')->name('store');
            Route::get('/{role}', 'show')->name('show');
            Route::get('/{role}/editar', 'edit')->name('editar');
            Route::put('/{role}', 'update')->name('update');
            Route::delete('/{role}', 'destroy')->name('destroy');
            Route::post('/{role}/asignar-permisos', 'asignarPermisos')->name('asignar-permisos');
            Route::post('/{role}/cambiar-estado', 'cambiarEstado')->name('cambiar-estado');
            Route::get('/api', 'apiRoles')->name('api');
        });
        
        // Configuración del sistema
        Route::controller(AdminController::class)->group(function () {
            // Configuración general existente
            Route::get('/configuracion', 'configuracion')->name('configuracion');
            Route::post('/configuracion/actualizar', 'actualizarConfiguracionSistema')->name('configuracion.actualizar');
            Route::get('/configuracion/obtener', 'obtenerConfiguracionSistema')->name('configuracion.obtener');
            
            // Nuevas rutas de configuración específicas
            Route::get('/configuracion/empresa', 'configuracionEmpresa')->name('configuracion.empresa');
            Route::post('/configuracion/empresa/actualizar', 'actualizarConfiguracionEmpresa')->name('configuracion.empresa.actualizar');
            
            Route::get('/configuracion/igv', 'configuracionIgv')->name('configuracion.igv');
            Route::post('/configuracion/igv/actualizar', 'actualizarConfiguracionIgv')->name('configuracion.igv.actualizar');
            

            
            Route::get('/configuracion/impresoras', 'configuracionImpresoras')->name('configuracion.impresoras');
            Route::post('/configuracion/impresoras/actualizar', 'actualizarConfiguracionImpresoras')->name('configuracion.impresoras.actualizar');
            Route::post('/configuracion/impresoras/probar', 'probarImpresora')->name('configuracion.impresoras.probar');
            
            Route::get('/configuracion/tickets', 'configuracionTickets')->name('configuracion.tickets');
            Route::post('/configuracion/tickets/actualizar', 'actualizarConfiguracionTickets')->name('configuracion.tickets.actualizar');
            Route::get('/configuracion/tickets/vista-previa', 'vistaPreviaTicket')->name('configuracion.tickets.vista-previa');
            
            Route::get('/configuracion/comprobantes', 'configuracionComprobantes')->name('configuracion.comprobantes');
            Route::post('/configuracion/comprobantes/actualizar', 'actualizarConfiguracionComprobantes')->name('configuracion.comprobantes.actualizar');
            Route::get('/configuracion/comprobantes/vista-previa', 'vistaPreviaComprobante')->name('configuracion.comprobantes.vista-previa');
            
            Route::get('/configuracion/alertas', 'configuracionAlertas')->name('configuracion.alertas');
            Route::post('/configuracion/alertas/actualizar', 'actualizarConfiguracionAlertas')->name('configuracion.alertas.actualizar');
            
            Route::get('/configuracion/cache', 'configuracionCache')->name('configuracion.cache');
            Route::post('/configuracion/cache/limpiar', 'limpiarCache')->name('configuracion.cache.limpiar');
            Route::post('/configuracion/cache/optimizar', 'optimizarSistema')->name('configuracion.cache.optimizar');
        });
        
        // Configuración SUNAT - Facturación Electrónica
        Route::controller(\App\Http\Controllers\SunatConfigController::class)->prefix('sunat')->name('sunat.')->group(function () {
            Route::get('/configuracion', 'index')->name('configuracion');
            Route::post('/configuracion/guardar', 'guardarConfiguracion')->name('configuracion.guardar');
            Route::post('/certificado/subir', 'subirCertificado')->name('certificado.subir');
            Route::post('/conexion/probar', 'probarConexion')->name('conexion.probar');
            Route::get('/estado', 'estadoSistema')->name('estado');
            
            // Rutas adicionales de configuración
            Route::post('/configuracion/exportar', 'exportarConfiguracion')->name('configuracion.exportar');
            Route::post('/configuracion/importar', 'importarConfiguracion')->name('configuracion.importar');
            Route::get('/configuracion/estado-sistema', 'estadoSistema')->name('configuracion.estado-sistema');

            Route::post('/configuracion/alertas/probar', 'probarAlertas')->name('configuracion.alertas.probar');
            Route::get('/configuracion/comprobantes/vista-previa', 'vistaPreviaComprobante')->name('configuracion.comprobantes.vista-previa');
            
            // Monitoreo
            Route::get('/monitoreo', 'monitoreo')->name('monitoreo');
            Route::get('/monitoreo/data', 'monitoreoData')->name('monitoreo.data');
            Route::get('/estadisticas', 'estadisticas')->name('estadisticas');
            Route::post('/actualizar-estados', 'actualizarEstados')->name('actualizar-estados');
            Route::get('/detalles/{venta}', 'detalles')->name('detalles');
            
            // Descargas
            Route::get('/descargar-xml/{venta}', 'descargarXML')->name('descargar-xml');
            Route::get('/descargar-pdf/{venta}', 'descargarPDF')->name('descargar-pdf');
            
            // Rutas existentes
            Route::get('/respaldos', 'respaldos')->name('respaldos');
            Route::get('/logs', 'logs')->name('logs');
        });
        
        // Rutas de respaldos
        Route::controller(\App\Http\Controllers\Inventario\BackupController::class)->prefix('respaldos')->name('respaldos.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/crear', 'backup')->name('crear');
        });
        
        // Ruta directa para respaldos (compatibilidad)
        Route::get('/respaldos', [\App\Http\Controllers\Inventario\BackupController::class, 'index'])->name('respaldos');
        
        // Ruta de logs
        Route::get('/logs', [\App\Http\Controllers\Admin\AdminController::class, 'logs'])->name('logs');
        
        // Optimización de imágenes
        Route::get('/image-optimization', [\App\Http\Controllers\ImageOptimizationViewController::class, 'index'])->name('image-optimization');
    });

    // Backups - sin middlewares temporalmente
    Route::controller(BackupController::class)->group(function () {
        Route::post('/inventario/backup', 'backup')->name('inventario.backup');
    });

    // ============================================
    // MÓDULO DE PERFIL - SIN MIDDLEWARES TEMPORALMENTE
    // ============================================
    
    // Perfil - todas las rutas sin middlewares
    Route::prefix('perfil')->name('perfil.')->group(function () {
        Route::get('/ver', [AdminController::class, 'verPerfil'])->name('ver');
        Route::get('/editar', [AdminController::class, 'editarPerfil'])->name('editar');
        Route::patch('/actualizar', [AdminController::class, 'actualizarPerfil'])->name('actualizar');
        Route::put('/cambiar-password', [AdminController::class, 'cambiarPassword'])->name('cambiar-password');
        Route::post('/subir-avatar', [AdminController::class, 'subirAvatar'])->name('subir-avatar');
        Route::delete('/eliminar-avatar', [AdminController::class, 'eliminarAvatar'])->name('eliminar-avatar');
        Route::post('/actualizar-configuracion', [AdminController::class, 'actualizarConfiguracion'])->name('actualizar-configuracion');
        Route::post('/exportar-datos', [AdminController::class, 'exportarDatos'])->name('exportar-datos');
        Route::delete('/eliminar-cuenta', [AdminController::class, 'eliminarCuenta'])->name('eliminar-cuenta');
        
        // Ruta de respaldos
        Route::get('/respaldos', [\App\Http\Controllers\Inventario\BackupController::class, 'index'])->name('respaldos');
        Route::post('/respaldos/crear', [\App\Http\Controllers\Inventario\BackupController::class, 'backup'])->name('respaldos.crear');
    });

    // ============================================
    // RUTAS AJAX PARA CATEGORÍAS Y PRESENTACIONES - SIN MIDDLEWARES TEMPORALMENTE
    // ============================================
    
    Route::prefix('inventario/categoria/api')->group(function () {
        Route::get('/', [CategoriaController::class, 'index']);
        Route::get('/all', function() {
            return response()->json(['success' => true, 'data' => \App\Models\Categoria::where('estado', 'activo')->orderBy('nombre')->get(['id','nombre'])]);
        });
        Route::get('/producto/{id}', [InventarioController::class, 'getProductoById']);
        Route::get('/{id}', [CategoriaController::class, 'show']);
        Route::post('/', [CategoriaController::class, 'store']);
        Route::put('/{id}', [CategoriaController::class, 'update']);
        Route::delete('/{id}', [CategoriaController::class, 'destroy']);
    });

    Route::prefix('inventario/presentacion/api')->group(function () {
        Route::get('/', [PresentacionController::class, 'index']);
        Route::get('/all', function() {
            return response()->json(['success' => true, 'data' => \App\Models\Presentacion::where('estado', 'activo')->orderBy('nombre')->get(['id','nombre'])]);
        });
        Route::get('/{id}', [PresentacionController::class, 'show']);
        Route::post('/', [PresentacionController::class, 'store']);
        Route::put('/{id}', [PresentacionController::class, 'update']);
        Route::delete('/{id}', [PresentacionController::class, 'destroy']);
    });

    // Presentaciones por producto (Unidad, Blíster, Caja, etc.)
    Route::prefix('inventario/producto/presentaciones/api')->group(function () {
        Route::get('/', [\App\Http\Controllers\Inventario\producto\ProductoPresentacionController::class, 'index']);
        Route::get('/{id}', [\App\Http\Controllers\Inventario\producto\ProductoPresentacionController::class, 'show']);
        Route::post('/', [\App\Http\Controllers\Inventario\producto\ProductoPresentacionController::class, 'store']);
        Route::put('/{id}', [\App\Http\Controllers\Inventario\producto\ProductoPresentacionController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\Inventario\producto\ProductoPresentacionController::class, 'destroy']);
    });

    // ============================================
    // RUTAS DEL SISTEMA DE LOTES
    // ============================================
    
    // Rutas para ventas con lotes
    Route::prefix('lotes')->name('lotes.')->group(function () {
        Route::controller(\App\Http\Controllers\Venta\VentaLoteController::class)->group(function () {
            Route::post('/procesar-venta', 'procesarVenta')->name('procesar-venta');
            Route::get('/disponibles/{producto_id}', 'obtenerLotesDisponibles')->name('disponibles');
            Route::post('/simular-venta', 'simularVenta')->name('simular-venta');
            Route::get('/resumen/{producto_id}', 'resumenLotes')->name('resumen');
        });
    });

    // Rutas para gestión de lotes en inventario
    Route::prefix('inventario/lotes')->name('inventario.lotes.')->group(function () {
        Route::controller(\App\Http\Controllers\LoteController::class)->group(function () {
            Route::get('/{producto_id}', 'index')->name('index');
            Route::get('/info/{producto_id}', 'obtenerLotes')->name('info');
            Route::get('/disponibles/{producto_id}', 'lotesDisponibles')->name('disponibles');
            Route::post('/simular-venta', 'simularVenta')->name('simular-venta');
            Route::put('/ajustar-stock/{lote_id}', 'ajustarStock')->name('ajustar-stock');
            Route::get('/movimientos/{lote_id}', 'movimientos')->name('movimientos');
            Route::post('/marcar-vencidos', 'marcarVencidos')->name('marcar-vencidos');
            Route::get('/reporte-proximos-vencer', 'reporteProximosVencer')->name('reporte-proximos-vencer');
        });
    });

    // ============================================
    // RUTAS DE COMPATIBILIDAD Y REDIRECCIONES
    // ============================================
    
    Route::redirect('/ventas/nueva', '/punto-venta')->name('ventas.nueva.redirect');

}); // Fin del middleware auth

// ============================================
// MÓDULO DE INVENTARIO - SIN MIDDLEWARES DE AUTENTICACIÓN
// ============================================

// Inventario - todas las rutas sin middlewares de autenticación
Route::controller(InventarioController::class)->group(function () {
    Route::get('/inventario/productos', 'index')->name('inventario.productos');
    Route::get('/inventario/productos/ajax', 'ajaxIndex')->name('inventario.productos.ajax');
    Route::get('/inventario/producto/{id}', 'show');
    Route::post('/inventario/producto/guardar', 'store');
    Route::post('/inventario/producto/actualizar/{id}', 'update');
    Route::put('/inventario/producto/actualizar/{id}', 'update');
    Route::delete('/inventario/producto/eliminar/{id}', 'destroy');
    Route::get('/inventario/reordenar-ids', 'reordenarIds')->name('inventario.reordenar-ids');
    Route::get('/inventario/categorias', 'categorias')->name('inventario.categorias');
    Route::get('/inventario/presentaciones', 'presentacion')->name('inventario.presentaciones');
    Route::post('/inventario/categorias/{id}/cambiar-estado', 'cambiarEstadoCategoria')->name('inventario.categorias.cambiar-estado');
    Route::post('/inventario/presentaciones/{id}/cambiar-estado', 'cambiarEstadoPresentacion')->name('inventario.presentaciones.cambiar-estado');
});

// Vista alternativa: Productos Botica (lista-productos.blade)
Route::get('/inventario/productos-botica', function() {
    return view('inventario.lista-productos');
})->name('inventario.productos.botica');

// Ruta de prueba temporal
Route::get('/test-producto', function () {
    return view('test-producto');
});

// ============================================
// API DE VALIDACIONES EN TIEMPO REAL PARA PRODUCTOS
// ============================================

Route::prefix('api/productos')->name('api.productos.')->controller(ProductoOptimizadoController::class)->group(function () {
    Route::post('/validar-duplicado', 'validarDuplicado')->name('validar-duplicado');
    Route::post('/validar-codigo-barras', 'validarCodigoBarras')->name('validar-codigo-barras');
    Route::post('/validar-precios', 'validarPrecios')->name('validar-precios');
    Route::get('/autocompletar', 'autocompletar')->name('autocompletar');
    Route::get('/criticos', 'productosCriticos')->name('criticos');
});

// ============================================
// RUTAS DE PRUEBA Y DESARROLLO (solo en desarrollo)
// ============================================

if (config('app.debug')) {
    Route::middleware('auth')->get('/test-permisos', function() {
        if (!auth()->check()) {
            return 'Usuario no autenticado';
        }
        
        $user = auth()->user();
        return [
            'usuario' => $user->name,
            'email' => $user->email,
            'roles' => $user->getRoleNames(),
            'total_permisos' => $user->getAllPermissions()->count(),
            'permisos' => $user->getAllPermissions()->pluck('name'),
            'puede_ver_dashboard' => $user->canViewDashboard(),
            'puede_acceder_ventas' => $user->canAccessVentas(),
            'puede_acceder_inventario' => $user->canAccessInventario(),
            'middleware_funcionando' => 'SÍ - Esta ruta requiere autenticación'
        ];
    });

    Route::middleware('auth')->get('/test-dashboard', function() {
        return [
            'mensaje' => '✅ Dashboard accesible - SIN MIDDLEWARES DE PERMISOS TEMPORALMENTE',
            'usuario' => auth()->user()->name,
            'sistema_funcionando' => 'OK'
        ];
    });
    
    // Endpoint temporal para verificar ventas disponibles
    Route::middleware('auth')->get('/debug-ventas', function() {
        try {
            $ventaEspecifica = \App\Models\PuntoVenta\Venta::where('numero_venta', 'V-20250729-9126')->first();
            
            $ventas = \App\Models\PuntoVenta\Venta::select('numero_venta', 'estado', 'fecha_venta', 'total')
                ->orderBy('fecha_venta', 'desc')
                ->limit(10)
                ->get();
            
            return response()->json([
                'venta_buscada' => $ventaEspecifica ? [
                    'numero' => $ventaEspecifica->numero_venta,
                    'estado' => $ventaEspecifica->estado,
                    'fecha' => $ventaEspecifica->fecha_venta,
                    'puede_devolver' => !in_array($ventaEspecifica->estado, ['cancelada'])
                ] : 'NO_ENCONTRADA',
                'total_ventas' => $ventas->count(),
                'ultimas_ventas' => $ventas->map(function($venta) {
                    return [
                        'numero' => $venta->numero_venta,
                        'estado' => $venta->estado,
                        'fecha' => $venta->fecha_venta,
                        'total' => $venta->total,
                        'puede_devolver' => !in_array($venta->estado, ['cancelada'])
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    });
    
    // Endpoint temporal para resetear devoluciones de una venta (solo para testing)
    Route::middleware('auth')->get('/reset-devoluciones/{numeroVenta}', function($numeroVenta) {
        try {
            $venta = \App\Models\PuntoVenta\Venta::where('numero_venta', $numeroVenta)->first();
            
            if (!$venta) {
                return response()->json(['error' => 'Venta no encontrada']);
            }
            
            $devoluciones = $venta->devoluciones;
            $totalDevoluciones = $devoluciones->count();
            
            // Restaurar stock
            foreach ($devoluciones as $devolucion) {
                $producto = \App\Models\Producto::find($devolucion->producto_id);
                if ($producto) {
                    $producto->decrement('stock_actual', $devolucion->cantidad_devuelta);
                }
            }
            
            // Eliminar devoluciones
            $venta->devoluciones()->delete();
            
            // Restaurar estado original
            $venta->update(['estado' => 'completada']);
            
            return response()->json([
                'success' => true,
                'message' => "Se eliminaron {$totalDevoluciones} devoluciones de la venta {$numeroVenta}",
                'venta_estado' => $venta->estado
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    });

    // Endpoint temporal para verificar totales de una venta específica
    Route::middleware('auth')->get('/verificar-totales/{numeroVenta}', function($numeroVenta) {
        try {
            $venta = \App\Models\PuntoVenta\Venta::with(['detalles.producto', 'devoluciones.producto'])
                ->where('numero_venta', $numeroVenta)
                ->first();
            
            if (!$venta) {
                return response()->json(['error' => 'Venta no encontrada']);
            }
            
            return response()->json([
                'numero_venta' => $venta->numero_venta,
                'estado' => $venta->estado,
                'estado_formateado' => $venta->estado_formateado,
                'tiene_devoluciones' => $venta->tiene_devoluciones,
                'totales' => [
                    'original' => $venta->total,
                    'actual' => $venta->total_actual,
                    'devuelto' => $venta->monto_total_devuelto,
                    'porcentaje_devuelto' => $venta->total > 0 ? round(($venta->monto_total_devuelto / $venta->total) * 100, 2) : 0
                ],
                'debug_info' => [
                    'total_detalles' => $venta->detalles->count(),
                    'total_devoluciones' => $venta->devoluciones->count(),
                    'detalles_completamente_devueltos' => $venta->detalles->filter(function($detalle) use ($venta) {
                        $cantidadDevuelta = $venta->devoluciones
                            ->where('venta_detalle_id', $detalle->id)
                            ->sum('cantidad_devuelta');
                        return $cantidadDevuelta >= $detalle->cantidad;
                    })->count()
                ],
                'detalles' => $venta->detalles->map(function($detalle) use ($venta) {
                    $cantidadDevuelta = $venta->devoluciones
                        ->where('venta_detalle_id', $detalle->id)
                        ->sum('cantidad_devuelta');
                    
                    return [
                        'producto' => $detalle->producto->nombre,
                        'cantidad_original' => $detalle->cantidad,
                        'cantidad_devuelta' => $cantidadDevuelta,
                        'cantidad_restante' => $detalle->cantidad - $cantidadDevuelta,
                        'porcentaje_devuelto' => $detalle->cantidad > 0 ? round(($cantidadDevuelta / $detalle->cantidad) * 100, 2) : 0,
                        'completamente_devuelto' => $cantidadDevuelta >= $detalle->cantidad,
                        'precio_unitario' => $detalle->precio_unitario,
                        'subtotal_original' => $detalle->cantidad * $detalle->precio_unitario,
                        'subtotal_actual' => ($detalle->cantidad - $cantidadDevuelta) * $detalle->precio_unitario
                    ];
                }),
                'devoluciones' => $venta->devoluciones->map(function($devolucion) {
                    return [
                        'producto' => $devolucion->producto->nombre,
                        'cantidad_devuelta' => $devolucion->cantidad_devuelta,
                        'monto_devolucion' => $devolucion->monto_devolucion,
                        'motivo' => $devolucion->motivo,
                        'fecha' => $devolucion->fecha_devolucion
                    ];
                })
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    });
} // Fin del bloque if (config('app.debug'))