<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFotoPinjamTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('foto_pinjam', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('reference_id');
            $table->enum('type',['Pengembalian', 'Pinjaman']);
            $table->string('urlfoto');      
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
        Schema::dropIfExists('foto_pinjam');
    }
}
