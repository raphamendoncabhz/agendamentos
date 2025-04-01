<?php

namespace App\Http\Controllers;

use App\Schedule;
use App\User;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{

    public function index()
    {
        $schedules = Schedule::all();
        dd($schedules);
        return view('schedules.create');
    }

    public function create()
    {
        return view('schedules.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'staff_id' => 'required',
            'days' => 'required|array',
            'start_times' => 'required|array',
            'end_times' => 'required|array',
            'average_durations' => 'required|array',
        ]);
    
        foreach ($request->days as $index => $day) {
            // Validações por dia
            $startTime = $request->start_times[$index];
            $endTime = $request->end_times[$index];
            $averageTime = $request->average_durations[$index];
    
            if ($startTime && $endTime) {
                Schedule::updateOrCreate(
                    [
                        'user_id' => $request->staff_id, // Exemplo: substitua pelo médico logado ou selecionado
                        'day_of_week' => $day,
                    ],
                    [
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'average_duration' => $averageTime,
                    ]
                );
            }
        }

        return redirect()->back()->with('success', 'Horário cadastrado com sucesso!');
    }

    public function show(User $user)
    {
        return $user->schedules;
    }
}