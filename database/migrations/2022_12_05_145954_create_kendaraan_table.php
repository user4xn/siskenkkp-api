<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKendaraanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kendaraan', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('idType');
            $table->bigInteger('idjeniskdrn');
            $table->string('nobpkb');
            $table->string('nomesin');
            $table->string('norangka');
            $table->string('nopolisi');
            $table->integer('thnkdrn');
            $table->date('tglpajak');
            $table->date('tglmatipajak');
            $table->bigInteger('jaraktempuh');
            $table->bigInteger('idmerk');
            $table->string('warna');
            $table->string('kondisi');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kendaraan');
    }
}
