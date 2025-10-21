# Sistema de Botica - Farmacia

Sistema de punto de venta optimizado para farmacias, desarrollado en Laravel con base de datos MySQL.

## Características

- Gestión de productos y inventario
- Control de ventas y facturación
- Administración de clientes y proveedores
- Gestión de ubicaciones y lotes
- Sistema de usuarios y permisos
- Base de datos optimizada para rendimiento

## Requisitos del Sistema

- PHP >= 8.1
- Composer
- MySQL >= 5.7 o MariaDB >= 10.3
- Node.js >= 16.x (para compilar assets)
- XAMPP, WAMP o servidor web similar

## Instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/JhonBrayanHuinchoQuispe/botica.git
cd botica
```

### 2. Instalar dependencias de PHP

```bash
composer install
```

### 3. Instalar dependencias de Node.js

```bash
npm install
```

### 4. Configurar el archivo de entorno

```bash
# Copiar el archivo de ejemplo
cp .env.example .env

# Generar la clave de aplicación
php artisan key:generate
```

### 5. Configurar la base de datos

Edita el archivo `.env` con los datos de tu base de datos:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=botica_sistema
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

### 6. Crear la base de datos

Crea una base de datos llamada `botica_sistema` en tu servidor MySQL.

### 7. Ejecutar migraciones

```bash
# Ejecutar todas las migraciones
php artisan migrate
```

### 8. Ejecutar seeders (datos iniciales)

```bash
# Ejecutar todos los seeders
php artisan db:seed

# O ejecutar seeders específicos
php artisan db:seed --class=RolesAndPermissionsSeeder
php artisan db:seed --class=ConfiguracionSistemaSeeder
php artisan db:seed --class=PuntoVentaSeeder
```

### 9. Compilar assets (opcional)

```bash
# Para desarrollo
npm run dev

# Para producción
npm run build
```

### 10. Configurar permisos (Linux/Mac)

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

## Ejecución

### Servidor de desarrollo

```bash
php artisan serve
```

El sistema estará disponible en: `http://127.0.0.1:8000`

### Servidor web (Apache/Nginx)

Configura tu servidor web para que apunte al directorio `public/` del proyecto.

## Usuarios por defecto

Después de ejecutar los seeders, tendrás acceso con:

- **Administrador**: 
  - Email: admin@botica.com
  - Contraseña: admin123

- **Vendedor**:
  - Email: vendedor@botica.com
  - Contraseña: vendedor123

## Comandos útiles

### Limpiar caché

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Regenerar autoload

```bash
composer dump-autoload
```

### Ejecutar migraciones específicas

```bash
# Ver estado de migraciones
php artisan migrate:status

# Rollback última migración
php artisan migrate:rollback

# Refrescar base de datos (cuidado: elimina todos los datos)
php artisan migrate:fresh --seed
```

## Estructura del proyecto

```
├── app/                    # Lógica de la aplicación
│   ├── Http/Controllers/   # Controladores
│   ├── Models/            # Modelos Eloquent
│   ├── Services/          # Servicios de negocio
│   └── Repositories/      # Repositorios de datos
├── database/
│   ├── migrations/        # Migraciones de base de datos
│   └── seeders/          # Datos iniciales
├── resources/
│   └── views/            # Vistas Blade
├── routes/               # Definición de rutas
└── public/              # Archivos públicos
```

## Optimizaciones Realizadas

- Eliminación de campos SUNAT innecesarios
- Limpieza de campos de observaciones no utilizados
- Optimización de estructura de base de datos
- Mejora en el rendimiento del sistema
- Índices optimizados para consultas frecuentes

## Soporte

Para reportar problemas o solicitar nuevas características, por favor crea un issue en el repositorio de GitHub.

## Licencia

Este proyecto es de código abierto y está disponible bajo la [Licencia MIT](LICENSE).
