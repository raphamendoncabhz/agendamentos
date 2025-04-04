@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Nova Instância WhatsApp</h5>
                </div>

                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('whatsapp.instances.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="instance_name" class="form-label">Nome da Instância</label>
                            <input type="text" class="form-control @error('instance_name') is-invalid @enderror" 
                                id="instance_name" name="instance_name" value="{{ old('instance_name') }}" required>
                            <small class="form-text text-muted">Use apenas letras, números e traços. Este nome será usado para identificar a instância no Evolution API.</small>
                            
                            @error('instance_name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('whatsapp.instances.index') }}" class="btn btn-secondary">Voltar</a>
                            <button type="submit" class="btn btn-primary">Criar Instância</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


