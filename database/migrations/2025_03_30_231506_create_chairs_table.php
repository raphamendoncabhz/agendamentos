<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChairsTable extends Migration
{
    public function up()
    {
        Schema::create('chairs', function (Blueprint $table) {
            $table->id();
            $table->string('description'); // Nome ou descrição da cadeira
            $table->string('localization')->nullable(); // Onde está localizada
            $table->timestamps(); // Inclui created_at e updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('chairs');
    }
}
