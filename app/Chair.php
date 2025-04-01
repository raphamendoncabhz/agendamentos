<?php
namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chair extends Model
{
    use HasFactory;

    protected $fillable = [
        'descricao',
        'localizacao',
    ];

    // Relacionamentos futuros, como com "appointments", podem ser adicionados aqui.
}