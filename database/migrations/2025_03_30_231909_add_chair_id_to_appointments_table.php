<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChairIdToAppointmentsTable extends Migration
{
    public function up()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('chair_id')->nullable()
                ->constrained('chairs') // Define a relação com a tabela chairs
                ->onDelete('cascade'); // Caso a cadeira seja excluída, os agendamentos vinculados também serão removidos
        });
    }

    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['chair_id']);
            $table->dropColumn('chair_id');
        });
    }
}
