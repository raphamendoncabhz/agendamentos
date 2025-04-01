@extends('layouts.app')

@section('content')
<!-- Calendar CSS -->
<link href="{{ asset('public/backend/plugins/fullcalendar/packages/core/main.css') }}" rel="stylesheet" />
<link href="{{ asset('public/backend/plugins/fullcalendar/packages/daygrid/main.css') }}" rel="stylesheet" />
<link href="{{ asset('public/backend/plugins/fullcalendar/packages/bootstrap/main.css') }}" rel="stylesheet" />
<link href="{{ asset('public/backend/plugins/fullcalendar/packages/timegrid/main.css') }}" rel="stylesheet" />
<link href="{{ asset('public/backend/plugins/fullcalendar/packages/list/main.css') }}" rel="stylesheet" />
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <!-- Dropdowns para seleção de médico e cadeira -->
                <label for="doctor-select-fielter">Selecione o Médico:</label>
                <select id="doctor-select-fielter" class="form-control">
                    <option value="all" selected>Todos os Médicos</option>
                    @foreach($staff as $doctor)
                        <option value="{{ $doctor->id }}">{{ $doctor->name }}</option>
                    @endforeach
                </select>
                
                <label for="chair-select-fielter" class="mt-2">Selecione a Cadeira:</label>
                <select id="chair-select-filter" class="form-control">
                    <option value="all" selected>Todas as Cadeiras</option>
                    @foreach($chairs as $chair)
                        <option value="{{ $chair->id }}">{{ $chair->description }}</option>
                    @endforeach
                </select>
                
                <!-- Calendário -->
                <div class="container mt-4">
                    <div id="appointments_calendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal de agendamento -->
<div class="modal fade" id="appointmentModal" tabindex="-1" aria-labelledby="appointmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="appointmentModalLabel">Novo Agendamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="appointment-form">
                    <div class="form-group">
                        <label for="patient-select">Selecione o Paciente:</label>
                        <select id="patient-select" class="form-control" name="contact_id" required>
                            <option value="" disabled selected>Selecione um paciente</option>
                            @foreach($patients as $patient)
                                <option value="{{ $patient->id }}">{{ $patient->contact_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mt-3">
                        <label for="modal-doctor-select">Selecione o Profissional:</label>
                        <select id="modal-doctor-select" class="form-control" name="staff_id" required>
                            <option value="" disabled selected>Selecione um profissional</option>
                            @foreach($staff as $doctor)
                                <option value="{{ $doctor->id }}">{{ $doctor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mt-3">
                        <label for="modal-chair-select">Selecione a Cadeira:</label>
                        <select id="modal-chair-select" class="form-control" name="chair_id" required>
                            <option value="" disabled selected>Selecione uma cadeira</option>
                            @foreach($chairs as $chair)
                                <option value="{{ $chair->id }}">{{ $chair->description }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mt-3">
                        <label for="modal-date">Data:</label>
                        <input type="date" id="modal-date" class="form-control" name="date" required readonly>
                    </div>
      
                    <div class="form-group mt-3">
                        <label class="control-label">Status do Agendamento</label>
                        <select class="form-control" name="appointment_status" id="create-appointment-status-select-modal" required>
                            <option value="" disabled selected>Selecione o Status</option>
                            @foreach($appointments_statusses as $appointment_status)
                                <option value="{{ $appointment_status->id }}">{{ $appointment_status->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mt-3">
                        <label for="available-times">Horários Disponíveis:</label>
                        <div id="available-times" class="d-flex flex-wrap">
                            <!-- Horários disponíveis serão carregados via AJAX -->
                        </div>
                    </div>
                    <!-- Campo oculto para armazenar o horário selecionado -->
                    <input type="hidden" id="selected-time" name="selected_time">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="submit" class="btn btn-primary" id="save-appointment">Salvar</button>
            </div>
        </div>
    </div>
</div>


<!-- Modal de Edição -->
<div class="modal fade" id="editAppointmentModal" tabindex="-1" aria-labelledby="editAppointmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAppointmentModalLabel">Editar Agendamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="edit-appointment-form">
                    <input type="hidden" id="edit-appointment-id" name="id">

                    <div class="form-group">
                        <label for="edit-patient-select">Paciente:</label>
                        <select id="edit-patient-select" class="form-control" name="contact_id" required>
                            @foreach($patients as $patient)
                                <option value="{{ $patient->id }}">{{ $patient->contact_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mt-3">
                        <label for="edit-modal-doctor-select">Profissional:</label>
                        <select id="edit-modal-doctor-select" class="form-control" name="staff_id" required>
                            @foreach($staff as $doctor)
                                <option value="{{ $doctor->id }}">{{ $doctor->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mt-3">
                        <label for="edit-modal-chair-select">Cadeira:</label>
                        <select id="edit-modal-chair-select" class="form-control" name="chair_id" required>
                            @foreach($chairs as $chair)
                                <option value="{{ $chair->id }}">{{ $chair->description }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mt-3">
                        <label for="edit-modal-date">Data:</label>
                        <input type="date" id="edit-modal-date" class="form-control" name="date" required>
                    </div>

                    <div class="form-group mt-3">
                        <label for="edit-appointment-status-select-modal">Status:</label>
                        <select id="edit-appointment-status-select-modal" class="form-control" name="appointment_status" required>
                            @foreach($appointments_statusses as $appointment_status)
                                <option value="{{ $appointment_status->id }}">{{ $appointment_status->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="submit" class="btn btn-primary" id="update-appointment">Salvar Alterações</button>
            </div>
        </div>
    </div>
</div>



@endsection

@section('js-script')
<script src="{{ asset('public/backend/plugins/fullcalendar/packages/core/main.js') }}"></script>
<script src="{{ asset('public/backend/plugins/fullcalendar/packages/daygrid/main.js') }}"></script>
<script src="{{ asset('public/backend/plugins/fullcalendar/packages/timegrid/main.js') }}"></script>
<script src="{{ asset('public/backend/plugins/fullcalendar/packages/interaction/main.js') }}"></script>
<script src="{{ asset('public/backend/plugins/fullcalendar/packages/list/main.js') }}"></script>

<script>
$(document).ready(function () {
    var calendarEl = document.getElementById('appointments_calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        plugins: ['interaction', 'dayGrid', 'timeGrid', 'list'],
        locale: 'pt-br',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay',
        },
        events: function(info, successCallback, failureCallback) {
            // Pegar os valores dos filtros
            const doctorId = $('#doctor-select-fielter').val();
            const chairId = $('#chair-select-filter').val();

            $.ajax({
                url: '/calendar/events',
                method: 'GET',
                data: {
                    doctor_id: doctorId === 'all' ? null : doctorId,  // Se o valor for 'all', não aplica filtro de médico
                    chair_id: chairId === 'all' ? null : chairId,  // Se o valor for 'all', não aplica filtro de cadeira
                },
                success: function(response) {
                    successCallback(response); // Passa os dados filtrados para o calendário
                },
                error: function() {
                    failureCallback(); // Caso haja erro, falha na requisição
                }
            });
        },
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        },
        eventClick: function(info) {
            var eventId = info.event.id;

            $.ajax({
                url: 'api/calendar/event/' + eventId,
                method: 'GET',
                success: function(response) {
                    console.log("Evento carregado:", response);

                    $('#edit-appointment-id').val(response.id); 
                    $('#edit-patient-select').val(response.contact_id);
                    $('#edit-modal-doctor-select').val(response.staff_id);
                    $('#edit-modal-chair-select').val(response.chair_id);
                    $('#edit-modal-date').val(response.date);
                    $('#edit-appointment-status-select-modal').val(response.status_id);

                    $('#editAppointmentModal').modal('show');
                },
                error: function() {
                    alert('Erro ao carregar os dados do agendamento.');
                }
            });
        },

        dateClick: function (info) {
            // Abrir o modal e preencher a data
            $('#modal-date').val(info.dateStr); // Data clicada no calendário
            $('#appointmentModal').modal('show');
        }
    });

    // Inicializar o calendário
    calendar.render();

    // Evento de mudança do filtro de médico
    $('#doctor-select-fielter').change(function () {
        calendar.refetchEvents(); // Recarregar eventos com base no filtro
    });

    // Evento de mudança do filtro de cadeira
    $('#chair-select-filter').change(function () {
        calendar.refetchEvents(); // Recarregar eventos com base no filtro
    });

    // Buscar horários disponíveis ao selecionar profissional, cadeira e data
    $('#modal-doctor-select, #modal-chair-select').change(function () {
        const doctorId = $('#modal-doctor-select').val();
        const chairId = $('#modal-chair-select').val();
        const date = $('#modal-date').val();

        if (doctorId && chairId && date) {
            $.ajax({
                url: '/api/availability',
                method: 'GET',
                data: {
                    doctor_id: doctorId,
                    chair_id: chairId,
                    date: date
                },
                success: function (response) {
                    $('#available-times').empty(); // Limpar horários anteriores

                    if (response.length > 0) {
                        response.forEach(function (time) {
                            const button = $('<button>')
                                .attr('type', 'button')
                                .addClass('btn m-1')
                                .text(time.time);

                            if (!time.available) {
                                button.addClass('btn-danger').prop('disabled', true); // Médico ocupado
                            } else if (time.chair_occupied) {
                                button.addClass('btn-warning'); // Cadeira ocupada
                            } else {
                                button.addClass('btn-outline-primary').on('click', function () {
                                    // Remover "active" dos outros botões e adicionar no clicado
                                    $('.btn-outline-primary').removeClass('active');
                                    $(this).addClass('active');

                                    // Atualizar o campo oculto com o horário selecionado
                                    $('#selected-time').val(time.time);
                                });
                            }

                            $('#available-times').append(button);
                        });
                    } else {
                        $('#available-times').append('<p>Nenhum horário disponível.</p>');
                    }
                },
                error: function () {
                    console.error('Erro ao buscar horários disponíveis.');
                }
            });
        }
    });

    // Salvar agendamento ao clicar em salvar
    $('#save-appointment').click(function () {
        const data = $('#appointment-form').serialize(); // Captura os dados do formulário

        $.ajax({
            url: '/api/appointments',
            method: 'POST',
            data: data,
            success: function (response) {
                alert('Agendamento realizado com sucesso!');
                $('#appointmentModal').modal('hide');
                calendar.refetchEvents(); // Recarregar eventos no calendário
            },
            error: function () {
                alert('Erro ao salvar o agendamento!');
            }
        });
    });

    $('#update-appointment').click(function () {
    var data = $('#edit-appointment-form').serialize();

    $.ajax({
        url: '/api/appointments/update', // Ajuste conforme necessário
        method: 'POST',
        data: data,
        success: function (response) {
            alert('Agendamento atualizado com sucesso!');
            $('#editAppointmentModal').modal('hide');
            calendar.refetchEvents(); // Atualizar o calendário
        },
        error: function () {
            alert('Erro ao atualizar o agendamento.');
        }
    });
});
});



</script>
@endsection



{{-- @extends('layouts.app')

@section('content')
<!-- Calendar CSS -->
<link href="{{ asset('public/backend/plugins/fullcalendar/packages/core/main.css') }}" rel="stylesheet" />
<link href="{{ asset('public/backend/plugins/fullcalendar/packages/daygrid/main.css') }}" rel="stylesheet" />
<link href="{{ asset('public/backend/plugins/fullcalendar/packages/bootstrap/main.css') }}" rel="stylesheet" />
<link href="{{ asset('public/backend/plugins/fullcalendar/packages/timegrid/main.css') }}" rel="stylesheet" />
<link href="{{ asset('public/backend/plugins/fullcalendar/packages/list/main.css') }}" rel="stylesheet" />
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <!-- Dropdown para seleção do médico -->
                <label for="doctor-select">Selecione o Médico:</label>
                <select id="doctor-select" class="form-control">
                    <option value="all" selected>Todos os Médicos</option>
                    @foreach($staff as $doctor)
                        <option value="{{ $doctor->id }}">{{ $doctor->name }}</option>
                    @endforeach
                </select>

                <!-- Calendário com novo ID -->
                <div class="container mt-4">
                    <!-- Calendário -->
                    <div id="appointments_calendar"></div>
                </div>
                
                <!-- Modal para Agendar Consulta -->
                <div class="modal fade" id="appointmentModal" tabindex="-1" role="dialog" aria-labelledby="appointmentModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="appointmentModalLabel">Agendar Consulta</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form id="appointmentForm" action="{{ route('appointments.store') }}">
                                <div class="modal-body">
                                    @csrf
                                    <div class="form-group">
                                        <label for="contact-select">Paciente</label>
                                        <select id="contact-select" name="contact_id" class="form-control" required>
                                            <option value="" disabled selected>Selecione o paciente</option>
                                            @foreach($contacts as $contact)
                                                <option value="{{ $contact->id }}">{{ $contact->contact_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="doctor-select-modal">Médico</label>
                                        <select id="doctor-select-modal" name="user_id" class="form-control" required>
                                            <option value="" disabled selected>Selecione o médico</option>
                                            @foreach($staff as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="appointment-date">Data</label>
                                        <input type="date" id="appointment-date" name="date" class="form-control" required readonly>
                                    </div>
                                    <div class="form-group">
                                        <label>Horários Disponíveis</label>
                                        <div id="available-times" class="d-flex flex-wrap">
                                            <!-- Botões de horários disponíveis irão aparecer aqui dinamicamente -->
                                        </div>
                                    </div>
                                    <input type="hidden" name="selected_time" id="selected_time">
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-primary">Salvar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

               <!-- Modal for Editing Appointment -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Editar Consulta</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editAppointmentForm" action="" method="POST">
                <div class="modal-body">
                    @csrf
                    @method('PUT') <!-- Ensure Laravel knows this is an update -->
                    <div class="form-group">
                        <label for="edit-contact-select">Paciente</label>
                        <select id="edit-contact-select" name="contact_id" class="form-control" required>
                            <option value="" disabled selected>Selecione o paciente</option>
                            @foreach($contacts as $contact)
                                <option value="{{ $contact->id }}">{{ $contact->contact_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit-doctor-select-modal">Médico</label>
                        <select id="edit-doctor-select-modal" name="user_id" class="form-control" required>
                            <option value="" disabled selected>Selecione o médico</option>
                            @foreach($staff as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit-appointment-date">Data</label>
                        <input type="date" id="edit-appointment-date" name="date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Horários Disponíveis</label>
                        <div id="edit-available-times" class="d-flex flex-wrap">
                            <!-- Dynamic time slots will be populated here -->
                        </div>
                    </div>
                    <input type="hidden" name="selected_time" id="edit-selected_time">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>


            </div>
        </div>
    </div>
</div>

@endsection

@section('js-script')
<!-- FullCalendar JS -->
<script src="{{ asset('public/backend/plugins/fullcalendar/packages/core/main.js') }}"></script>
<script src="{{ asset('public/backend/plugins/fullcalendar/packages/daygrid/main.js') }}"></script>
<script src="{{ asset('public/backend/plugins/fullcalendar/packages/timegrid/main.js') }}"></script>
<script src="{{ asset('public/backend/plugins/fullcalendar/packages/interaction/main.js') }}"></script>
<script src="{{ asset('public/backend/plugins/fullcalendar/packages/list/main.js') }}"></script>


<script>
$(document).ready(function () {
    var isSubmitting = false; // Variável para rastrear submissões

    $('#appointmentForm').on('submit', function (event) {
        event.preventDefault(); // Evitar comportamento padrão do formulário

        if (isSubmitting) {
            // Caso já esteja sendo submetido, ignorar nova tentativa
            return;
        }

        isSubmitting = true; // Marcar como em submissão

        const formData = $(this).serialize();
        const submitButton = $(this).find('button[type="submit"]');

        // Desabilitar o botão para evitar cliques repetidos
        submitButton.prop('disabled', true).text('Salvando...');

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            success: function (response) {
                showNotification('success', response.success); // Mostrar mensagem de sucesso
                $('#appointmentModal').modal('hide'); // Fechar modal
                calendar.refetchEvents(); // Atualizar o calendário
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.error || 'Erro ao salvar a consulta.');
            },
            complete: function () {
                // Reativar o botão após a conclusão
                isSubmitting = false; // Resetar status de submissão
                submitButton.prop('disabled', false).text('Salvar');
            },
        });
    }); 

     // Função para exibir notificações
     function showNotification(type, message) {
        const notification = $('<div>')
            .addClass(`alert alert-${type} alert-dismissible fade show`)
            .attr('role', 'alert')
            .text(message)
            .append(
                $('<button>')
                    .attr('type', 'button')
                    .addClass('btn-close')
                    .attr('data-bs-dismiss', 'alert')
                    .attr('aria-label', 'Close')
            );

        // Adicionar a notificação no início da página
        $('.card-body').prepend(notification);

        // Remover após 5 segundos
        setTimeout(() => {
            notification.alert('close');
        }, 5000);
    }

    // Inicialização do FullCalendar
    var calendarEl = document.getElementById('appointments_calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        plugins: ['interaction', 'dayGrid', 'timeGrid', 'list'],
        locale: 'pt-br',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay',
        },
        dateClick: function (info) {
            // Preencher a data no modal e abrir
            $('#appointment-date').val(info.dateStr);
            $('#appointmentModal').modal('show');
        },
        events: '/calendar/events', // URL for loading events from the backend
        eventClick: function (info) {
    const event = info.event; // Get the clicked event details

    // Open the edit modal
    $('#editModal').modal('show');

    // Populate fields with event details
    $('#edit-contact-select').val(event.extendedProps.contact_id);
    $('#edit-doctor-select').val(event.extendedProps.user_id);
    $('#edit-appointment-date').val(event.startStr.split("T")[0]); // Extract date
    $('#edit-selected_time').val(event.startStr.split("T")[1]); // Extract time

    // Set the hidden input field for the event ID
    $('#edit-event-id').val(event.id);

    // Update the form's action URL dynamically to include the appointment ID
    const updateUrl = `/appointments/${event.id}`;
    $('#editAppointmentForm').attr('action', updateUrl);
}
    });
    // Handle Edit Form Submission
    $('#editAppointmentForm').on('submit', function (event) {
        event.preventDefault();

        if (isSubmitting) return;

        isSubmitting = true;

        const formData = $(this).serialize();
        const submitButton = $(this).find('button[type="submit"]');

        submitButton.prop('disabled', true).text('Salvando...');

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            success: function (response) {
                showNotification('success', response.success);
                $('#editModal').modal('hide');
                calendar.refetchEvents(); // Reload calendar events
            },
            error: function (xhr) {
                showNotification('error', xhr.responseJSON?.error || 'Erro ao salvar a consulta.');
            },
            complete: function () {
                isSubmitting = false;
                submitButton.prop('disabled', false).text('Salvar');
            },
        });
    });


    calendar.render();

    // Evento de mudança no filtro do médico
    $('#doctor-select').change(function () {
        var doctorId = $(this).val(); // Obter o ID do médico selecionado

        // Definir a URL de busca com base na seleção
        var url = doctorId === 'all' ? '/calendar/events' : `/calendar/events?doctor_id=${doctorId}`;

        // Atualizar os eventos no calendário
        $.ajax({
            url: url,
            method: 'GET',
            success: function (response) {
                calendar.removeAllEvents(); // Remover todos os eventos do calendário
                calendar.addEventSource(response); // Adicionar os novos eventos
            },
            error: function () {
                console.error('Erro ao carregar eventos.');
            }
        });
    });

    // Atualizar horários disponíveis ao selecionar um médico
    $('#doctor-select-modal').change(function () {
        const doctorId = $(this).val();
        const date = $('#appointment-date').val();

        if (doctorId && date) {
            $.ajax({
                url: `/api/doctors/${doctorId}/available-times`,
                method: 'GET',
                data: { date: date },
                success: function (response) {
                    $('#available-times').empty(); // Limpar horários anteriores
                    if (response.length > 0) {
                        response.forEach(function (time) {
                            const button = $('<button>')
                                .attr('type', 'button')
                                .addClass('btn m-1 btn-outline-primary')
                                .text(time.time)
                                .on('click', function () {
                                    // Atualizar o horário selecionado
                                    $('#selected_time').val(time.time);
                                    $('.btn-outline-primary').removeClass('active');
                                    $(this).addClass('active');
                                });

                            if (!time.available) {
                                button.addClass('btn-danger').prop('disabled', true);
                            }

                            $('#available-times').append(button);
                        });
                    } else {
                        $('#available-times').html('<p>Nenhum horário disponível</p>');
                    }
                },
                error: function () {
                    console.error('Erro ao buscar horários disponíveis.');
                },
            });
        }
    });

    // Limpar o conteúdo do modal ao fechar
    $('#appointmentModal').on('hidden.bs.modal', function () {
        $('#appointmentForm')[0].reset(); // Resetar o formulário
        $('#available-times').empty(); // Limpar os horários disponíveis
        $('#selected_time').val(''); // Resetar o horário selecionado
    });

});
</script>
@endsection --}}

{{-- 
@extends('layouts.app')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.13.4/jquery.mask.min.js"></script>

@section('content')
<div class="container mt-4">
    <div class="row mb-3">
        <div class="col-md-4">
            <label for="chair-filter">Filtrar por Cadeira:</label>
            <select id="chair-filter" class="form-control">
                <option value="" disabled selected>Selecione uma cadeira</option>
                @foreach($chairs as $chair)
                    <option value="{{ $chair->id }}">{{ $chair->description }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label for="doctor-filter">Filtrar por Médico (opcional):</label>
            <select id="doctor-filter" class="form-control">
                <option value="" selected>Todos os médicos</option>
                @foreach($staffs as $doctor)
                    <option value="{{ $doctor->id }}">{{ $doctor->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Paciente</th>
                        <th>Médico</th>
                        <th>Cadeira</th>
                        <th>Data</th>
                        <th>Horário</th>
                    </tr>
                </thead>
                <tbody id="appointment-list">
                    <!-- Agendamentos serão preenchidos dinamicamente -->
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        $('#chair-filter').change(function () {
            const chairId = $(this).val();
            const doctorId = $('#doctor-filter').val();

            console.log(`Cadeira selecionada: ${chairId}, Médico selecionado: ${doctorId}`);

            loadAppointments({ chair_id: chairId, doctor_id: doctorId });
        });

        $('#doctor-filter').change(function () {
            const chairId = $('#chair-filter').val();
            const doctorId = $(this).val();

            console.log(`Cadeira selecionada: ${chairId}, Médico selecionado: ${doctorId}`);

            if (chairId) {
                loadAppointments({ chair_id: chairId, doctor_id: doctorId });
            }
        });
        // Função para carregar agendamentos com base nos filtros
        function loadAppointments(filters) {
            $.ajax({
                url: '/api/appointments',
                method: 'GET',
                data: filters,
                success: function (response) {
                    console.log('Resposta do backend:', response); // Inspecionar a resposta recebida
                    $('#appointment-list').empty();

                    if (response.length > 0) {
                        response.forEach(function (appointment) {
                            $('#appointment-list').append(`
                                <tr>
                                    <td>${appointment.id}</td>
                                    <td>${appointment.contact_name}</td>
                                    <td>${appointment.doctor_name}</td>
                                    <td>${appointment.chair_description}</td>
                                    <td>${appointment.date}</td>
                                    <td>${appointment.start_time} - ${appointment.end_time}</td>
                                </tr>
                            `);
                        });
                    } else {
                        $('#appointment-list').html('<tr><td colspan="6" class="text-center">Nenhum agendamento encontrado.</td></tr>');
                    }
                },
                error: function (xhr) {
                    console.error('Erro na requisição:', xhr);
                },
            });
        }
    });

</script>
@endsection --}}