<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración del Sistema
    |--------------------------------------------------------------------------
    |
    | Aquí se definen las configuraciones por defecto del sistema
    |
    */

    'empresa' => [
        'nombre_default' => 'Mi Empresa',
        'ruc_default' => '',
        'direccion_default' => '',
        'telefono_default' => '',
        'email_default' => '',
        'logo_path' => 'storage/logos/',
        'logo_max_size' => 2048, // KB
        'logo_allowed_types' => ['jpg', 'jpeg', 'png', 'gif']
    ],

    'igv' => [
        'porcentaje_default' => 18.00,
        'nombre_default' => 'IGV',
        'incluir_precios_default' => true,
        'mostrar_ticket_default' => true
    ],

    'sunat' => [
        'url_produccion' => 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService',
        'url_beta' => 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService',
        'timeout' => 30,
        'certificado_path' => 'storage/certificados/',
        'certificado_max_size' => 5120 // KB
    ],

    'impresoras' => [
        'papel_ancho_default' => 80, // mm
        'copias_ticket_default' => 1,
        'tipos_papel' => [
            '58' => '58mm',
            '80' => '80mm',
            'A4' => 'A4',
            'Carta' => 'Carta'
        ]
    ],

    'tickets' => [
        'margen_superior_default' => 5, // mm
        'margen_inferior_default' => 5, // mm
        'mostrar_logo_default' => true,
        'mostrar_direccion_default' => true,
        'mostrar_telefono_default' => true,
        'mostrar_igv_default' => true,
        'pie_pagina_default' => 'Gracias por su compra'
    ],

    'comprobantes' => [
        'serie_factura_default' => 'F001',
        'serie_boleta_default' => 'B001',
        'serie_ticket_default' => 'T001',
        'numeracion_inicial' => 1,
        'papel_size_default' => 'A4',
        'orientacion_default' => 'portrait',
        'mostrar_qr_default' => true,
        'mostrar_hash_default' => true,
        'copias_factura_default' => 2,
        'copias_boleta_default' => 2,
        'copias_ticket_default' => 1
    ],

    'alertas' => [
        'stock_minimo_global_default' => 10,
        'dias_anticipacion_vencimiento_default' => 30,
        'dias_anticipacion_stock_default' => 7,
        'nivel_criticidad_default' => 'medio',
        'frecuencia_email_default' => 'diario',
        'niveles_criticidad' => [
            'bajo' => 'Bajo',
            'medio' => 'Medio',
            'alto' => 'Alto',
            'critico' => 'Crítico'
        ],
        'frecuencias_email' => [
            'inmediato' => 'Inmediato',
            'diario' => 'Diario',
            'semanal' => 'Semanal',
            'mensual' => 'Mensual'
        ]
    ],

    'cache' => [
        'tipos' => [
            'application' => 'Caché de Aplicación',
            'route' => 'Caché de Rutas',
            'view' => 'Caché de Vistas',
            'config' => 'Caché de Configuración'
        ],
        'optimizaciones' => [
            'config' => 'Optimizar Configuración',
            'route' => 'Optimizar Rutas',
            'autoload' => 'Optimizar Autoload'
        ],
        'frecuencias_limpieza' => [
            'nunca' => 'Nunca',
            'diario' => 'Diario',
            'semanal' => 'Semanal',
            'mensual' => 'Mensual'
        ]
    ],

    'sistema' => [
        'version' => '1.0.0',
        'nombre' => 'Sistema de Botica',
        'desarrollador' => 'Tu Empresa',
        'soporte_email' => 'soporte@tuempresa.com',
        'backup_path' => 'storage/backups/',
        'logs_path' => 'storage/logs/',
        'max_file_upload' => 10240, // KB
        'timezone_default' => 'America/Lima'
    ]
];