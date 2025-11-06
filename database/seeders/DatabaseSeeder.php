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

        // Crear usuario principal (Dueño)
        $userDueno = User::firstOrCreate(
            ['email' => 'brayanhuincho975@gmail.com'],
            [
                'name' => 'Brayan Huincho',
                'nombres' => 'Brayan',
                'apellidos' => 'Huincho',
                'password' => Hash::make('brayan933783039'),
                'telefono' => '931815749',
                'direccion' => 'Dirección Principal',
                'is_active' => true,
            ]
        );

        // Asignar rol Dueño
        $userDueno->syncRoles(['dueño']);

        $this->command->info('✅ Usuario Dueño verificado:');
        $this->command->info('   Email: brayanhuincho975@gmail.com');
        $this->command->info('   Password: brayan933783039');
        $this->command->info('   Rol: Dueño (acceso total)');

        // Crear usuario Administrador de prueba
        $userAdmin = User::firstOrCreate(
            ['email' => 'prueba@gmail.com'],
            [
                'name' => 'Usuario Prueba',
                'nombres' => 'Usuario',
                'apellidos' => 'Prueba',
                'password' => Hash::make('prueba12345'),
                'telefono' => '000000000',
                'direccion' => '—',
                'is_active' => true,
            ]
        );
        $userAdmin->syncRoles(['administrador']);

        $this->command->info('✅ Usuario Administrador creado:');
        $this->command->info('   Email: prueba@gmail.com');
        $this->command->info('   Password: prueba12345');
        $this->command->info('   Rol: Administrador');
    }
}