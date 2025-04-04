@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Enviar Mensagem - {{ $instance->instance_name }}</h5>
                    <a href="{{ route('whatsapp.instances.show', $instance->id) }}" class="btn btn-secondary btn-sm">Voltar</a>
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

                    @if ($instance->status != 'connected')
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


