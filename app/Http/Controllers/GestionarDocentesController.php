<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Campus;
use App\Models\Facultad;

class GestionarDocentesController extends Controller
{
     public function index()
    {
        // Cambiamos la consulta para usar la relación con roles
        $docentes = User::whereHas('role', function($query) {
            $query->where('nombre_role', 'docente');
        })->get();
        
        // Cargamos los campus y facultades para los selectores
        $campus = Campus::all();
        $facultades = Facultad::all();
        
        // Devuelve la vista con los datos de los docentes, campus y facultades
        return view ('Administrador.GestionarDocentes.index', compact('docentes', 'campus', 'facultades'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
       
       
    }

  
    public function store(Request $request)
    {
         $request->validate([
            'numero_cuenta' => ['required', 'string', 'max:13', 'unique:users,numero_cuenta'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'id_facultad' => ['required', 'exists:facultad,id'],
            'id_campus' => ['required', 'exists:campus,id'],
        ]);

        // Buscar el ID del rol docente
        $role = Role::where('nombre_role', 'docente')->first();
        
        if (!$role) {
            return redirect()->back()->with('error', 'El rol de docente no existe en el sistema.');
        }

        User::create([
            'numero_cuenta' => $request->numero_cuenta,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'id_role' => $role->id,
            'id_facultad' => $request->id_facultad,
            'id_campus' => $request->id_campus,
        ]);

        return redirect()->route('GestionarDocentes.index')->with('success', 'Docente creado exitosamente.');
    }



     public function show(Request $request)
    {
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $gestionarDocente = User::findOrFail($id);
        $gestionarDocente->delete();
        return redirect()->route('GestionarDocentes.index')->with('success', 'Docente eliminado correctamente');
    }


}
