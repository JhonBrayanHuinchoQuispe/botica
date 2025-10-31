<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boleta Electrónica - {{ $venta->numero_venta }}</title>
    <style>
        @media print {
            /* Mantener colores en impresión (Chrome/Edge/Firefox) */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            body { 
                margin: 0; 
                padding: 0;
                background: white !important;
            }
            .boleta-container {
                border: none !important;
                box-shadow: none !important;
                margin: 0 !important;
                padding: 10mm !important;
                width: 100% !important;
                max-width: none !important;
                box-sizing: border-box;
            }
            @page {
                size: A4;
                margin: 5mm;
                /* Intentar preservar colores en algunos motores */
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .no-print {
                display: none !important;
            }

            /* Forzar los colores del tema rojo en impresión */
            .header { border-bottom: 2px solid #dc2626 !important; }
            .document-section { border: 2px solid #dc2626 !important; }
            .company-name { color: #dc2626 !important; }
            .products-table th { background: #dc2626 !important; color: #ffffff !important; }
            .total-final { background: #dc2626 !important; color: #ffffff !important; }
            .footer { border-top: 2px solid #dc2626 !important; }
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            line-height: 1.3;
            margin: 0;
            padding: 0;
            background: white;
            color: black;
        }

        .boleta-container {
            width: 190mm;
            max-width: 750px;
            margin: 0 auto;
            padding: 15mm;
            background: white;
            color: black;
            min-height: 270mm;
            box-sizing: border-box;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            border-bottom: 2px solid #dc2626;
            padding-bottom: 20px;
        }

        .company-section {
            flex: 1;
        }
        
        /* Se elimina caja duplicada de boleta electrónica */
        
        .boleta-title {
            font-size: 12px;
            font-weight: bold;
            color: #dc2626;
            margin-bottom: 5px;
        }
        
        .boleta-number {
            font-size: 14px;
            font-weight: bold;
            color: #000;
        }

        .logo {
            width: 80px;
            height: 80px;
            margin-bottom: 10px;
            display: inline-block;
        }

        .logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin: 5px 0;
            color: #dc2626;
        }

        .company-info {
            font-size: 11px;
            margin: 2px 0;
            color: #333;
        }

        .document-section {
            text-align: center;
            border: 2px solid #dc2626;
            padding: 15px;
            min-width: 200px;
        }

        .document-type {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .document-number {
            font-size: 16px;
            font-weight: bold;
            color: #1f2937;
        }

        .client-info {
            margin: 20px 0;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }

        .info-row {
            display: flex;
            margin: 8px 0;
        }

        .info-label {
            font-weight: bold;
            width: 120px;
            color: #333;
        }

        .info-value {
            flex: 1;
            color: #000;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 10px;
        }

        .products-table th {
            background: #dc2626;
            color: white;
            padding: 8px 6px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #000;
            font-size: 10px;
        }

        .products-table td {
            padding: 6px 4px;
            border: 1px solid #ccc;
            vertical-align: middle;
            font-size: 10px;
        }

        .products-table tr:nth-child(even) {
            background: #f8f9fa;
        }

        .products-table .col-cant { width: 8%; }
        .products-table .col-unidad { width: 10%; }
        .products-table .col-codigo { width: 12%; }
        .products-table .col-descripcion { width: 45%; }
        .products-table .col-precio { width: 12%; }
        .products-table .col-total { width: 13%; }

        .text-center { text-align: center; }
        .text-right { text-align: right; }

        .totals-section {
            margin-top: 20px;
            float: right;
            width: 280px;
            clear: both;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px solid #ddd;
            font-size: 11px;
        }

        .total-label {
            font-weight: bold;
            color: #333;
        }

        .total-amount {
            font-weight: bold;
            color: #000;
        }

        .total-final {
            background: #dc2626;
            color: white;
            padding: 10px;
            margin-top: 8px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 4px;
        }

        .payment-info {
            clear: both;
            margin-top: 40px;
            padding: 15px;
            background: #e9ecef;
            border-radius: 5px;
        }

        .qr-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 25px 0;
            clear: both;
            page-break-inside: avoid;
        }

        .qr-placeholder {
            border: 2px solid #2E8B57;
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: bold;
            background: #f8f9fa;
            border-radius: 4px;
        }

        .qr-info {
            flex: 1;
            margin-left: 15px;
            font-size: 10px;
            line-height: 1.4;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #dc2626;
            font-size: 9px;
            color: #666;
            page-break-inside: avoid;
        }

        .footer div {
            margin: 2px 0;
        }

        .footer strong {
            color: #dc2626;
        }
    </style>
</head>
<body>
    <div class="boleta-container">
        <!-- Header -->
        <div class="header">
            <div class="company-section">
                <div class="logo">
                    <img src="{{ asset('assets/images/logotipo.png') }}" alt="Botica San Antonio Logo">
                </div>
                <div class="company-name">BOTICA SAN ANTONIO</div>
                <div class="company-info"><strong>Dirección:</strong> AV. FERROCARRIL 188, CHILCA 12003</div>
                <div class="company-info"><strong>Correo:</strong> BOTICA@SANANTONIO.COM</div>
            </div>
            
            <div class="document-section">
                <div class="document-type">BOLETA DE VENTA ELECTRÓNICA</div>
                <div class="document-number">{{ $venta->numero_venta }}</div>
            </div>
        </div>

        <!-- Client Information -->
        <div class="client-info">
            <div class="info-row">
                <span class="info-label">Cliente:</span>
                <span class="info-value">{{ $venta->cliente->nombre ?? 'CLIENTE GENERAL' }}</span>
            </div>
            @if($venta->cliente && $venta->cliente->documento)
            <div class="info-row">
                <span class="info-label">RUC:</span>
                <span class="info-value">{{ $venta->cliente->documento }}</span>
            </div>
            @endif
            <div class="info-row">
                <span class="info-label">Dirección:</span>
                <span class="info-value">HUANCAYO</span>
            </div>
            <div class="info-row">
                <span class="info-label">Fecha de emisión:</span>
                <span class="info-value">{{ $venta->fecha_venta->format('Y-m-d') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Fecha de vencimiento:</span>
                <span class="info-value">{{ $venta->fecha_venta->format('Y-m-d') }}</span>
            </div>
        </div>

        <!-- Products Table -->
        <table class="products-table">
            <thead>
                <tr>
                    <th class="col-cant">CANTIDAD</th>
                    <th class="col-codigo">CÓDIGO</th>
                    <th class="col-descripcion">DESCRIPCIÓN</th>
                    <th class="col-precio">P.UNIT</th>
                    <th class="col-total">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($venta->detalles as $detalle)
                <tr>
                    <td class="text-center col-cant">{{ $detalle->cantidad }} {{ $detalle->cantidad == 1 ? 'UNIDAD' : 'UNIDADES' }}</td>
                    <td class="text-center col-codigo">{{ $detalle->producto->codigo ?? 'A002' }}</td>
                    <td class="col-descripcion">{{ $detalle->producto->nombre }}</td>
                    <td class="text-center col-precio">S/ {{ number_format($detalle->precio_unitario, 2) }}</td>
                    <td class="text-center col-total">S/ {{ number_format($detalle->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <div class="total-row">
                <span class="total-label">OP. GRAVADAS: S/</span>
                <span class="total-amount">{{ number_format($venta->subtotal, 2) }}</span>
            </div>
            <div class="total-row">
                <span class="total-label">IGV: S/</span>
                <span class="total-amount">{{ number_format($venta->igv, 2) }}</span>
            </div>
            <div class="total-final">
                <div class="total-row">
                    <span>TOTAL A PAGAR: S/</span>
                    <span>{{ number_format($venta->total, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Payment Info -->
        <div class="payment-info">
            <strong>SON: {{ numeroALetras($venta->total) ?? 'CIENTO SESENTA Y DOS CON 25/100 SOLES' }}</strong>
        </div>

        <!-- Se elimina QR y datos bancarios -->

        <!-- Footer -->
        <div class="footer">
            <div><strong>CONDICIÓN DE PAGO: Contado</strong></div>
            <div>REPRESENTACIÓN IMPRESA DE LA BOLETA DE VENTA ELECTRÓNICA</div>
            <div>Fecha de impresión: {{ now()->format('d/m/Y H:i:s') }}</div>
        </div>
    </div>

    <script>
        // Impresión controlada desde el POS; no auto-imprimir aquí para evitar dobles.
    </script>
</body>
</html>