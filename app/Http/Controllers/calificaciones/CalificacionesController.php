<?php

namespace App\Http\Controllers\calificaciones;

use App\tbl_cursos;
use App\instructores;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CalificacionesController extends Controller {

    function __construct() {
        session_start();
    }

    public function index(Request $request) {
        $message = NULL;
        if(session('message')) $message = session('message');

        $clave = $request->clave;
        if($clave == null) {
            if(session('clave')) $clave = session('clave');
        }
        $curso = tbl_cursos::where('clave', '=', $clave)->first();

        $fecha_hoy = date("d-m-Y");
        $fecha_valida = NULL;
        $alumnos = [];
        if ($curso) {
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

            if(count($alumnos)==0 AND !$message) $message = "El curso no tiene alumnos registrados. ";
            else $_SESSION['id_curso'] = $curso->id;
        }// else $message = "Clave inválida.";

        return view('layouts.calificaciones.agregarCalificaciones', compact('clave', 'curso', 'alumnos', 'message', 'fecha_valida'));
    }

    public function update(Request $request) {
        $id_curso = $_SESSION['id_curso'];
        $clave = $request->clave;
        if($request->calificacion ){
            foreach($request->calificacion as $key=>$val){
                if(!is_numeric($val) OR $val<6 )  $val = "NP";
                $result = DB::connection('pgsql')
                    ->table('tbl_inscripcion')
                    ->where('id_curso',$id_curso)
                    ->where('id', $key)
                    ->update(['calificacion' => $val,'iduser_updated'=>Auth::user()->id]);
            }
            if($result) $message = "Operacion exitosa!!";        
        }else $message = "No existen cambios que guardar.";

        // return redirect()->route('calificaciones.inicio')->with('success', 'ACTIVIDAD GUARDADA EXITOSAMENTE');
        return redirect('/Calificaciones/inicio')->with(['message'=>$message, 'clave'=>$clave]);
    }
}
