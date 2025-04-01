<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentStatus extends Model
{
    use HasFactory;

    // Tabela associada
    protected $table = 'appointments_status';

    // Campos que podem ser atribuídos em massa
    protected $fillable = [
        'name',
        'description',
        'color',
        'active',
    ];
}
