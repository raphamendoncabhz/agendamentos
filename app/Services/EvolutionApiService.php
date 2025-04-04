<?php

namespace App\Services;

use App\WhatsappInstance;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EvolutionApiService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.evolution_api.url');
        $this->apiKey = config('services.evolution_api.key');
    }

    /**
     * Cria uma nova instância no Evolution API
     */
    public function createInstance(string $instanceName, string $instanceKey): array
    {

        try {
            $payload = array_merge([
                'instanceName' => $instanceName,
                'token' => $instanceKey,
                'qrcode' => true,
                'integration' => 'WHATSAPP-BAILEYS',
                'webhook_by_events' => true,
                'events' => ['APPLICATION_STARTUP'],
                'reject_call' => true,
                'groups_ignore' => true,
                'always_online' => true,
                'read_messages' => true,
                'read_status' => true,
                'websocket_enabled' => true,
                'websocket_events' => ['APPLICATION_STARTUP'],
                'rabbitmq_enabled' => true,
                'rabbitmq_events' => ['APPLICATION_STARTUP'],
                'sqs_enabled' => true,
                'sqs_events' => ['APPLICATION_STARTUP']
            ]);

            $response = Http::withHeaders([
                'apikey' => $this->apiKey,
                'Content-Type' => 'application/json'
            ])->post("{$this->baseUrl}/instance/create", $payload);

            return [
                'success' => $response->successful(),
                'data' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao criar instância: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Conecta uma instância existente
     */
    public function connectInstance(string $instanceName): array
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->apiKey,
            ])->get("{$this->baseUrl}/instance/connect/{$instanceName}");

            return [
                'success' => $response->successful(),
                'data' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao conectar instância: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Obtém QR Code para conexão
     */
    public function getQrCode(string $instanceName): array
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->apiKey,
            ])->get("{$this->baseUrl}/instance/qrcode/{$instanceName}");

            return [
                'success' => $response->successful(),
                'data' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao obter QR Code: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Desconecta uma instância
     */
    public function disconnectInstance(string $instanceName): array
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->apiKey,
            ])->delete("{$this->baseUrl}/instance/logout/{$instanceName}");

            return [
                'success' => $response->successful(),
                'data' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao desconectar instância: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Exclui uma instância
     */
    public function deleteInstance(string $instanceName): array
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->apiKey,
            ])->delete("{$this->baseUrl}/instance/delete/{$instanceName}");

            return [
                'success' => $response->successful(),
                'data' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao excluir instância: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Obtém status de uma instância
     */
    public function getInstanceStatus(string $instanceName): array
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->apiKey,
            ])->get("{$this->baseUrl}/instance/connectionState/{$instanceName}");

            return [
                'success' => $response->successful(),
                'data' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao obter status: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Envia mensagem de texto
     */
    public function sendTextMessage(string $instanceName, string $number, string $message): array
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->apiKey,
                'Content-Type' => 'application/json'
            ])->post("{$this->baseUrl}/message/sendText/{$instanceName}", [
                'number' => $number,
                'text' => $message,
                'options' => [
                    'delay' => 1200
                ]
            ]);

            return [
                'success' => $response->successful(),
                'data' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao enviar mensagem: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}