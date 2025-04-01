@extends('layouts.app')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.13.4/jquery.mask.min.js"></script>

<style>
	.btn-disabled {
    background-color: #e97e7e;
    color: #810707;
    cursor: not-allowed;
}
</style>
@section('content')
<div class="row">
	<div class="col-lg-6">
		<div class="card">
			<span class="panel-title d-none">{{ _lang('Agendar Consulta') }}</span>

			<div class="card-body">
				<form method="post" class="validate" autocomplete="off" action="{{ route('appointments.store') }}" enctype="multipart/form-data">
					{{ csrf_field() }}
					<div class="row">
						<div class="col-md-12">
						  <div class="form-group">
							<label class="control-label">Paciente</label>
							<select class="form-control" name="contact_id" required>
								<option value="" disabled selected>Selecione o paciente</option>
								@foreach($contacts as $contact)
									<option value="{{ $contact->id }}">{{ $contact->contact_name }}</option>
								@endforeach
							</select>
						  </div>
						</div>
				
						<div class="col-md-12">
						  <div class="form-group">
							<label class="control-label">Médico</label>
							<select class="form-control" name="user_id" id="doctor-select" required>
								<option value="" disabled selected>Selecione o médico</option>
								@foreach($staff as $user)
									<option value="{{ $user->id }}">{{ $user->name }}</option>
								@endforeach
							</select>
						  </div>
						</div>
				
						<div class="col-md-12">
						  <div class="form-group">
							<label class="control-label">Data</label>
							<input type="date" class="form-control" name="date" id="appointment-date" required>
						  </div>
						</div>
				
						<div class="col-md-12">
						  <div class="form-group">
							<label class="control-label">Horários Disponíveis</label>
							<div id="available-times" class="d-flex flex-wrap">
								<!-- Botões de horários serão preenchidos dinamicamente -->
							</div>
						  </div>
						</div>
						
						<div class="col-md-12">
						  <div class="form-group">
							<button type="reset" class="btn btn-danger">Resetar</button>
							<button type="submit" class="btn btn-primary">Agendar</button>
						  </div>
						</div>
					</div>
					<script>
						$(document).ready(function () {
						 // Seu código JavaScript aqui
						 console.log("O documento está pronto!");
					 
						 // Exemplo: evento de mudança
						 $('#doctor-select, #appointment-date').change(function () {
        const doctorId = $('#doctor-select').val();
        const date = $('#appointment-date').val();

        if (doctorId && date) {
            // Fazer requisição Ajax para buscar os horários disponíveis
            $.ajax({
                url: `/api/doctors/${doctorId}/available-times`,
                method: 'GET',
                data: { date: date },
                success: function (response) {
                    $('#available-times').empty(); // Limpar horários existentes

                    if (response.length > 0) {
                        response.forEach(function (time) {
                            // Criar um botão para cada horário
                            const button = $('<button>')
                                .attr('type', 'button')
                                .addClass('btn m-1') // Classes padrão
                                .text(time.time);   // Texto do botão é o horário

                            // Adicionar classe danger e disabled se não estiver disponível
                            if (!time.available) {
                                button
                                    .addClass('btn-danger') // Botão com estilo de perigo
                                    .prop('disabled', true); // Desabilitar o botão
                            } else {
                                button.addClass('btn-outline-primary'); // Botão padrão para disponíveis
                            }

                            // Adicionar o botão ao contêiner
                            $('#available-times').append(button);
                        });
                    } else {
                        $('#available-times').append('<p>Nenhum horário disponível</p>');
                    }
                },
                error: function () {
                    console.error('Erro ao buscar horários disponíveis.');
                }
            });
        }
    });
					 
						 $('#appointment-date').change(function () {
							 console.log('Data selecionada:', $(this).val());
						 });

						// Evento de clique nos botões de horários
						$('#available-times').on('click', '.btn-outline-primary', function () {
								// Remove a classe "active" de todos os botões
								$('.btn-outline-primary').removeClass('active');
								
								// Adiciona a classe "active" ao botão clicado
								$(this).addClass('active');
								
								// Verifica se o campo oculto "selected_time" já existe
								let hiddenInput = $('input[name="selected_time"]');
								
								if (hiddenInput.length === 0) {
									// Cria o campo oculto se não existir
									hiddenInput = $('<input>')
										.attr('type', 'hidden')
										.attr('name', 'selected_time')
										.val($(this).text()); // Define o valor como o horário do botão clicado
									$('#available-times').append(hiddenInput);
								} else {
									// Atualiza o valor do campo oculto
									hiddenInput.val($(this).text());
								}
							});


					 });
					 
					 </script>
				</form>
				
				
			</div>
		</div>
	</div>
</div>

 
@endsection

