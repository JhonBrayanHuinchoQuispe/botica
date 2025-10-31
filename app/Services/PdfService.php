<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Boleta;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

class PdfService
{
    private $dompdf;
    private $pdfTemplateService;

    public function __construct(PdfTemplateService $pdfTemplateService)
    {
        $this->pdfTemplateService = $pdfTemplateService;
        $this->initializeDompdf();
    }

    /**
     * Inicializar Dompdf
     */
    private function initializeDompdf()
    {
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isFontSubsettingEnabled', true);

        $this->dompdf = new Dompdf($options);
    }

    /**
     * Generar PDF de factura
     */
    public function generateInvoicePdf(Invoice $invoice, $format = 'a4')
    {
        try {
            $template = $this->pdfTemplateService->getTemplatePath('invoice', $format);
            
            if (!View::exists($template)) {
                throw new Exception("Template no encontrado: {$template}");
            }

            $data = $this->prepareInvoiceData($invoice);
            $html = View::make($template, $data)->render();

            return $this->generatePdf($html, $format, "factura_{$invoice->numero_completo}");

        } catch (Exception $e) {
            Log::error('Error generando PDF de factura: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generar PDF de boleta
     */
    public function generateBoletaPdf(Boleta $boleta, $format = 'a4')
    {
        try {
            $template = $this->pdfTemplateService->getTemplatePath('boleta', $format);
            
            if (!View::exists($template)) {
                throw new Exception("Template no encontrado: {$template}");
            }

            $data = $this->prepareBoletaData($boleta);
            $html = View::make($template, $data)->render();

            return $this->generatePdf($html, $format, "boleta_{$boleta->numero_completo}");

        } catch (Exception $e) {
            Log::error('Error generando PDF de boleta: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generar PDF genérico
     */
    private function generatePdf($html, $format, $filename)
    {
        try {
            $this->dompdf->loadHtml($html);
            
            // Configurar tamaño de papel
            $paperSize = $this->getPaperSize($format);
            $this->dompdf->setPaper($paperSize['size'], $paperSize['orientation']);
            
            $this->dompdf->render();
            
            $pdfContent = $this->dompdf->output();
            
            // Guardar PDF
            $path = "pdfs/{$filename}.pdf";
            Storage::disk('public')->put($path, $pdfContent);
            
            return [
                'success' => true,
                'path' => $path,
                'url' => Storage::disk('public')->url($path),
                'content' => $pdfContent
            ];

        } catch (Exception $e) {
            Log::error('Error generando PDF: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Preparar datos para factura
     */
    private function prepareInvoiceData(Invoice $invoice)
    {
        $invoice->load(['company', 'branch', 'client', 'details', 'legends']);

        return [
            'invoice' => $invoice,
            'company' => $invoice->company,
            'branch' => $invoice->branch,
            'client' => $invoice->client,
            'details' => $invoice->details,
            'legends' => $invoice->legends,
            'qr' => $invoice->qr,
            'hash' => $invoice->hash,
            'fecha_generacion' => now()->format('d/m/Y H:i:s')
        ];
    }

    /**
     * Preparar datos para boleta
     */
    private function prepareBoletaData(Boleta $boleta)
    {
        $boleta->load(['company', 'branch', 'client', 'details', 'legends']);

        return [
            'boleta' => $boleta,
            'company' => $boleta->company,
            'branch' => $boleta->branch,
            'client' => $boleta->client,
            'details' => $boleta->details,
            'legends' => $boleta->legends,
            'qr' => $boleta->qr,
            'hash' => $boleta->hash,
            'fecha_generacion' => now()->format('d/m/Y H:i:s')
        ];
    }

    /**
     * Obtener configuración de papel según formato
     */
    private function getPaperSize($format)
    {
        $sizes = [
            'a4' => ['size' => 'A4', 'orientation' => 'portrait'],
            'a5' => ['size' => 'A5', 'orientation' => 'portrait'],
            '80mm' => ['size' => [0, 0, 226.77, 841.89], 'orientation' => 'portrait'], // 80mm width
            '50mm' => ['size' => [0, 0, 141.73, 841.89], 'orientation' => 'portrait'], // 50mm width
            'ticket' => ['size' => [0, 0, 226.77, 841.89], 'orientation' => 'portrait'], // Default ticket size
        ];

        return $sizes[$format] ?? $sizes['a4'];
    }

    /**
     * Generar PDF desde HTML directo
     */
    public function generateFromHtml($html, $format = 'a4', $filename = null)
    {
        try {
            $filename = $filename ?: 'documento_' . time();
            return $this->generatePdf($html, $format, $filename);

        } catch (Exception $e) {
            Log::error('Error generando PDF desde HTML: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtener formatos disponibles
     */
    public function getAvailableFormats()
    {
        return [
            'a4' => 'A4 (210 x 297 mm)',
            'a5' => 'A5 (148 x 210 mm)',
            '80mm' => '80mm Ticket',
            '50mm' => '50mm Ticket',
            'ticket' => 'Ticket (80mm)'
        ];
    }

    /**
     * Validar formato
     */
    public function isValidFormat($format)
    {
        return array_key_exists($format, $this->getAvailableFormats());
    }
}