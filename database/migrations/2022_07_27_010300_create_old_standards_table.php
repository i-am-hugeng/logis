<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOldStandardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('old_standards', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_sk_revisi')->unsigned();
            $table->string('nmr_sni_lama');
            $table->longText('jdl_sni_lama');
            $table->timestamps();
        });

        //Set Foreign Key di kolom id_sni_lama pada tabel meeting_materials
        Schema::table('meeting_materials', function(Blueprint $table){
            $table->foreign('id_sni_lama')->references('id')->on('old_standards')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Drop Foreign Key di kolom id_sni_lama di tabel meeting_materials
        Schema::table('meeting_materials', function(Blueprint $table){
            $table->dropForeign('meeting_materials_id_sni_lama_foreign');
        });

        Schema::dropIfExists('old_standards');
    }
}
