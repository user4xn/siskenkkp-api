<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePegawaiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pegawai', function (Blueprint $table) {
            $table->bigInteger('nip');
            $table->string('nama');
            $table->string('jk');
            $table->string('alamat');
            $table->bigInteger('idbiro');
            $table->bigInteger('idjabatan');
            $table->timestamp('createddate')->useCurrent();
            $table->timestamp('updateddate')->useCurrent();
            $table->primary('nip');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pegawai');
    }
}
