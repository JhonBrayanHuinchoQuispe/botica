<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar cache de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // CREAR PERMISOS COMPLETOS
        $permissions = [
            // Dashboard
            'dashboard.view' => 'Ver Dashboard',
            'dashboard.analytics' => 'Ver Análisis',
            'dashboard.alerts' => 'Ver Alertas',

            // Ventas y Punto de Venta
            'ventas.view' => 'Ver Ventas',
            'ventas.create' => 'Crear Ventas',
            'ventas.edit' => 'Editar Ventas',
            'ventas.delete' => 'Eliminar Ventas',
            'ventas.reports' => 'Reportes de Ventas',
            'ventas.devoluciones' => 'Procesar Devoluciones',
            'ventas.clientes' => 'Gestionar Clientes',

            // Inventario y Productos
            'inventario.view' => 'Ver Inventario',
            'inventario.create' => 'Agregar Productos',
            'inventario.edit' => 'Editar Productos',
            'inventario.delete' => 'Eliminar Productos',
            'inventario.categories' => 'Gestionar Categorías y Presentaciones',
            'inventario.stock' => 'Gestionar Stock',

            // Ubicaciones y Almacén
            'ubicaciones.view' => 'Ver Ubicaciones',
            'ubicaciones.create' => 'Crear Ubicaciones',
            'ubicaciones.edit' => 'Editar Ubicaciones',
            'ubicaciones.delete' => 'Eliminar Ubicaciones',
            'ubicaciones.manage' => 'Gestionar Mapa del Almacén',
            'ubicaciones.move' => 'Mover Productos en Almacén',

            // Compras y Proveedores
            'compras.view' => 'Ver Compras',
            'compras.create' => 'Crear Compras',
            'compras.edit' => 'Editar Compras',
            'compras.delete' => 'Eliminar Compras',
            'compras.providers' => 'Gestionar Proveedores',
            'compras.entrada' => 'Entrada de Mercadería',

            // Usuarios y Roles
            'usuarios.view' => 'Ver Usuarios',
            'usuarios.create' => 'Crear Usuarios',
            'usuarios.edit' => 'Editar Usuarios',
            'usuarios.delete' => 'Eliminar Usuarios',
            'usuarios.roles' => 'Gestionar Roles y Permisos',
            'usuarios.activate' => 'Activar/Desactivar Usuarios',

            // Configuración del Sistema
            'config.system' => 'Configuración del Sistema',
            'config.backups' => 'Gestionar Respaldos',
            'config.logs' => 'Ver Logs del Sistema',
            'config.general' => 'Configuración General',

            // Perfil Personal
            'perfil.view' => 'Ver Perfil',
            'perfil.edit' => 'Editar Perfil',
        ];

        foreach ($permissions as $permission => $displayName) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // CREAR ROLES ESPECÍFICOS PARA FARMACIA
        
        // 1. DUEÑO - Acceso total y único (nivel más alto)
        $dueno = Role::firstOrCreate([
            'name' => 'dueño',
            'guard_name' => 'web'
        ]);
        $dueno->syncPermissions(Permission::all());

        // 2. GERENTE - Acceso casi total (segundo nivel)
        $gerente = Role::firstOrCreate([
            'name' => 'gerente',
            'guard_name' => 'web'
        ]);
        $gerente->syncPermissions(Permission::all());

        // 3. ADMINISTRADOR - Acceso casi completo (solo sin logs críticos)
        $admin = Role::firstOrCreate([
            'name' => 'administrador',
            'guard_name' => 'web'
        ]);
        $adminPermisos = Permission::whereNotIn('name', [
            'config.logs' // Solo sin acceso a logs críticos del sistema
        ])->get();
        $admin->syncPermissions($adminPermisos);

        // 4. VENDEDOR - Solo ventas y consultas básicas
        $vendedor = Role::firstOrCreate([
            'name' => 'vendedor',
            'guard_name' => 'web'
        ]);
        $vendedor->syncPermissions([
            'dashboard.view',
            'ventas.view', 
            'ventas.create',
            'ventas.devoluciones',
            'ventas.clientes',
            'inventario.view', // Solo para consultar productos (no puede editar)
            'perfil.view', 
            'perfil.edit'
        ]);

        // 5. VENDEDOR-ALMACENERO - Ventas + inventario + almacén (empleado completo)
        $vendedorAlmacenero = Role::firstOrCreate([
            'name' => 'vendedor-almacenero',
            'guard_name' => 'web'
        ]);
        $vendedorAlmacenero->syncPermissions([
            'dashboard.view', 
            'dashboard.analytics',
            'ventas.view', 
            'ventas.create',
            'ventas.devoluciones',
            'ventas.clientes',
            'inventario.view', 
            'inventario.create', 
            'inventario.edit',
            'inventario.categories',
            'inventario.stock',
            'ubicaciones.view', 
            'ubicaciones.manage', 
            'ubicaciones.move',
            'compras.view',
            'compras.entrada',
            'perfil.view', 
            'perfil.edit'
        ]);

        // 6. ALMACENERO - Solo inventario, ubicaciones y entrada de mercadería
        $almacenero = Role::firstOrCreate([
            'name' => 'almacenero',
            'guard_name' => 'web'
        ]);
        $almacenero->syncPermissions([
            'dashboard.view',
            'inventario.view', 
            'inventario.create', 
            'inventario.edit',
            'inventario.categories',
            'inventario.stock',
            'ubicaciones.view', 
            'ubicaciones.create',
            'ubicaciones.edit',
            'ubicaciones.manage', 
            'ubicaciones.move',
            'compras.view',
            'compras.create',
            'compras.edit',
            'compras.entrada',
            'compras.providers',
            'perfil.view', 
            'perfil.edit'
        ]);

        // 7. SUPERVISOR - Acceso medio con reportes y supervisión
        $supervisor = Role::firstOrCreate([
            'name' => 'supervisor',
            'guard_name' => 'web'
        ]);
        $supervisor->syncPermissions([
            'dashboard.view', 
            'dashboard.analytics',
            'dashboard.alerts',
            'ventas.view', 
            'ventas.create',
            'ventas.reports',
            'ventas.devoluciones',
            'ventas.clientes',
            'inventario.view', 
            'inventario.create', 
            'inventario.edit',
            'inventario.categories',
            'ubicaciones.view', 
            'ubicaciones.manage',
            'compras.view',
            'compras.create',
            'usuarios.view', // Solo ver usuarios (no puede editarlos)
            'perfil.view', 
            'perfil.edit'
        ]);

        // ASIGNAR ROL DUEÑO AL PRIMER USUARIO (cuenta por defecto del dueño)
        $primerUsuario = User::first();
        if ($primerUsuario) {
            $primerUsuario->syncRoles(['dueño']);
            echo "✅ Usuario '{$primerUsuario->name}' asignado como DUEÑO (cuenta principal)\n";
        }

        echo "\n🎯 SISTEMA DE ROLES Y PERMISOS PARA FARMACIA COMPLETADO:\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "📋 " . Permission::count() . " permisos creados\n";
        echo "👥 7 roles jerárquicos creados:\n";
        echo "   👑 Dueño - Acceso total y único (cuenta principal)\n";
        echo "   🏢 Gerente - Acceso casi total (segundo al mando)\n";
        echo "   ⚙️  Administrador - Acceso administrativo (sin logs críticos)\n";
        echo "   🛒 Vendedor - Solo ventas y consultas\n";
        echo "   📦 Vendedor-Almacenero - Ventas + inventario + almacén\n";
        echo "   📋 Almacenero - Solo inventario y almacén\n";
        echo "   👁️  Supervisor - Acceso medio con reportes y supervisión\n";
        echo "🏥 Sistema jerárquico para farmacia configurado y listo\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    }
}
