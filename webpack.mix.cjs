const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

// Configuración de producción
if (mix.inProduction()) {
    mix.version();
    mix.options({
        terser: {
            terserOptions: {
                compress: {
                    drop_console: true,
                    drop_debugger: true,
                },
            },
        },
    });
}

// CSS Principal
mix.styles([
    'public/css/bootstrap.min.css',
    'public/css/style.css',
    'public/css/custom.css'
], 'public/dist/css/app.css');

// CSS de AdminLTE si existe
if (mix.File.exists('public/css/adminlte.min.css')) {
    mix.styles([
        'public/css/adminlte.min.css',
        'public/css/adminlte-custom.css'
    ], 'public/dist/css/admin.css');
}

// JavaScript Principal
mix.scripts([
    'public/js/jquery.min.js',
    'public/js/bootstrap.bundle.min.js',
    'public/js/app.js'
], 'public/dist/js/app.js');

// JavaScript de AdminLTE si existe
if (mix.File.exists('public/js/adminlte.min.js')) {
    mix.scripts([
        'public/js/adminlte.min.js',
        'public/js/admin-custom.js'
    ], 'public/dist/js/admin.js');
}

// JavaScript específico de módulos
mix.scripts([
    'public/js/inventario.js',
    'public/js/ventas.js',
    'public/js/dashboard.js'
], 'public/dist/js/modules.js');

// Optimizaciones adicionales
mix.options({
    processCssUrls: false,
    postCss: [
        require('autoprefixer'),
        require('cssnano')({
            preset: 'default',
        }),
    ],
});

// Configuración de desarrollo
if (!mix.inProduction()) {
    mix.sourceMaps();
    mix.browserSync({
        proxy: 'localhost:8000',
        files: [
            'app/**/*.php',
            'resources/views/**/*.php',
            'public/js/**/*.js',
            'public/css/**/*.css'
        ]
    });
}

// Copiar assets optimizados
mix.copy('public/images', 'public/dist/images');
mix.copy('public/fonts', 'public/dist/fonts');

// Configuración de Webpack personalizada
mix.webpackConfig({
    resolve: {
        alias: {
            '@': path.resolve('resources/js'),
        },
    },
    optimization: {
        splitChunks: {
            chunks: 'all',
            cacheGroups: {
                vendor: {
                    test: /[\\/]node_modules[\\/]/,
                    name: 'vendor',
                    chunks: 'all',
                },
            },
        },
    },
});