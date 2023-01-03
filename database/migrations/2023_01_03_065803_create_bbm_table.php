<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBbmTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bbm', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('iddetailpinjam');
            $table->integer('kmsebelum');
            $table->integer('kmsesudah');
            $table->integer('kmspt');
            $table->integer('sisakm');
            $table->integer('jmlliter');
            $table->integer('harga');
            $table->integer('total');
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
        Schema::dropIfExists('bbm');
    }
}
