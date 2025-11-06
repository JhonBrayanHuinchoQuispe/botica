<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Botica San Antonio</title>
    <style>
        @media screen {
            body {
                margin: 0;
                padding: 0;
                overflow: hidden;
                background: white !important;
                font-family: 'Courier New', monospace;
                font-size: 11px;
                line-height: 1.2;
                width: 80mm;
                color: #000;
            }
            
            body::before,
            body::after {
                display: none !important;
            }
            
            body > *:not(.ticket-container) {
                display: none !important;
                visibility: hidden !important;
            }
            
            .ticket-container {
                width: 80mm;
                padding: 5mm;
                background: white;
                border: none;
                box-shadow: none;
                font-size: 10px;
                line-height: 1.2;
                position: fixed;
                top: 0;
                left: 0;
                z-index: 99999;
                height: 100vh;
                overflow: auto;
            }
        }
        
        @media print {
            @page {
                margin: 0;
                size: 80mm auto;
            }
            
            html, body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
                margin: 0;
                padding: 0;
                background: white !important;
                font-family: 'Courier New', monospace;
                font-size: 11px;
                line-height: 1.2;
                width: 80mm;
                color: #000;
            }
            
            .ticket-container {
                width: 80mm;
                background: white;
                padding: 5mm;
                margin: 0;
                border: none;
                box-shadow: none;
                font-size: 10px;
                line-height: 1.2;
                position: static;
            }
        }
        
        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }
        
        .company-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .company-info {
            font-size: 9px;
            margin-bottom: 2px;
        }
        
        .ticket-info {
            text-align: center;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 1px dashed #000;
        }
        
        .ticket-number {
            font-weight: bold;
            font-size: 12px;
        }
        
        .items-section {
            margin-bottom: 8px;
        }
        
        .item {
            margin-bottom: 3px;
            font-size: 10px;
        }
        
        .item-name {
            font-weight: bold;
        }
        
        .item-details {
            display: flex;
            justify-content: space-between;
        }
        
        .totals-section {
            border-top: 1px dashed #000;
            padding-top: 5px;
            margin-top: 8px;
        }
        
        .total-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }
        
        .total-final {
            font-weight: bold;
            font-size: 12px;
            border-top: 1px solid #000;
            padding-top: 3px;
            margin-top: 3px;
        }
        
        .footer {
            text-align: center;
            margin-top: 10px;
            padding-top: 5px;
            border-top: 1px dashed #000;
            font-size: 9px;
        }
        
        .print-button {
            display: none; /* Ocultar botón de imprimir */
        }
    </style>
</head>
<body>

    
    <div class="ticket-container">
        <!-- Header -->
        <div class="header">
            <img src="/assets/images/logotipo.png" alt="Logo" style="width:32px;height:32px;object-fit:contain;display:block;margin:0 auto 3px;">
            <div class="company-name">Botica San Antonio</div>
            <div class="company-info">Av. Ferrocarril 118, Chilca 12003</div>
            <div class="company-info">BOTICA@SANANTONIO.COM</div>
        </div>
        
        <!-- Ticket Info -->
        <div class="ticket-info">
            <div class="ticket-number">BOLETA SIMPLE</div>
            <div class="ticket-number">N° {{ $venta->numero_venta }}</div>
            <div style="font-size: 9px; margin-top: 3px;">
                Fecha: {{ $venta->created_at->format('d/m/Y H:i') }}
            </div>
            @if($venta->cliente)
            <div style="font-size: 9px;">
                Cliente: {{ $venta->cliente->nombre }}
            </div>
            @endif
        </div>
        
        <!-- Items -->
        <div class="items-section">
            @foreach($venta->detalles as $detalle)
            <div class="item">
                <div class="item-name">{{ $detalle->producto->nombre }}</div>
                <div class="item-details">
                    <span>{{ $detalle->cantidad }} x S/. {{ number_format($detalle->precio_unitario, 2) }}</span>
                    <span>S/. {{ number_format($detalle->subtotal, 2) }}</span>
                </div>
            </div>
            @endforeach
        </div>
        
        <!-- Totals -->
        <div class="totals-section">
            <div class="total-line">
                <span>Subtotal:</span>
                <span>S/. {{ number_format($venta->subtotal, 2) }}</span>
            </div>
            @if($venta->descuento > 0)
            <div class="total-line">
                <span>Descuento @if($venta->tipo_descuento == 'porcentaje')({{ $venta->porcentaje_descuento }}%)@endif:</span>
                <span>-S/. {{ number_format($venta->descuento, 2) }}</span>
            </div>
            @endif
            <div class="total-line">
                <span>IGV (18%):</span>
                <span>S/. {{ number_format($venta->igv, 2) }}</span>
            </div>
            <div class="total-line total-final">
                <span>TOTAL:</span>
                <span>S/. {{ number_format($venta->total, 2) }}</span>
            </div>
        </div>
        
        <!-- Payment Info -->
        @if($venta->metodo_pago)
        <div style="text-align: center; margin-top: 8px; font-size: 9px;">
            Método de pago: {{ ucfirst($venta->metodo_pago) }}
            @if($venta->metodo_pago == 'efectivo' && $venta->monto_recibido > 0)
            <br>Recibido: S/. {{ number_format($venta->monto_recibido, 2) }}
            <br>Vuelto: S/. {{ number_format($venta->vuelto, 2) }}
            @elseif(in_array($venta->metodo_pago, ['tarjeta', 'yape']) && $venta->monto_recibido > 0)
            <br>Monto: S/. {{ number_format($venta->monto_recibido, 2) }}
            @endif
        </div>
        @endif
        
        <!-- Footer -->
        <div class="footer">
            <div>¡Gracias por su compra!</div>
            <div style="margin-top: 8px; font-size: 8px; border-top: 1px dashed #000; padding-top: 5px;">
                <strong>POLÍTICA DE DEVOLUCIÓN:</strong><br>
                Las devoluciones solo se aceptan el mismo día<br>
                de la compra con boleta original.<br>
                Productos en perfecto estado.
            </div>
        </div>
    </div>
    
    <script>
        // La impresión es controlada desde el POS (iframe + print). Evitamos auto-imprimir aquí
        // para prevenir dobles diálogos de impresión.
    </script>
</body>
</html>