<?php

namespace App\Http\Controllers;

use App\Appointment;
use App\AppointmentStatus;
use App\Chair;
use App\Contact;
use App\User;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{

    public function index()
    {
        $appointments = Appointment::all();
        $staff = User::where('user_type', 'staff')->get();
        $contacts = Contact::all();

        // dd($appointments);
        return view('backend.accounting.calendar.list',compact('appointments', 'staff', 'contacts'));
    }


    public function create(Request $request)
    {
        $contacts = Contact::all();
        $chairs = Chair::all();
        $appointments_status = AppointmentStatus::all();

        // dd($contacts);
        $staff = User::where('user_type', 'staff')->get();
        if (!$request->ajax()) {
            // Retornar a view completa para uso normal
            return view('backend.accounting.appointments.create',compact('contacts', 'staff', 'chairs', 'appointments_status'));
        } else {
            // Retornar uma view específica para o modal
            return view('backend.accounting.appointments.modal.create', compact('contacts', 'staff','chairs', 'appointments_status'));
        }

    }

    public function getAppointment($id)
    {
        $appointment = Appointment::with(['patient', 'staff', 'chair', 'status'])->find($id);

        if (!$appointment) {
            return response()->json(['error' => 'Agendamento não encontrado'], 404);
        }

        return response()->json([
            'id' => $appointment->id,
            'contact_id' => $appointment->contact_id,
            'staff_id' => $appointment->user_id,
            'chair_id' => $appointment->chair_id,
            'appointment_status_id' => $appointment->appointment_status,
            'date' => $appointment->date,
            'time' => $appointment->time,
            'patient_name' => $appointment->patient->contact_name ?? 'Não informado',
            'doctor_name' => $appointment->staff->name ?? 'Não informado',
            'chair_description' => $appointment->chair->description ?? 'Não informada',
            'status_id' => $appointment->status_id ?? 'Não informada',
            'status_name' => $appointment->status->name ?? 'Sem status',
        ]);
    }

    public function update(Request $request)
{
    $appointment = Appointment::find($request->id);
    
    if ($appointment) {
        $appointment->contact_id = $request->contact_id;
        $appointment->staff_id = $request->staff_id;
        $appointment->chair_id = $request->chair_id;
        $appointment->date = $request->date;
        $appointment->appointment_status = $request->appointment_status;
        $appointment->save();

        return response()->json(['success' => 'Agendamento atualizado com sucesso!']);
    }

    return response()->json(['error' => 'Agendamento não encontrado.'], 404);
}

    public function store(Request $request)
    {

        // dd($request->all());
        // Validate input
        $request->validate([
            'staff_id' => 'required|exists:users,id', // Médico (profissional)
            'contact_id' => 'required|exists:contacts,id', // Paciente
            'chair_id' => 'required|exists:chairs,id', // Cadeira
            'date' => 'required|date', // Data do agendamento
            'selected_time' => 'required', // Hora inicial
            'appointment_status' => 'required', // Status do Agendamento
        ]);
    
        // Buscar o médico e a cadeira
        $doctor = User::findOrFail($request->staff_id);
        $chairId = $request->chair_id;
    
        // Definir horário inicial e calcular horário final
        $start_time = $request->selected_time;
        $schedule = $doctor->schedules()->where('day_of_week', date('l', strtotime($request->date)))->first();
        $average_duration = $schedule ? $schedule->average_duration : 30; // Padrão de 30 minutos caso não tenha agenda configurada
        $end_time = date('H:i:s', strtotime($start_time . "+{$average_duration} minutes"));
    
        // Verificar conflitos de horário com o médico
        $doctorConflict = Appointment::where('user_id', $doctor->id)
            ->where('date', $request->date)
            ->where(function ($query) use ($start_time, $end_time) {
                $query->where('start_time', '<', $end_time)
                      ->where('end_time', '>', $start_time);
            })
            ->exists();
    
        if ($doctorConflict) {
            // Retornar erro caso o médico já tenha um agendamento
            if ($request->ajax()) {
                return response()->json(['error' => 'Conflito de horário detectado para este profissional!'], 409);
            }
            return redirect()->back()->withErrors('Conflito de horário detectado para este profissional!');
        }
    
        // Verificar disponibilidade da cadeira
        $chairConflict = Appointment::where('chair_id', $chairId)
            ->where('date', $request->date)
            ->where(function ($query) use ($start_time, $end_time) {
                $query->where('start_time', '<', $end_time)
                      ->where('end_time', '>', $start_time);
            })
            ->exists();
    
        // Avisar sobre o conflito de cadeira (não bloqueia o agendamento)
        if ($chairConflict) {
            $warningMessage = 'Aviso: A cadeira já está ocupada neste horário!';
        }
        $appointment_status = $request->appointment_status;
        // dd($appointment_status);
        // Criar o agendamento
        $appointment = Appointment::create([
            'user_id' => $doctor->id,
            'contact_id' => $request->contact_id,
            'chair_id' => $chairId,
            'date' => $request->date,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'status_id' => $appointment_status,
        ]);
    
        // Responder conforme o tipo de requisição
        if ($request->ajax()) {
            $response = [
                'success' => 'Consulta agendada com sucesso!',
                'appointment' => $appointment,
            ];
    
            if (isset($warningMessage)) {
                $response['warning'] = $warningMessage;
            }
    
            return response()->json($response, 200);
        }
    
        return redirect()->route('appointments.index')
            ->with('success', 'Consulta agendada com sucesso!')
            ->with('warning', $warningMessage ?? null);
    }



// public function filterAppointments(Request $request)
// {
//     $query = Appointment::query();

//     // Filtrar por cadeira
//     if ($request->has('chair_id')) {
//         $query->where('chair_id', $request->chair_id);
//     }

//     // Filtrar por médico (se informado)
//     if ($request->has('doctor_id')) {
//         $query->where('user_id', $request->doctor_id);
//     }

//     $appointments = $query->get()->map(function ($appointment) {
//         return [
//             'id' => $appointment->id,
//             'contact_name' => $appointment->contact->name,
//             'doctor_name' => $appointment->user->name,
//             'chair_description' => $appointment->chair->descricao,
//             'date' => $appointment->date,
//             'start_time' => $appointment->start_time,
//             'end_time' => $appointment->end_time,
//             'color' => "#FF5733", // Recuperando a cor do status
//         ];
//     });

//     return response()->json($appointments);
// }



    // Validate the inputs
    // public function store(Request $request)
    // {
    //     // Validate input
    //     $request->validate([
    //         'user_id' => 'required|exists:users,id',
    //         'contact_id' => 'required|exists:contacts,id',
    //         'date' => 'required|date',
    //         'selected_time' => 'required', // Ensure a time is selected
    //     ]);
    
    //     // Retrieve the doctor (user)
    //     $user = User::findOrFail($request->user_id);
    
    //     // Get the selected start time
    //     $start_time = $request->selected_time;
    
    //     // Retrieve the doctor's schedule for the selected day
    //     $schedule = $user->schedules()->where('day_of_week', date('l', strtotime($request->date)))->first();
    //     $average_duration = $schedule ? $schedule->average_duration : 30; // Default 30 minutes if no schedule found
    //     $end_time = date('H:i:s', strtotime($start_time . "+{$average_duration} minutes"));
    
    //     // Refined logic for checking time conflicts
    //     $conflict = Appointment::where('user_id', $user->id)
    //         ->where('date', $request->date)
    //         ->where(function ($query) use ($start_time, $end_time) {
    //             $query->where(function ($q) use ($start_time, $end_time) {
    //                 $q->where('start_time', '<', $end_time)
    //                   ->where('end_time', '>', $start_time);
    //             });
    //         })
    //         ->exists();
    
    //     if ($conflict) {
    //         // Handle conflict
    //         if ($request->ajax()) {
    //             return response()->json(['error' => 'Conflito de horário detectado!'], 409); // HTTP Conflict Response
    //         }
    //         return redirect()->back()->withErrors('Conflito de horário detectado!');
    //     }
        
    
    //     // Verificar se o agendamento já existe para o mesmo horário
    //     $exists = Appointment::where('user_id', $request->user_id)
    //         ->where('date', $request->date)
    //         ->where('start_time', $request->selected_time)
    //         ->exists();

    //     if ($exists) {
    //         if ($request->ajax()) {
    //             return response()->json(['error' => 'Já existe uma consulta agendada nesse horário!'], 409);
    //         }
    //         return redirect()->back()->withErrors('Já existe uma consulta agendada nesse horário!');
    //     }

    //     // Criar o agendamento
    //     $appointment = Appointment::create([
    //         'user_id' => $user->id,
    //         'contact_id' => $request->contact_id,
    //         'date' => $request->date,
    //         'start_time' => $start_time,
    //         'end_time' => $end_time,
    //     ]);

    
    //     // Handle response based on request type
    //     if ($request->ajax()) {
    //         return response()->json([
    //             'success' => 'Consulta agendada com sucesso!',
    //             'appointment' => $appointment,
    //         ], 200);
    //     }
    
    //     return redirect()->route('appointments.index')->with('success', 'Consulta agendada com sucesso!');
    // }
    

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'user_id' => 'required|exists:users,id',
    //         'contact_id' => 'required|exists:contacts,id',
    //         'date' => 'required|date',
    //         'selected_time' => 'required', // Validando o horário selecionado
    //     ]);
    
    //     // Encontrar o médico
    //     $user = User::findOrFail($request->user_id);
    
    //     // Pegar o horário selecionado no botão
    //     $start_time = $request->selected_time;
    
    //     // Calcular o horário de término com base na duração média
    //     $schedule = $user->schedules()->where('day_of_week', date('l', strtotime($request->date)))->first();
    //     $average_duration = $schedule ? $schedule->average_duration : 30; // Duração média padrão caso não encontrada
    //     $end_time = date('H:i:s', strtotime($start_time . "+{$average_duration} minutes"));
    
    //     // Verificar conflitos de horários
    //     $conflict = Appointment::where('user_id', $user->id)
    //         ->where('date', $request->date)
    //         ->where(function ($query) use ($start_time, $end_time) {
    //             $query->whereBetween('start_time', [$start_time, $end_time])
    //                   ->orWhereBetween('end_time', [$start_time, $end_time]);
    //         })
    //         ->exists();
    
    //     if ($conflict) {
    //         return redirect()->back()->withErrors('Conflito de horário detectado!');
    //     }
    
    //     // Criar o agendamento
    //     $appointment = Appointment::create([
    //         'user_id' => $user->id,
    //         'contact_id' => $request->contact_id,
    //         'date' => $request->date,
    //         'start_time' => $start_time,
    //         'end_time' => $end_time,
    //     ]);
    //     if ($request->ajax()) {
    //         // Retorno específico para chamadas AJAX
    //         return response()->json([
    //             'success' => 'Consulta agendada com sucesso!',
    //             'appointment' => $appointment,
    //         ], 200);
    //     }
    
    //     // Retorno padrão para chamadas não AJAX
    //     return redirect()->route('appointments.index')->with('success', 'Consulta agendada com sucesso!');
    // }
    

    // public function getAvailableTimes(User $doctor, Request $request)
    // {
    //     $date = $request->query('date');
        
    //     // Validate if a valid date is provided
    //     if (!$date || !strtotime($date)) {
    //         return response()->json(['error' => 'Invalid or missing date'], 400); // HTTP 400: Bad Request
    //     }
    
    //     // Retrieve the doctor's schedule for the specific day of the week
    //     $dayOfWeek = date('l', strtotime($date)); // Get the day of the week (e.g., Monday)
    //     $schedule = $doctor->schedules()->where('day_of_week', $dayOfWeek)->first();
    
    //     // If no schedule is found, return an empty array
    //     if (!$schedule) {
    //         return response()->json(['error' => 'No schedule available for the selected date'], 404); // HTTP 404: Not Found
    //     }
    
    //     $startTime = new \DateTime($schedule->start_time);
    //     $endTime = new \DateTime($schedule->end_time);
    //     $interval = $schedule->average_duration;
    
    //     // Retrieve the reserved appointments for the given date
    //     $reservedAppointments = $doctor->appointments()
    //         ->where('date', $date)
    //         ->pluck('start_time')
    //         ->map(function ($time) {
    //             return date('H:i', strtotime($time)); // Format times as H:i
    //         })
    //         ->toArray();
    
    //     $availableTimes = [];
    //     while ($startTime < $endTime) {
    //         $time = $startTime->format('H:i'); // Format as H:i
    //         $availableTimes[] = [
    //             'time' => $time,
    //             'available' => !in_array($time, $reservedAppointments), // Mark as available or not
    //         ];
    //         $startTime->modify("+{$interval} minutes");
    //     }
    
    //     return response()->json($availableTimes);
    // }

    // public function getAvailableTimes(User $doctor, Request $request)
    // {
    //     $date = $request->query('date');

    //     if (!$date) {
    //         return response()->json(['error' => 'Data não fornecida'], 400);
    //     }

    //     $schedule = $doctor->schedules()->where('day_of_week', date('l', strtotime($date)))->first();

    //     if (!$schedule) {
    //         return response()->json([]);
    //     }

    //     $startTime = new \DateTime($schedule->start_time);
    //     $endTime = new \DateTime($schedule->end_time);
    //     $interval = $schedule->average_duration;

    //     $reservedAppointments = $doctor->appointments()
    //         ->where('date', $date)
    //         ->pluck('start_time')
    //         ->map(function ($time) {
    //             return date('H:i', strtotime($time));
    //         })
    //         ->toArray();

    //     $availableTimes = [];
    //     while ($startTime < $endTime) {
    //         $time = $startTime->format('H:i');
    //         $availableTimes[] = [
    //             'time' => $time,
    //             'available' => !in_array($time, $reservedAppointments),
    //         ];
    //         $startTime->modify("+{$interval} minutes");
    //     }

    //     return response()->json($availableTimes);
    // }
    public function getAvailableTimes(Request $request)
    {
        $doctorId = $request->query('doctor_id');
        $chairId = $request->query('chair_id');
        $date = $request->query('date');
    
        if (!$doctorId || !$chairId || !$date) {
            return response()->json(['error' => 'Parâmetros incompletos fornecidos.'], 400);
        }
    
        $doctor = User::find($doctorId);
    
        if (!$doctor) {
            return response()->json(['error' => 'Médico não encontrado.'], 404);
        }
    
        $schedule = $doctor->schedules()->where('day_of_week', date('l', strtotime($date)))->first();
    
        if (!$schedule) {
            return response()->json([]); // Médico não atende neste dia
        }
    
        $startTime = new \DateTime($schedule->start_time);
        $endTime = new \DateTime($schedule->end_time);
        $interval = $schedule->average_duration;
    
        // Obter agendamentos do médico e da cadeira
        $doctorAppointments = Appointment::where('user_id', $doctorId)
            ->where('date', $date)
            ->get(['start_time', 'end_time']);
    
        $chairAppointments = Appointment::where('chair_id', $chairId)
            ->where('date', $date)
            ->get(['start_time', 'end_time']);
    
        $availableTimes = [];
    
        while ($startTime < $endTime) {
            $timeSlot = $startTime->format('H:i');
            $timeSlotEnd = (clone $startTime)->modify("+{$interval} minutes -1 second")->format('H:i');
    
            $isDoctorOccupied = $doctorAppointments->contains(function ($appointment) use ($startTime, $interval) {
                $appointmentStart = new \DateTime($appointment->start_time);
                $appointmentEnd = new \DateTime($appointment->end_time);
                $slotEnd = (clone $startTime)->modify("+{$interval} minutes -1 second");
            
                return $appointmentStart < $slotEnd && $appointmentEnd > $startTime;
            });
            // Verificar se a cadeira já está ocupada nesse horário
            $isChairOccupied = $chairAppointments->contains(function ($appointment) use ($startTime, $interval) {
                $appointmentStart = new \DateTime($appointment->start_time);
                $appointmentEnd = new \DateTime($appointment->end_time);
                $slotEnd = (clone $startTime)->modify("+{$interval} minutes -1 second");
            
                return $appointmentStart < $slotEnd && $appointmentEnd > $startTime;
            });
    
            $availableTimes[] = [
                'time' => $timeSlot,
                'available' => !$isDoctorOccupied, // O médico precisa estar disponível
                'chair_occupied' => $isChairOccupied, // Apenas um aviso sobre a cadeira ocupada
            ];
    
            $startTime->modify("+{$interval} minutes");
        }
    
        return response()->json($availableTimes);
    }
    
}

