<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ReniecService
{
    private $apis;
    
    public function __construct()
    {
        $this->apis = [
            [
                'name' => 'ApisPeru',
                'url' => "https://dniruc.apisperu.com/api/v1/dni/{dni}",
                'headers' => [
                    'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbWFpbCI6ImVqZW1wbG9AZ21haWwuY29tIn0.qUX2RjFc2wOqJ4QQHnHPtPBMG6_bvNLrLvnzRNYKiEU'
                ],
                'method' => 'GET',
                'format' => 'apisperu'
            ],
            [
                'name' => 'ApisNet',
                'url' => "https://api.apis.net.pe/v2/reniec/dni",
                'headers' => [
                    'Authorization' => 'Bearer apis-token-8046.5EUbWPaKKvAaI8LBHF4QUKgfwXNs8Ej8'
                ],
                'method' => 'POST',
                'data_key' => 'dni',
                'format' => 'apisnet'
            ],
            [
                'name' => 'PeruDevs',
                'url' => "https://api.perudevs.com/api/v1/dni/complete",
                'headers' => [
                    'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbWFpbCI6InRlc3RAZ21haWwuY29tIn0.DUJOZhLn_H5xJJ5YE2yKlOKsOZkWHqCzCHxNzLNpSUE'
                ],
                'method' => 'POST',
                'data_key' => 'document',
                'format' => 'perudevs'
            ]
        ];
    }
    
    /**
     * Consultar DNI en múltiples APIs de RENIEC
     */
    public function consultarDni($dni)
    {
        if (strlen($dni) !== 8 || !is_numeric($dni)) {
            throw new Exception('DNI debe tener 8 dígitos numéricos');
        }
        
        Log::info("🔍 Consultando DNI: {$dni} en APIs de RENIEC");
        
        foreach ($this->apis as $api) {
            try {
                Log::info("🌐 Intentando API: {$api['name']} - {$api['url']}");
                
                $response = $this->hacerConsulta($api, $dni);
                
                if ($response && $response->successful()) {
                    $data = $response->json();
                    Log::info("📄 Respuesta de {$api['name']}: " . json_encode($data));
                    
                    $datosPersona = $this->procesarRespuesta($api['format'], $data);
                    
                    if ($datosPersona) {
                        Log::info("✅ DNI encontrado en {$api['name']}: " . $datosPersona['nombre_completo']);
                        return $datosPersona;
                    }
                }
                
            } catch (Exception $e) {
                Log::warning("⚠️ Error en API {$api['name']}: " . $e->getMessage());
                continue; // Intentar con la siguiente API
            }
        }
        
        throw new Exception('No se pudo encontrar información del DNI en ninguna API disponible');
    }
    
    /**
     * Realizar consulta HTTP a la API
     */
    private function hacerConsulta($api, $dni)
    {
        $url = str_replace('{dni}', $dni, $api['url']);
        
        if ($api['method'] === 'POST') {
            $data = [$api['data_key'] => $dni];
            
            return Http::timeout(15)
                ->withHeaders($api['headers'])
                ->post($url, $data);
        } else {
            return Http::timeout(15)
                ->withHeaders($api['headers'])
                ->get($url);
        }
    }
    
    /**
     * Procesar respuesta según el formato de cada API
     */
    private function procesarRespuesta($format, $data)
    {
        switch ($format) {
            case 'apisperu':
                return $this->procesarApisPeru($data);
                
            case 'apisnet':
                return $this->procesarApisNet($data);
                
            case 'perudevs':
                return $this->procesarPeruDevs($data);
                
            default:
                return null;
        }
    }
    
    /**
     * Procesar respuesta de ApisPeru
     */
    private function procesarApisPeru($data)
    {
        if (isset($data['nombres']) && isset($data['apellidoPaterno'])) {
            return [
                'nombres' => trim($data['nombres']),
                'apellido_paterno' => trim($data['apellidoPaterno']),
                'apellido_materno' => trim($data['apellidoMaterno'] ?? ''),
                'nombre_completo' => trim($data['nombres'] . ' ' . $data['apellidoPaterno'] . ' ' . ($data['apellidoMaterno'] ?? '')),
                'fuente' => 'ApisPeru'
            ];
        }
        
        return null;
    }
    
    /**
     * Procesar respuesta de ApisNet
     */
    private function procesarApisNet($data)
    {
        if (isset($data['success']) && $data['success'] && isset($data['data'])) {
            $persona = $data['data'];
            
            return [
                'nombres' => trim($persona['nombres'] ?? ''),
                'apellido_paterno' => trim($persona['apellido_paterno'] ?? ''),
                'apellido_materno' => trim($persona['apellido_materno'] ?? ''),
                'nombre_completo' => trim(($persona['nombres'] ?? '') . ' ' . ($persona['apellido_paterno'] ?? '') . ' ' . ($persona['apellido_materno'] ?? '')),
                'fuente' => 'ApisNet'
            ];
        }
        
        return null;
    }
    
    /**
     * Procesar respuesta de PeruDevs
     */
    private function procesarPeruDevs($data)
    {
        if (isset($data['data']) && isset($data['data']['name'])) {
            $nombreCompleto = trim($data['data']['name']);
            $partesNombre = explode(' ', $nombreCompleto);
            
            if (count($partesNombre) >= 3) {
                return [
                    'nombres' => $partesNombre[0],
                    'apellido_paterno' => $partesNombre[1],
                    'apellido_materno' => implode(' ', array_slice($partesNombre, 2)),
                    'nombre_completo' => $nombreCompleto,
                    'fuente' => 'PeruDevs'
                ];
            }
        }
        
        return null;
    }
    
    /**
     * Validar si los datos son válidos
     */
    public function validarDatos($datos)
    {
        return isset($datos['nombres']) && 
               isset($datos['apellido_paterno']) && 
               !empty(trim($datos['nombres'])) && 
               !empty(trim($datos['apellido_paterno']));
    }
} 