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
            $table->text('tipo_archivo');
            $table->string('uuid_sellado')->nullable();
            $table->string('fecha_sellado')->nullable();
            $table->string('numero_o_clave')->nullable();
            $table->string('nombre_archivo')->nullable();
            $table->text('md5_file')->nullable();
            $table->text('cadena_sello')->nullable();

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
