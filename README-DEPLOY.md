# Despliegue en Render + MySQL (AlwaysData)

Este proyecto está listo para desplegarse en Render usando Docker y conectarse a una base MySQL alojada en AlwaysData.

## Requisitos

- Cuenta en GitHub con el repositorio de este proyecto.
- Cuenta en Render (https://render.com) con permisos para leer tu repo.
- Base de datos MySQL en AlwaysData (host, puerto, nombre, usuario y password).

## Archivos clave

- `Dockerfile`: imagen PHP 8.2 con extensiones de Laravel; sirve `public/` usando el servidor embebido.
- `.dockerignore`: evita enviar archivos innecesarios al contexto de build.
- `render.yaml` (opcional): configura el servicio y el comando post-deploy. Puedes usar el UI de Render si prefieres.
- `.env.production.example`: preparado para leer `DB_*` desde variables de entorno.

## Pasos

1. Haz push de este repo a GitHub.
2. En Render → New → Web Service → Connect repo → selecciona el repo y la rama `main`.
3. Runtime: `Docker`. Region: la que prefieras.
4. Variables de entorno (Environment):
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_KEY=<tu-clave>`
   - `DB_CONNECTION=mysql`
   - `DB_HOST=jhonbrayanhuinchoquispe.alwaysdata.net`
   - `DB_PORT=3306`
   - `DB_DATABASE=sistemasic_botica`
   - `DB_USERNAME=436286`
   - `DB_PASSWORD=brayan933783039`
5. Post-Deploy Command: `npm install && npm run build && php artisan migrate --force && php artisan db:seed --force && rm -rf public/storage && php artisan storage:link || true`
   - `npm install`: Instala las dependencias de Node.js
   - `npm run build`: Compila los assets (CSS, JS) para producción
   - `php artisan migrate --force`: Ejecuta las migraciones de base de datos
   - `php artisan db:seed --force`: Ejecuta los seeders para poblar la base de datos
   - `rm -rf public/storage`: Elimina el enlace simbólico anterior (si existe)
   - `php artisan storage:link`: Crea el enlace simbólico para el storage
6. Deploy. Cuando el servicio esté `Deployed`, abre el URL público.

## Notas

- No subas `.env` reales ni certificados sensibles. Usa variables de entorno en Render.
- El filesystem del contenedor es efímero; para producción considera un bucket S3 para archivos subidos.
- Para colas: crea un `Background Worker` con `php artisan queue:work --sleep=3 --tries=3`.
- Para tareas programadas: usa `Cron Jobs` con `php artisan schedule:run` cada minuto.