@extends('layouts.app')

@section('content')
{{-- resources/views/whatsapp/instances/show.blade.php --}}
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Instância: {{ $instance->instance_name }}</h5>
                    <a href="{{ route('whatsapp.instances.index') }}" class="btn btn-secondary btn-sm">Voltar</a>
                </div>

                <div class="card-body">
                    {{-- @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif --}}

                    <div class="row">
                        <div class="col-md-6">
                            <h5>Informações da Instância</h5>
                            <table class="table table-sm">
                                <tbody>
                                    <tr>
                                        <th>Nome:</th>
                                        <td>{{ $instance->instance_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td>
                                            @if ($instance->status == 'connected')
                                                <span class="badge bg-success">Conectado</span>
                                            @elseif ($instance->status == 'connecting')
                                                <span class="badge bg-warning">Conectando</span>
                                            @elseif ($instance->status == 'disconnected')
                                                <span class="badge bg-secondary">Desconectado</span>
                                            @else
                                                <span class="badge bg-info">{{ $instance->status }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Criado em:</th>
                                        <td>{{ $instance->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Atualizado em:</th>
                                        <td>{{ $instance->updated_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="mt-3">
                                <div class="d-flex gap-2">
                                    @if ($instance->status != 'connected')
                                        <form action="{{ route('whatsapp.instances.connect', $instance->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-success">Conectar</button>
                                        </form>
                                    @else
                                        <a href="{{ route('whatsapp.instances.show-send-message', $instance->id) }}" class="btn btn-primary">Enviar Mensagem</a>
                                        
                                        <form action="{{ route('whatsapp.instances.disconnect', $instance->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-warning">Desconectar</button>
                                        </form>
                                    @endif

                                    <form action="{{ route('whatsapp.instances.destroy', $instance->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta instância?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Excluir</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            @if ($instance->status == 'connecting' && $instance->qrcode)
                                <h5>QR Code</h5>
                                <p>Escaneie o QR Code com seu WhatsApp para conectar:</p>
                                <div class="text-center p-3 bg-light">
                                    @if (!empty($instance->qrcode))
									<img src="{{$instance->qrcode}}"" alt="QR Code">

									@else
										<p>Nenhum QR Code disponível</p>
									@endif
                                </div>
                                <div class="text-center mt-2">
                                    <form action="{{ route('whatsapp.instances.connect', $instance->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary">Atualizar QR Code</button>
                                    </form>
                                </div>
                            @elseif ($instance->status == 'connected' && $instance->connection_data)
                                <h5>Informações da Conexão</h5>
                                <div class="border p-3 rounded bg-light">
                                    @if (isset($instance->connection_data['instance']))
                                        <p><strong>Nome:</strong> {{ $instance->connection_data['instance']['instanceName'] ?? 'N/A' }}</p>
                                    @endif
                                    
                                    @if (isset($instance->connection_data['phone']))
                                        <p><strong>Número:</strong> {{ $instance->connection_data['phone']['number'] ?? 'N/A' }}</p>
                                        <p><strong>Nome:</strong> {{ $instance->connection_data['phone']['name'] ?? 'N/A' }}</p>
                                    @endif
                                </div>
                            @else
                                <div class="alert alert-info">
                                    Clique em "Conectar" para gerar um QR Code e conectar esta instância ao WhatsApp.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Enviar Mensagem - {{ $instance->instance_name }}</h5>
                    <a href="{{ route('whatsapp.instances.show', $instance->id) }}" class="btn btn-secondary btn-sm">Voltar</a>
                </div>

                <div class="card-body">
                    @if ($instance->status != 'open')
                        <div class="alert alert-warning">
                            Esta instância não está conectada. Por favor, conecte-a antes de enviar mensagens.
                        </div>
                    @else
                        <form method="POST" action="{{ route('whatsapp.instances.send-message', $instance->id) }}">
                            @csrf

                            <div class="mb-3">
                                <label for="number" class="form-label">Número</label>
                                <input type="text" class="form-control @error('number') is-invalid @enderror" 
                                    id="number" name="number" value="{{ old('number') }}" required>
                                <small class="form-text text-muted">Formato: DDD + número. Ex: 5511999887766</small>
                                
                                @error('number')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="message" class="form-label">Mensagem</label>
                                <textarea class="form-control @error('message') is-invalid @enderror" 
                                    id="message" name="message" rows="4" required>{{ old('message') }}</textarea>
                                
                                @error('message')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('whatsapp.instances.show', $instance->id) }}" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-primary">Enviar</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection


