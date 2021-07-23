<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RolesController extends Controller {
    
    public function index() {
        return view('layouts.agregarRole');
    }

    public function store(Request $request) {
        Role::create(['name' => $request->rol]);

        return redirect()->route('roles.inicio')->with('success', 'ROL AGREGADO EXITOSAMENTE');
    }

}
