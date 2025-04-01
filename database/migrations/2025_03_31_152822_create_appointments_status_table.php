<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppointmentsStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointments_status', function (Blueprint $table) {
            $table->id(); // ID primário
            $table->string('name'); // Nome do status
            $table->string('description')->nullable(); // Descrição, pode ser nula
            $table->string('color', 10); // Cor, limitada a 10 caracteres
            $table->boolean('active')->default(true); // Campo ativo com valor padrão true
            $table->timestamps(); // Campos de criação e atualização
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appointments_status');
    }
}
