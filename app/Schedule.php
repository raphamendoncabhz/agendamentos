<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'day_of_week', 'start_time', 'end_time', 'average_duration'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}