@extends('layouts.app')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Instâncias WhatsApp</h5>
                    <a href="{{ route('whatsapp.instances.create') }}" class="btn btn-primary">Nova Instância</a>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Status</th>
                                    <th>Criado em</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($instances as $instance)
                                    <tr>
                                        <td>{{ $instance->instance_name }}</td>
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
                                        <td>{{ $instance->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('whatsapp.instances.show', $instance->id) }}" class="btn btn-sm btn-info">Detalhes</a>
                                                
                                                @if ($instance->status != 'connected')
                                                    <form action="{{ route('whatsapp.instances.connect', $instance->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success">Conectar</button>
                                                    </form>
                                                @else
                                                    <a href="{{ route('whatsapp.instances.show-send-message', $instance->id) }}" class="btn btn-sm btn-primary">Enviar Mensagem</a>
                                                    
                                                    <form action="{{ route('whatsapp.instances.disconnect', $instance->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-warning">Desconectar</button>
                                                    </form>
                                                @endif

                                                <form action="{{ route('whatsapp.instances.destroy', $instance->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir esta instância?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Nenhuma instância encontrada.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection


