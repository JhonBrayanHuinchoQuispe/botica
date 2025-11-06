<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Proveedor;

class ProveedorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $proveedores = [
            [
                'codigo_proveedor' => 'PROV001',
                'razon_social' => 'Laboratorios Genfar S.A.',
                'nombre_comercial' => 'Genfar',
                'ruc' => '20123456789',
                'telefono' => '01-2345678',
                'email' => 'ventas@genfar.com',
                'direccion' => 'Av. Industrial 123',
                'ciudad' => 'Lima',
                'departamento' => 'Lima',
                'contacto_principal' => 'María García',
                'telefono_contacto' => '987654321',
                'email_contacto' => 'maria.garcia@genfar.com',
                'estado' => 'activo',
                'observaciones' => 'Proveedor principal de medicamentos genéricos',
                'limite_credito' => 50000.00,
                'dias_credito' => 30,
                'categoria_proveedor' => 'Medicamentos'
            ],
            [
                'codigo_proveedor' => 'PROV002',
                'razon_social' => 'Droguería Farmacorp S.A.C.',
                'nombre_comercial' => 'Farmacorp',
                'ruc' => '20234567890',
                'telefono' => '01-3456789',
                'email' => 'contacto@farmacorp.com',
                'direccion' => 'Jr. Comercio 456',
                'ciudad' => 'Lima',
                'departamento' => 'Lima',
                'contacto_principal' => 'Carlos Rodríguez',
                'telefono_contacto' => '987654322',
                'email_contacto' => 'carlos.rodriguez@farmacorp.com',
                'estado' => 'activo',
                'observaciones' => 'Distribuidor de productos farmacéuticos y cosméticos',
                'limite_credito' => 30000.00,
                'dias_credito' => 15,
                'categoria_proveedor' => 'Medicamentos'
            ],
            [
                'codigo_proveedor' => 'PROV003',
                'razon_social' => 'Distribuidora Médica del Perú S.A.',
                'nombre_comercial' => 'Dimedsa',
                'ruc' => '20345678901',
                'telefono' => '01-4567890',
                'email' => 'ventas@dimedsa.com',
                'direccion' => 'Av. Universitaria 789',
                'ciudad' => 'Lima',
                'departamento' => 'Lima',
                'contacto_principal' => 'Ana López',
                'telefono_contacto' => '987654323',
                'email_contacto' => 'ana.lopez@dimedsa.com',
                'estado' => 'activo',
                'observaciones' => 'Especialista en productos de marca',
                'limite_credito' => 75000.00,
                'dias_credito' => 45,
                'categoria_proveedor' => 'Medicamentos'
            ],
            [
                'codigo_proveedor' => 'PROV004',
                'razon_social' => 'Química Suiza S.A.',
                'nombre_comercial' => 'Química Suiza',
                'ruc' => '20456789012',
                'telefono' => '01-5678901',
                'email' => 'info@quimicasuiza.com',
                'direccion' => 'Av. Argentina 321',
                'ciudad' => 'Lima',
                'departamento' => 'Lima',
                'contacto_principal' => 'Luis Martínez',
                'telefono_contacto' => '987654324',
                'email_contacto' => 'luis.martinez@quimicasuiza.com',
                'estado' => 'activo',
                'observaciones' => 'Productos de alta calidad y medicamentos especializados',
                'limite_credito' => 40000.00,
                'dias_credito' => 30,
                'categoria_proveedor' => 'Medicamentos'
            ],
            [
                'codigo_proveedor' => 'PROV005',
                'razon_social' => 'Productos Naturales San Jorge E.I.R.L.',
                'nombre_comercial' => 'San Jorge Natural',
                'ruc' => '20567890123',
                'telefono' => '01-6789012',
                'email' => 'ventas@sanjorgenatural.com',
                'direccion' => 'Jr. Junín 654',
                'ciudad' => 'Lima',
                'departamento' => 'Lima',
                'contacto_principal' => 'Rosa Flores',
                'telefono_contacto' => '987654325',
                'email_contacto' => 'rosa.flores@sanjorgenatural.com',
                'estado' => 'activo',
                'observaciones' => 'Especialista en productos naturales y suplementos',
                'limite_credito' => 20000.00,
                'dias_credito' => 15,
                'categoria_proveedor' => 'Productos Naturales'
            ]
        ];

        foreach ($proveedores as $proveedor) {
            Proveedor::create($proveedor);
        }
    }
}
