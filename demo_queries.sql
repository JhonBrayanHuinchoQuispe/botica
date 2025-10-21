-- =====================================================
-- SCRIPT DE DEMOSTRACIÓN - SISTEMA DE BOTICA
-- Consultas para presentar la funcionalidad de la BD
-- =====================================================

-- 1. CONSULTA BÁSICA: Productos con información completa
-- Muestra la relación entre productos, categorías y presentaciones
SELECT 
    p.id,
    p.nombre,
    p.codigo_barras,
    c.nombre AS categoria,
    pr.nombre AS presentacion,
    p.stock_actual,
    p.precio_venta,
    p.fecha_vencimiento,
    p.estado
FROM productos p
LEFT JOIN categorias c ON p.categoria_id = c.id
LEFT JOIN presentaciones pr ON p.presentacion_id = pr.id
ORDER BY p.nombre
LIMIT 10;

-- 2. PRODUCTOS POR VENCER (Próximos 30 días)
-- Consulta crítica para el negocio
SELECT 
    p.nombre,
    p.lote,
    p.fecha_vencimiento,
    p.stock_actual,
    DATEDIFF(p.fecha_vencimiento, CURDATE()) AS dias_restantes
FROM productos p
WHERE p.fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    AND p.stock_actual > 0
ORDER BY p.fecha_vencimiento ASC;

-- 3. PRODUCTOS CON BAJO STOCK
-- Alerta de inventario
SELECT 
    p.nombre,
    c.nombre AS categoria,
    p.stock_actual,
    p.stock_minimo,
    (p.stock_minimo - p.stock_actual) AS deficit
FROM productos p
LEFT JOIN categorias c ON p.categoria_id = c.id
WHERE p.stock_actual <= p.stock_minimo
ORDER BY deficit DESC;

-- 4. VENTAS DEL DÍA CON DETALLES
-- Reporte de ventas
SELECT 
    v.numero_venta,
    CONCAT(cl.nombres, ' ', cl.apellidos) AS cliente,
    v.total,
    v.metodo_pago,
    COUNT(vd.id) AS items_vendidos
FROM ventas v
LEFT JOIN clientes cl ON v.cliente_id = cl.id
LEFT JOIN venta_detalles vd ON v.id = vd.venta_id
WHERE DATE(v.fecha_venta) = CURDATE()
GROUP BY v.id
ORDER BY v.created_at DESC;

-- 5. PRODUCTOS MÁS VENDIDOS
-- Análisis de productos populares
SELECT 
    p.nombre,
    c.nombre AS categoria,
    SUM(vd.cantidad) AS total_vendido,
    SUM(vd.subtotal) AS ingresos_generados
FROM venta_detalles vd
JOIN productos p ON vd.producto_id = p.id
LEFT JOIN categorias c ON p.categoria_id = c.id
JOIN ventas v ON vd.venta_id = v.id
WHERE v.fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY p.id
ORDER BY total_vendido DESC
LIMIT 10;

-- 6. INVENTARIO POR CATEGORÍA
-- Resumen de stock por categoría
SELECT 
    c.nombre AS categoria,
    COUNT(p.id) AS total_productos,
    SUM(p.stock_actual) AS stock_total,
    SUM(p.stock_actual * p.precio_compra) AS valor_inventario
FROM categorias c
LEFT JOIN productos p ON c.id = p.categoria_id
WHERE p.stock_actual > 0
GROUP BY c.id
ORDER BY valor_inventario DESC;

-- 7. UBICACIONES EN ALMACÉN
-- Sistema de ubicaciones
SELECT 
    e.nombre AS estante,
    u.nombre AS ubicacion,
    u.codigo,
    u.capacidad_maxima,
    COUNT(pu.producto_id) AS productos_asignados
FROM estantes e
JOIN ubicaciones u ON e.id = u.estante_id
LEFT JOIN producto_ubicaciones pu ON u.id = pu.ubicacion_id AND pu.activo = 1
WHERE e.activo = 1 AND u.activo = 1
GROUP BY u.id
ORDER BY e.nombre, u.nombre;

-- 8. CLIENTES MÁS FRECUENTES
-- Análisis de clientes
SELECT 
    CONCAT(c.nombres, ' ', c.apellidos) AS cliente,
    c.dni,
    COUNT(v.id) AS total_compras,
    SUM(v.total) AS total_gastado,
    MAX(v.fecha_venta) AS ultima_compra
FROM clientes c
JOIN ventas v ON c.id = v.cliente_id
WHERE v.fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
GROUP BY c.id
HAVING total_compras >= 3
ORDER BY total_gastado DESC
LIMIT 10;

-- 9. ESTADO GENERAL DEL INVENTARIO
-- Dashboard principal
SELECT 
    COUNT(*) AS total_productos,
    SUM(CASE WHEN stock_actual > stock_minimo THEN 1 ELSE 0 END) AS productos_ok,
    SUM(CASE WHEN stock_actual <= stock_minimo THEN 1 ELSE 0 END) AS productos_bajo_stock,
    SUM(CASE WHEN fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) AS productos_por_vencer,
    SUM(stock_actual * precio_compra) AS valor_total_inventario
FROM productos
WHERE stock_actual > 0;

-- 10. ÍNDICES CREADOS (Para mostrar optimización)
-- Verificar índices para rendimiento
SHOW INDEX FROM productos;

-- =====================================================
-- COMANDOS PARA LA DEMOSTRACIÓN
-- =====================================================

-- Mostrar estructura de tabla principal
DESCRIBE productos;

-- Mostrar relaciones (Foreign Keys)
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE REFERENCED_TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'productos';

-- Estadísticas de la base de datos
SELECT 
    table_name AS 'Tabla',
    table_rows AS 'Registros',
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Tamaño (MB)'
FROM information_schema.tables
WHERE table_schema = DATABASE()
ORDER BY table_rows DESC;