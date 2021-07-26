<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('auth/login');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/Registro/inicio', 'RegistroController@index')->name('registro.inicio');
Route::post('/Registro/enviar', 'RegistroController@store')->name('registro.enviar');

Route::get('/Usuarios/inicio', 'UsuariosController@index')->name('usuarios.inicio');
Route::post('/Usuarios/modificar', 'UsuariosController@update')->name('usuarios.modificar');

// agregar rol
Route::get('/Roles/inicio', 'RolesController@index')->name('roles.inicio');
Route::post('/Roles/guardar', 'RolesController@store')->name('roles.store');

// permisos
Route::get('/Permisos/inicio', 'PermisosController@index')->name('permisos.inicio');
Route::post('/Permisos/guardar', 'PermisosController@store')->name('permisos.store');

// calificaciones
Route::get('/Calificaciones/inicio', 'calificaciones\CalificacionesController@index')->name('calificaciones.inicio');
Route::post('/Calificaciones/guardar', 'calificaciones\CalificacionesController@update')->name('calificaciones.guardar');