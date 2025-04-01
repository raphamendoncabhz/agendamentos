<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class TesteController extends Controller
{
    //
    public function index(){
        echo 'Teste Controller';

        $date = '2025-03-31';
        if (!$date) {
            return response()->json([]);
        }
        $doctor = User::find('3');
        // Obter o horário do calendário do médico
        $schedule = $doctor->schedules()->where('day_of_week', date('l', strtotime($date)))->first();
    
        if (!$schedule) {
            return response()->json([]);
        }
    
        $startTime = new \DateTime($schedule->start_time);
        $endTime = new \DateTime($schedule->end_time);
        $interval = $schedule->average_duration;
    
        // Horários reservados
        $reservedAppointments = $doctor->appointments()
            ->where('date', $date)
            ->pluck('start_time')
            ->toArray();
        // dd($reservedAppointments);
        $availableTimes = [];
        while ($startTime < $endTime) {
            $time = $startTime->format('H:i');
            $availableTimes[] = [
                'time' => $time,
                'available' => !in_array($time, $reservedAppointments), // Define como disponível ou não
            ];
            $startTime->modify("+{$interval} minutes");
        }
        dd($availableTimes);
    }
}
