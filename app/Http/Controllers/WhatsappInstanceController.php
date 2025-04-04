<?php

namespace App\Http\Controllers;

use App\WhatsappInstance;
use App\Services\EvolutionApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WhatsappInstanceController extends Controller
{
    protected $evolutionApiService;

    public function __construct(EvolutionApiService $evolutionApiService)
    {
        $this->evolutionApiService = $evolutionApiService;
    }

    /**
     * Exibe a lista de instâncias
     */
    public function index()
    {
        $instances = WhatsappInstance::all();
        return view('backend.accounting.whatsapp_instances.list', compact('instances'));
    }

    /**
     * Mostra formulário para criar instância
     */
    public function create()
    {
        return view('backend.accounting.whatsapp_instances.create');
    }

    /**
     * Armazena uma nova instância
     */
    public function store(Request $request)
    {
        $request->validate([
            'instance_name' => 'required|unique:whatsapp_instances,instance_name|alpha_dash',
        ]);

        $instanceName = $request->instance_name;
        $instanceKey = Str::random(20);

        // Criar instância na API
        $result = $this->evolutionApiService->createInstance($instanceName, $instanceKey);
// dd($result);
        if (!$result['success']) {
            return redirect()->back()->with('error', 'Erro ao criar instância: ' . ($result['message'] ?? 'Erro desconhecido'));
        }

        // Salvar no banco de dados
        $instance = WhatsappInstance::create([
            'instance_name' => $instanceName,
            'instance_key' => $instanceKey,
            'status' => 'created',
        ]);

        return redirect()->route('whatsapp.instances.show', $instance->id)
            ->with('success', 'Instância criada com sucesso!');
    }

    /**
     * Exibe detalhes de uma instância
     */
    public function show(WhatsappInstance $instance)
    {
        // Atualizar status da instância
        $statusResult = $this->evolutionApiService->getInstanceStatus($instance->instance_name);
        if ($statusResult['success']) {
            $instance->status = $statusResult['data']['instance']['state'] ?? $instance->status;
            $instance->connection_data = $statusResult['data'];
            $instance->save();
        }
        // dd($instance);

// echo '<img src="'.$instance->qrcode.'" alt="QR Code">';
// dd('asdfds');

        return view('backend.accounting.whatsapp_instances.view', compact('instance'));
    }

    /**
     * Conecta uma instância e exibe QR code
     */
    public function connect(WhatsappInstance $instance)
    {
        $result = $this->evolutionApiService->connectInstance($instance->instance_name);
        if (!$result['success']) {
            return redirect()->back()->with('error', 'Erro ao conectar instância: ' . ($result['message'] ?? 'Erro desconhecido'));
        }

        // Obter QR Code
        // $qrResult = $this->evolutionApiService->getQrCode($instance->instance_name);
  

        $qrcodeBase64 = $result['data']['base64'] ?? '';
// echo $qrcodeBase64 .' <br>';

// echo '<img src="'.$qrcodeBase64.'" alt="QR Code">';
// dd();

// dd();
        if ($result['success'] && isset($result['data']['base64'])) {
            $instance->qrcode = $result['data']['base64']; // Armazena a imagem do QR Code
            $instance->status = 'connecting';
            $instance->save();
        }

        return redirect()->route('whatsapp.instances.show', $instance->id)
            ->with('success', 'QR Code gerado com sucesso!');
    }

    /**
     * Desconecta uma instância
     */
    public function disconnect(WhatsappInstance $instance)
    {
        $result = $this->evolutionApiService->disconnectInstance($instance->instance_name);

        if (!$result['success']) {
            return redirect()->back()->with('error', 'Erro ao desconectar instância: ' . ($result['message'] ?? 'Erro desconhecido'));
        }

        $instance->status = 'disconnected';
        $instance->qrcode = null;
        $instance->save();

        return redirect()->route('whatsapp.instances.index')
            ->with('success', 'Instância desconectada com sucesso!');
    }

    /**
     * Exclui uma instância
     */
    public function destroy(WhatsappInstance $instance)
    {
        $result = $this->evolutionApiService->deleteInstance($instance->instance_name);
        
        // Mesmo se a API falhar, remover do banco
        $instance->delete();

        return redirect()->route('whatsapp.instances.index')
            ->with('success', 'Instância excluída com sucesso!');
    }

    /**
     * Mostra formulário para enviar mensagem
     */
    public function showSendMessage(WhatsappInstance $instance)
    {
        return view('whatsapp.instances.send-message', compact('instance'));
    }

    /**
     * Envia mensagem de texto
     */
    public function sendMessage(Request $request, WhatsappInstance $instance)
    {
        $request->validate([
            'number' => 'required|string',
            'message' => 'required|string',
        ]);

        $result = $this->evolutionApiService->sendTextMessage(
            $instance->instance_name,
            $request->number,
            $request->message
        );
        // dd($result);

        if (!$result['success']) {
            return redirect()->back()->with('error', 'Erro ao enviar mensagem: ' . ($result['message'] ?? 'Erro desconhecido'));
        }

        return redirect()->route('whatsapp.instances.show', $instance->id)
            ->with('success', 'Mensagem enviada com sucesso!');
    }
}