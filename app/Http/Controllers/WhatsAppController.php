<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PuntoVenta\Venta;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    /**
     * Enviar boleta por WhatsApp
     */
    public function enviarBoleta(Request $request)
    {
        try {
            $request->validate([
                'venta_id' => 'required|exists:ventas,id',
                'telefono' => 'required|string|min:9|max:20',
                'tipo_comprobante' => 'required|in:ticket,boleta_a4',
                'guardar_en_cliente' => 'sometimes|boolean'
            ]);

            $venta = Venta::with(['cliente', 'detalles.producto', 'usuario'])
                         ->findOrFail($request->venta_id);

            $telefono = $this->formatearTelefono($request->telefono);
            
            // Generar el mensaje de WhatsApp
            $mensaje = $this->generarMensajeWhatsApp($venta, $request->tipo_comprobante);
            
            // Generar URL de WhatsApp
            $urlWhatsApp = $this->generarUrlWhatsApp($telefono, $mensaje, $venta, $request->tipo_comprobante);

            // Guardar teléfono en el cliente si se solicita y existe cliente
            if ($request->boolean('guardar_en_cliente') && $venta->cliente) {
                try {
                    $venta->cliente->telefono = $telefono; // Guardamos en formato con código (sin +)
                    $venta->cliente->save();
                } catch (\Exception $e) {
                    Log::warning('No se pudo guardar el teléfono en el cliente: ' . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'url_whatsapp' => $urlWhatsApp,
                'mensaje' => 'URL de WhatsApp generada correctamente',
                'telefono_formateado' => $telefono
            ]);

        } catch (\Exception $e) {
            Log::error('Error al generar URL de WhatsApp: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el enlace de WhatsApp: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Formatear número de teléfono para WhatsApp
     */
    private function formatearTelefono($telefono)
    {
        // Remover espacios, guiones y caracteres especiales
        $telefono = preg_replace('/[^0-9]/', '', $telefono ?? '');

        if (!$telefono) {
            throw new \Exception('Número de teléfono vacío');
        }

        // Quitar prefijo internacional 00 si existe (p.ej. 00519XXXXXXXX)
        if (str_starts_with($telefono, '00')) {
            $telefono = substr($telefono, 2);
        }

        // Caso: 9XXXXXXXX (móvil peruano sin código)
        if (strlen($telefono) === 9 && str_starts_with($telefono, '9')) {
            return '51' . $telefono;
        }

        // Caso: 519XXXXXXXX (con código Perú correcto)
        if (strlen($telefono) === 11 && str_starts_with($telefono, '51') && substr($telefono, 2, 1) === '9') {
            return $telefono;
        }

        // Caso inválido
        throw new \Exception('Número inválido. Debe ser un móvil de Perú (empieza con 9 y tiene 9 dígitos).');
    }

    /**
     * Generar mensaje personalizado para WhatsApp
     */
    private function generarMensajeWhatsApp($venta, $tipoComprobante)
    {
        $nombreEmpresa = "🏥 Botica San Antonio";
        $tipoComprobanteTexto = $tipoComprobante === 'ticket' ? 'Boleta Simple' : 'Boleta A4';
        
        $mensaje = "¡Hola! 👋\n\n";
        $mensaje .= "Gracias por tu compra en {$nombreEmpresa}\n\n";
        $mensaje .= "📋 *Detalles de tu compra:*\n";
        $mensaje .= "• Número de venta: *{$venta->numero_venta}*\n";
        $mensaje .= "• Fecha: *" . $venta->fecha_venta->format('d/m/Y H:i') . "*\n";
        $mensaje .= "• Total: *S/. " . number_format($venta->total, 2) . "*\n";
        $mensaje .= "• Comprobante: *{$tipoComprobanteTexto}*\n\n";
        
        if ($venta->cliente) {
            $mensaje .= "👤 Cliente: *{$venta->cliente->nombre_completo}*\n\n";
        }
        
        $mensaje .= "📄 *Tu boleta electrónica:*\n";
        $mensaje .= "Haz clic en el enlace de abajo para ver y descargar tu comprobante:\n\n";
        
        return $mensaje;
    }

    /**
     * Generar URL completa de WhatsApp con enlace al comprobante
     */
    private function generarUrlWhatsApp($telefono, $mensaje, $venta, $tipoComprobante)
    {
        // URL del comprobante según el tipo
        $urlComprobante = $tipoComprobante === 'ticket' 
            ? url("/punto-venta/ticket/{$venta->id}")
            : url("/punto-venta/pdf/{$venta->id}");
        
        // Agregar URL del comprobante al mensaje
        $mensajeCompleto = $mensaje . $urlComprobante . "\n\n";
        $mensajeCompleto .= "💊 ¡Gracias por confiar en nosotros para tu salud!\n";
        $mensajeCompleto .= "🕒 Horario de atención: Lunes a Domingo 8:00am - 10:00pm\n";
        $mensajeCompleto .= "📍 Av. Ferrocarril 118, CHILCA 12003";
        
        // Codificar mensaje para URL
        $mensajeCodificado = urlencode($mensajeCompleto);
        
        // Generar URL de WhatsApp (wa.me requiere código de país sin signos)
        $urlWhatsApp = "https://wa.me/{$telefono}?text={$mensajeCodificado}";
        
        return $urlWhatsApp;
    }

    /**
     * Obtener información de la venta para WhatsApp
     */
    public function obtenerInfoVenta($ventaId)
    {
        try {
            $venta = Venta::with(['cliente', 'detalles.producto'])
                         ->findOrFail($ventaId);

            return response()->json([
                'success' => true,
                'venta' => [
                    'id' => $venta->id,
                    'numero_venta' => $venta->numero_venta,
                    'total' => $venta->total,
                    'fecha_venta' => $venta->fecha_venta->format('d/m/Y H:i'),
                    'cliente' => $venta->cliente ? [
                        'nombre_completo' => $venta->cliente->nombre_completo,
                        'telefono' => $venta->cliente->telefono ?? ''
                    ] : null
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener información de la venta'
            ], 500);
        }
    }
}