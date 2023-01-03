<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePinjamTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pinjam', function (Blueprint $table) {
            $table->id();
            $table->string('nip');
            $table->date('tglpinjam');
            $table->date('tglpengembalian')->nullable();
            $table->enum('jenispinjam', ['KOJ', 'PPKO']);
            $table->string('es1');
            $table->string('es2');
            $table->string('es3');
            $table->string('es4');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pinjam');
    }
}
