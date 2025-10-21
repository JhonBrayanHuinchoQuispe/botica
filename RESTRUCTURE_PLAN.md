# Plan de Reestructuración de Vistas

## Estructura Actual vs Propuesta

### Estructura Propuesta:
```
resources/views/
├── layouts/                    # Layouts principales
│   ├── app.blade.php          # Layout principal de la aplicación
│   ├── guest.blade.php        # Layout para invitados
│   └── navigation.blade.php   # Navegación
├── components/                 # Componentes reutilizables
│   ├── ui/                    # Componentes de UI básicos
│   ├── forms/                 # Componentes de formularios
│   └── navigation/            # Componentes de navegación
├── pages/                     # Páginas principales del sistema
│   ├── dashboard/
│   ├── inventario/
│   ├── ventas/
│   ├── compras/
│   ├── admin/
│   └── auth/
├── partials/                  # Vistas parciales
└── emails/                    # Templates de email
```

## Archivos a Eliminar:
- [ ] sidebar.blade copy.php
- [ ] productos.blade copy 3/
- [ ] tableData.blade copy.php
- [ ] tableData.blade copy 2.php
- [ ] estante-detalle.blade copy.php
- [ ] estante-detalle.blade copy 2.php
- [ ] Todo el directorio componentspage/ (demos)
- [ ] Archivos sueltos no utilizados

## Archivos a Reorganizar:
- [ ] Mover dashboard.blade.php a pages/dashboard/
- [ ] Consolidar layouts
- [ ] Reorganizar componentes reales