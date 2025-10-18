# 📁 Configuración de Estructura del Estante

Esta carpeta contiene los archivos especializados para la funcionalidad de configuración de estructura del estante.

## 🗂️ Estructura de Archivos

### CSS
- `estructura-modal.css` - Estilos del modal principal
- `estructura-form.css` - Estilos de formularios y controles

### JavaScript
- `estructura-modal.js` - Clase principal del modal de configuración

## 🎯 Funcionalidades

### Modal de Configuración
- ✅ Apertura/cierre animado
- ✅ Comparación de configuración actual vs nueva
- ✅ Controles interactivos (+/-) para niveles y posiciones
- ✅ Vista previa en tiempo real de la nueva estructura
- ✅ Sistema de advertencias dinámicas
- ✅ Validaciones automáticas
- ✅ Aplicación de cambios con confirmación

### Características del Diseño
- 🎨 Gradientes elegantes en rojo (#e53e3e)
- 🎭 Animaciones suaves y profesionales
- 📱 Diseño completamente responsive
- 🔍 Vista previa visual de slots
- ⚠️ Sistema de alertas contextuales

## 🚀 Uso

El modal se inicializa automáticamente cuando se carga la página:

```javascript
// Se inicializa automáticamente
window.ConfiguracionModal = new ConfiguracionEstructuraModal();

// Para abrir programáticamente
window.ConfiguracionModal.abrir();

// Para cerrar programáticamente
window.ConfiguracionModal.cerrar();
```

## 🔧 Configuración

La configuración actual se puede personalizar modificando los valores iniciales en `estructura-modal.js`:

```javascript
this.configuracionActual = {
    niveles: 4,           // Número de niveles del estante
    posiciones: 5,        // Posiciones por nivel
    productosUbicados: 12 // Productos actualmente ubicados
};
``` 