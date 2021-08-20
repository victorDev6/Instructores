<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentosFirmarTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('pgsql')->create('documentos_firmar', function (Blueprint $table) {
            $table->id();

            // $table->xml('documento');
            $table->jsonb('obj_documento');
            $table->jsonb('obj_documento_interno');
            $table->string('status');
            $table->string('link_pdf');
            $table->text('cadena_original');

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
        Schema::connection('pgsql')->dropIfExists('documentos_firmar');
    }
}
