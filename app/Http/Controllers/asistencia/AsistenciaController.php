<?php

namespace App\Http\Controllers\asistencia;

use App\tbl_cursos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AsistenciaController extends Controller {
    
    // 7X-21-ARFT-EXT-0006
    public function index(Request $request) {
        $message = NULL;
        $clave = $request->clave;
        $curso = tbl_cursos::where('clave', '=', $clave)->first();

        $dias = [];
        $alumnos = [];
        $fecha_valida = NULL;
        $fecha_hoy = date("d-m-Y");
        if($curso) {
            $inicio = $curso->inicio;
            $termino = $curso->termino;
            for($i=$inicio; $i<=$termino; $i=date("Y-m-d", strtotime($i ."+ 1 days"))){
                array_push($dias, $i);
            }

            if (Auth::user()->unidad == 1) $fecha_penultimo = date("Y-m-d", strtotime($curso->termino . "- 3 days"));
            else $fecha_penultimo = date("Y-m-d", strtotime($curso->termino . "- 1 days"));
            $fecha_valida =  strtotime($fecha_hoy) - strtotime($fecha_penultimo);

            if ($curso->turnado == "UNIDAD" and $curso->status != "REPORTADO" and $curso->status != "CANCELADO") {
                $alumnos = DB::connection('pgsql')->table('tbl_inscripcion as i')->select('i.id', 'i.matricula', 'i.alumno', 'i.calificacion', 'f.folio')
                    ->leftJoin('tbl_folios as f', function ($join) {
                        $join->on('f.id', '=', 'i.id_folio');
                    })
                    ->where('i.id_curso', $curso->id)->where('i.status', 'INSCRITO')->orderby('i.alumno')->get();

                if ($fecha_valida < 0) $message = "No prodece el registro de calificaciones, la fecha de termino del curso es el $curso->termino.";
            } else $message = "El Curso fué $curso->status y turnado a $curso->turnado.";
        }

        return view('layouts.asistencia.registrarAsistencias', compact('clave', 'curso', 'dias', 'alumnos', 'message'));
    }

    public function update(Request $request) {
        $asistencias = $request->asistencias;
        dd($asistencias);
        // $myArray = json_decode($_POST['kvcArray']);
        // dd($myArray);
    }

}
