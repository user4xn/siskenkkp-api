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
            $table->bigInteger('es1');
            $table->bigInteger('es2');
            $table->bigInteger('es3');
            $table->bigInteger('es4');
            $table->enum('status',['Diajukan', 'Disetujui','Ditolak']);
            $table->string('nippenyetuju')->nullable();
            $table->string('catatan')->nullable();
            $table->string('nippenanggungjawab');
            $table->string('nippemakai');
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
