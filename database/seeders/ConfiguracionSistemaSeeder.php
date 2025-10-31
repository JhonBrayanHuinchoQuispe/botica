<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ConfiguracionSistema;

class ConfiguracionSistemaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar si ya existe configuración
        if (ConfiguracionSistema::count() > 0) {
            $this->command->info('Las configuraciones del sistema ya existen. Saltando...');
            return;
        }

        $this->command->info('Creando configuraciones por defecto del sistema...');

        ConfiguracionSistema::create([
            // Configuración de Empresa
            'empresa_nombre' => config('sistema.empresa.nombre_default'),
            'empresa_ruc' => config('sistema.empresa.ruc_default'),
            'empresa_direccion' => config('sistema.empresa.direccion_default'),
            'empresa_telefono' => config('sistema.empresa.telefono_default'),
            'empresa_email' => config('sistema.empresa.email_default'),
            'empresa_logo' => null,

            // Configuración de IGV
            'igv_porcentaje' => config('sistema.igv.porcentaje_default'),
            'igv_nombre' => config('sistema.igv.nombre_default'),
            'incluir_igv_precios' => config('sistema.igv.incluir_precios_default'),
            'mostrar_igv_ticket' => config('sistema.igv.mostrar_ticket_default'),

            // Configuración de SUNAT
            'sunat_habilitado' => false,
            'sunat_usuario' => null,
            'sunat_password' => null,
            'sunat_certificado' => null,
            'sunat_clave_certificado' => null,
            'sunat_modo_prueba' => true,
            'sunat_envio_automatico' => false,
            'sunat_generar_pdf' => true,

            // Configuración de Impresoras
            'impresora_principal' => null,
            'impresora_tickets' => null,
            'impresora_reportes' => null,
            'impresion_automatica' => false,
            'copias_ticket' => config('sistema.impresoras.copias_ticket_default'),
            'papel_ancho' => config('sistema.impresoras.papel_ancho_default'),

            // Configuración de Tickets
            'mostrar_logo_ticket' => config('sistema.tickets.mostrar_logo_default'),
            'mostrar_direccion_ticket' => config('sistema.tickets.mostrar_direccion_default'),
            'mostrar_telefono_ticket' => config('sistema.tickets.mostrar_telefono_default'),
            'mostrar_igv_desglose' => config('sistema.tickets.mostrar_igv_default'),
            'margen_superior_ticket' => config('sistema.tickets.margen_superior_default'),
            'margen_inferior_ticket' => config('sistema.tickets.margen_inferior_default'),
            'pie_pagina_ticket' => config('sistema.tickets.pie_pagina_default'),

            // Configuración de Comprobantes
            'serie_factura' => config('sistema.comprobantes.serie_factura_default'),
            'serie_boleta' => config('sistema.comprobantes.serie_boleta_default'),
            'serie_ticket' => config('sistema.comprobantes.serie_ticket_default'),
            'numeracion_factura' => config('sistema.comprobantes.numeracion_inicial'),
            'numeracion_boleta' => config('sistema.comprobantes.numeracion_inicial'),
            'numeracion_ticket' => config('sistema.comprobantes.numeracion_inicial'),
            'papel_size' => config('sistema.comprobantes.papel_size_default'),
            'orientacion_papel' => config('sistema.comprobantes.orientacion_default'),
            'mostrar_qr' => config('sistema.comprobantes.mostrar_qr_default'),
            'mostrar_hash' => config('sistema.comprobantes.mostrar_hash_default'),
            'copias_factura' => config('sistema.comprobantes.copias_factura_default'),
            'copias_boleta' => config('sistema.comprobantes.copias_boleta_default'),

            // Configuración de Alertas
            'alertas_stock_minimo' => true,
            'stock_minimo_global' => config('sistema.alertas.stock_minimo_global_default'),
            'dias_anticipacion_vencimiento' => config('sistema.alertas.dias_anticipacion_vencimiento_default'),
            'dias_anticipacion_stock' => config('sistema.alertas.dias_anticipacion_stock_default'),
            'alertas_vencimiento' => true,
            'nivel_criticidad_alertas' => config('sistema.alertas.nivel_criticidad_default'),
            'alertas_email_habilitado' => false,
            'alertas_email_direccion' => null,
            'frecuencia_alertas_email' => config('sistema.alertas.frecuencia_email_default'),
            'alertas_sistema_habilitado' => true,
            'alertas_ventas_bajas' => false,
            'alertas_productos_inactivos' => false,
            'alertas_respaldo' => true,
            'alertas_actualizaciones' => true,

            // Configuración de Caché
            'cache_habilitado' => true,
            'limpieza_cache_automatica' => false,
            'frecuencia_limpieza_cache' => 'semanal',

            // Configuración adicional del sistema
            'sistema_version' => config('sistema.sistema.version'),
            'sistema_timezone' => config('sistema.sistema.timezone_default'),
            'sistema_mantenimiento' => false,
            'sistema_debug' => false,
        ]);

        $this->command->info('✅ Configuraciones por defecto creadas exitosamente.');
    }
}
