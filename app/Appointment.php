<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'contact_id',
        'chair_id', // Adicionado
        'date',
        'start_time',
        'end_time',
        'status_id',
    ];

    // Relacionamento com o modelo Chair
    public function chair()
    {
        return $this->belongsTo(Chair::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
    public function status()
    {
        return $this->belongsTo(AppointmentStatus::class, 'status_id'); // Relacionamento com a tabela appointments_status
    }
    // Relacionamento com Paciente (Assumindo que o paciente está na tabela contacts)
    public function patient()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    // Relacionamento com Médico (Profissional de saúde)
    public function staff()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
