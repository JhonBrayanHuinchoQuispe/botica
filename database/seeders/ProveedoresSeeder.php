<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Proveedor;

class ProveedoresSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $proveedores = [
            [
                'nombre' => 'Laboratorios Farmaindustria S.A.',
                'ruc' => '20123456789',
                'telefono' => '01-234-5678',
                'email' => 'ventas@farmaindustria.com.pe',
                'direccion' => 'Av. Industrial 123, Lima, Lima',
                'contacto_nombre' => 'María González',
                'contacto_cargo' => 'Gerente de Ventas',
                'observaciones' => 'Proveedor principal de medicamentos genéricos',
                'activo' => true
            ],
            [
                'nombre' => 'Distribuidora Médica del Perú S.A.C.',
                'ruc' => '20987654321',
                'telefono' => '01-876-5432',
                'email' => 'pedidos@medicdist.pe',
                'direccion' => 'Jr. Los Antibióticos 456, San Borja, Lima',
                'contacto_nombre' => 'Carlos Rodríguez',
                'contacto_cargo' => 'Ejecutivo de Cuentas',
                'observaciones' => 'Especializado en productos importados',
                'activo' => true
            ],
            [
                'nombre' => 'Química Suiza S.A.',
                'ruc' => '20147258369',
                'telefono' => '01-369-2580',
                'email' => 'comercial@quimicasuiza.com',
                'direccion' => 'Av. República de Panamá 789, San Isidro, Lima',
                'contacto_nombre' => 'Ana Martínez',
                'contacto_cargo' => 'Coordinadora Comercial',
                'observaciones' => 'Productos de alta calidad, entrega puntual',
                'activo' => true
            ],
            [
                'nombre' => 'Droguería San Martín E.I.R.L.',
                'ruc' => '20456789123',
                'telefono' => '01-789-4561',
                'email' => 'ventas@sanmartin.pe',
                'direccion' => 'Av. Grau 321, Cercado de Lima, Lima',
                'contacto_nombre' => 'José Fernández',
                'contacto_cargo' => 'Representante de Ventas',
                'observaciones' => 'Proveedor local con buenos precios',
                'activo' => true
            ],
            [
                'nombre' => 'Laboratorio Nacional S.A.',
                'ruc' => '20789123456',
                'telefono' => '01-456-7890',
                'email' => 'info@labnacional.com.pe',
                'direccion' => 'Calle Las Medicinas 654, Miraflores, Lima',
                'contacto_nombre' => 'Patricia López',
                'contacto_cargo' => 'Gerente Regional',
                'observaciones' => 'Especializado en medicamentos controlados',
                'activo' => true
            ],
            [
                'nombre' => 'Insumos Médicos del Norte S.A.C.',
                'ruc' => '20321654987',
                'telefono' => '074-123456',
                'email' => 'pedidos@medicalinsumos.pe',
                'direccion' => 'Av. España 852, Trujillo, La Libertad',
                'contacto_nombre' => 'Roberto Silva',
                'contacto_cargo' => 'Jefe de Ventas',
                'observaciones' => 'Proveedor regional, buenos tiempos de entrega',
                'activo' => true
            ],
            [
                'nombre' => 'Distribuidora Farma Express S.R.L.',
                'ruc' => '20654987321',
                'telefono' => '01-987-6543',
                'email' => 'express@farmaexpress.com',
                'direccion' => 'Jr. Medicamentos 159, Breña, Lima',
                'contacto_nombre' => 'Elena Torres',
                'contacto_cargo' => 'Supervisora de Cuentas',
                'observaciones' => 'Entrega express en Lima Metropolitana',
                'activo' => true
            ],
            [
                'nombre' => 'Laboratorios Andinos S.A.',
                'ruc' => '20852741963',
                'telefono' => '054-789123',
                'email' => 'comercial@labandinos.pe',
                'direccion' => 'Av. Parra 741, Arequipa, Arequipa',
                'contacto_nombre' => 'Miguel Herrera',
                'contacto_cargo' => 'Director Comercial',
                'observaciones' => 'Productos naturales y fitofármacos',
                'activo' => true
            ],
            [
                'nombre' => 'Proveedor Temporal (Inactivo)',
                'ruc' => '20111222333',
                'telefono' => '01-111-2222',
                'email' => 'temporal@ejemplo.com',
                'direccion' => 'Dirección temporal',
                'contacto_nombre' => 'Contacto Temporal',
                'contacto_cargo' => 'Temporal',
                'observaciones' => 'Proveedor desactivado para pruebas',
                'activo' => false
            ]
        ];

        foreach ($proveedores as $proveedor) {
            Proveedor::create($proveedor);
        }

        $this->command->info('✅ Se crearon ' . count($proveedores) . ' proveedores de prueba');
    }
}
