<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Appointment;
use App\AppointmentStatus;
use App\Chair;
use App\Contact;

class CalendarController extends Controller
{
    // Exibe a página do calendário
    public function index()
    {
        // Obter todos os médicos
        $staff = User::where('user_type', 'staff')->get();
        $chairs = Chair::all();
        $appointments = Appointment::all();
        $appointments_statusses = AppointmentStatus::all();
        $patients = Contact::all();
        // dd($chairs);
        return view('backend.accounting.calendar.list', compact('staff','chairs', 'patients', 'appointments_statusses'));
    }

    // Retorna os eventos do calendário
    public function events(Request $request)
    {
        $doctorId = $request->query('doctor_id'); // Capturar o ID do médico, se fornecido
        $chairId = $request->query('chair_id'); // Capturar o ID da cadeira, se fornecido
    
        $query = Appointment::query();
    
        // Filtrar por médico, se o ID do médico foi fornecido e não for 'all'
        if ($doctorId && $doctorId !== 'all') {
            $query->where('user_id', $doctorId);
        }
    
        // Filtrar por cadeira, se o ID da cadeira foi fornecido e não for 'all'
        if ($chairId && $chairId !== 'all') {
            $query->where('chair_id', $chairId);
        }
    
        $appointments = $query->get()->map(function ($appointment) {
            return [
                'id' => $appointment->id,
                'title' => $appointment->contact->contact_name,
                'start' => $appointment->date . 'T' . $appointment->start_time,
                'end' => $appointment->date . 'T' . $appointment->end_time,
                "color" =>$appointment->status->color // Cor vinda do status do agendamento
            ];
        });
    
        return response()->json($appointments);
    }

    public function scheduleStatus(Request $request){
        $schedule  =  $request->agendamento ;
        $schedule->agendamento  = $request->data_agendamento;
        $schedule->hora_agendamento = $request->hora_agendamento;
        $schedule->status_agendamento = $request->status_agendamento;
        $schedule->doctor_id = $request->doctor_id;
        $schedule->doctor_name = $request->doctor_name;
        $schedule->timeSheet = $request->timeSheet;
        $schedule->save();
        
        
        $appointment = new Appointment();
        $appointment->date = $request->date;
        $appointment->status = $request->status;
        $appointment->color = $request->color ;
        $appointment->change =  $request->change;
        $appointment->max_required_size =  $request->max_required_size;

    }
    
    
}
