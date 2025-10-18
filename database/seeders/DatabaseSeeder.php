<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Crear roles y permisos primero
        $this->call(RolesAndPermissionsSeeder::class);

        // Crear usuario administrador principal 
        $user = User::firstOrCreate(
            ['email' => 'brayanhuincho975@gmail.com'],
            [
                'name' => 'Brayan Huincho',
                'nombres' => 'Brayan',
                'apellidos' => 'Huincho',
                'password' => Hash::make('brayan933783039'),
                'cargo' => 'Gerente General',
                'telefono' => '931815749',
                'direccion' => 'Dirección Principal',
                'is_active' => true,
            ]
        );

        // Asignar rol de administrador (tiene todos los permisos)
        $user->assignRole('Gerente');
        
        $this->command->info('✅ Usuario administrador verificado:');
        $this->command->info('   Email: brayanhuincho975@gmail.com');
        $this->command->info('   Password: brayan933783039');
        $this->command->info('   Rol: Administrador (acceso completo)');
    }
}