<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FacturacionElectronicaService
{
    private $environment;
    
    public function __construct()
    {
        $this->environment = config('sunat.ambiente', 'beta');
    }
    
    /**
     * Generar boleta electrónica (versión simplificada para beta)
     */
    public function generarBoleta($venta)
    {
        try {
            Log::info("📄 Generando boleta electrónica para venta ID: {$venta->id}");
            
            if ($this->environment === 'beta') {
                return $this->generarBoletaBeta($venta);
            } else {
                return $this->generarBoletaProduccion($venta);
            }
            
        } catch (Exception $e) {
            Log::error("❌ Error generando boleta electrónica: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Generar boleta en modo BETA (simulación)
     */
    private function generarBoletaBeta($venta)
    {
        Log::info("🧪 Generando boleta en modo BETA (simulación)");
        
        // Generar XML simulado
        $xml = $this->generarXmlSimulado($venta);
        
        // Guardar archivos simulados
        $filename = $this->generarNombreArchivo($venta);
        
        // Guardar XML
        Storage::disk('local')->put("sunat/xml/{$filename}.xml", $xml);
        
        // Simular CDR
        $cdr = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<cdr><code>0</code><description>La Factura numero {$venta->numero_sunat}, ha sido aceptada</description></cdr>";
        Storage::disk('local')->put("sunat/cdr/R-{$filename}.xml", $cdr);
        
        // Actualizar venta
        $venta->update([
            'xml_path' => "sunat/xml/{$filename}.xml",
            'cdr_path' => "sunat/cdr/R-{$filename}.xml",
            'estado_sunat' => 'ACEPTADO',
            'fecha_envio_sunat' => now(),
            'fecha_aceptacion_sunat' => now(),
            'observaciones_sunat' => 'Boleta generada en modo BETA (simulación)'
        ]);
        
        Log::info("✅ Boleta BETA generada exitosamente para venta ID: {$venta->id}");
        
        return [
            'success' => true,
            'numero_boleta' => $venta->numero_sunat,
            'xml' => 'Boleta generada en modo BETA',
            'pdf_url' => route('punto-venta.vista-previa', $venta->id),
            'modo' => 'BETA'
        ];
    }
    
    /**
     * Generar boleta en modo PRODUCCIÓN (real)
     */
    private function generarBoletaProduccion($venta)
    {
        // Aquí iría la implementación real con Greenter
        // cuando tengas el certificado y credenciales reales
        throw new Exception('Modo producción requiere certificado digital y credenciales reales');
    }
    
    /**
     * Generar XML simulado para modo beta
     */
    private function generarXmlSimulado($venta)
    {
        $empresa = config('sunat.empresa');
        $fecha = $venta->fecha_venta->format('Y-m-d');
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2">
    <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
    <cbc:CustomizationID>2.0</cbc:CustomizationID>
    <cbc:ID>' . $venta->numero_sunat . '</cbc:ID>
    <cbc:IssueDate>' . $fecha . '</cbc:IssueDate>
    <cbc:InvoiceTypeCode listID="0101">03</cbc:InvoiceTypeCode>
    <cbc:DocumentCurrencyCode>PEN</cbc:DocumentCurrencyCode>
    
    <cac:AccountingSupplierParty>
        <cac:Party>
            <cac:PartyIdentification>
                <cbc:ID schemeID="6">' . $empresa['ruc'] . '</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyName>
                <cbc:Name>' . $empresa['nombre_comercial'] . '</cbc:Name>
            </cac:PartyName>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName>' . $empresa['razon_social'] . '</cbc:RegistrationName>
            </cac:PartyLegalEntity>
        </cac:Party>
    </cac:AccountingSupplierParty>';
    
        if ($venta->cliente) {
            $xml .= '
    <cac:AccountingCustomerParty>
        <cac:Party>
            <cac:PartyIdentification>
                <cbc:ID schemeID="1">' . $venta->cliente->dni . '</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName>' . $venta->cliente->nombre_completo . '</cbc:RegistrationName>
            </cac:PartyLegalEntity>
        </cac:Party>
    </cac:AccountingCustomerParty>';
        }
        
        $xml .= '
    <cac:LegalMonetaryTotal>
        <cbc:LineExtensionAmount currencyID="PEN">' . number_format($venta->monto_gravado, 2, '.', '') . '</cbc:LineExtensionAmount>
        <cbc:TaxInclusiveAmount currencyID="PEN">' . number_format($venta->total, 2, '.', '') . '</cbc:TaxInclusiveAmount>
        <cbc:PayableAmount currencyID="PEN">' . number_format($venta->total, 2, '.', '') . '</cbc:PayableAmount>
    </cac:LegalMonetaryTotal>
</Invoice>';
        
        return $xml;
    }
    
    /**
     * Generar nombre de archivo
     */
    private function generarNombreArchivo($venta)
    {
        $ruc = config('sunat.empresa.ruc');
        return $ruc . '-03-' . $venta->numero_sunat;
    }
    
    /**
     * Consultar estado en SUNAT (simulado para beta)
     */
    public function consultarEstado($ruc, $tipoDoc, $serie, $correlativo)
    {
        if ($this->environment === 'beta') {
            return [
                'success' => true,
                'estado' => 'ACEPTADO (BETA)',
                'codigo' => '0'
            ];
        }
        
        // Aquí iría la consulta real para producción
        return ['success' => false, 'error' => 'Consulta real requiere configuración de producción'];
    }
} 