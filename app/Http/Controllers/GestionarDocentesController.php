<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Campus;
use App\Models\Facultad;
use App\Mail\CredencialesMail;

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
            'telefono' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6'],
            'id_facultad' => ['required', 'exists:facultad,id'],
            'id_campus' => ['required', 'exists:campus,id'],
        ]);

        // Buscar el ID del rol docente
        $role = Role::where('nombre_role', 'docente')->first();
        
        if (!$role) {
            return redirect()->back()->with('error', 'El rol de docente no existe en el sistema.');
        }

         //Guardar Contrasena en texto plano para enviar por correo
        $passwordPlano = $request->password;

        $user = User::create([
            'numero_cuenta' => $request->numero_cuenta,
            'name' => $request->name,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'pais_telefono' => $request->pais_telefono,
            'password' => Hash::make($request->password),
            'id_role' => $role->id,
            'id_facultad' => $request->id_facultad,
            'id_campus' => $request->id_campus,
        ]);
        
        //Enviar correo
        //Enviar correo de forma asíncrona
        Mail::to($user->email)->queue(new CredencialesMail($user->name, $user->numero_cuenta, $passwordPlano, $user->email));

        return redirect()->route('GestionarDocentes.index')->with('success', 'Docente creado exitosamente.');
    }

public function edit($id)
    {
        $editando = User::findOrFail($id);
        $docentes = User::whereHas('role', function ($query) {
            $query->where('nombre_role', 'docente');
        })->get();

        $facultades = Facultad::all();
        $campus = Campus::all();

        return view('Administrador.GestionarDocentes.index', compact('editando', 'docentes', 'facultades', 'campus'));
    }

    public function update(Request $request, $id)
    {
        $docente = User::findOrFail($id);

        $request->validate([
            'numero_cuenta' => ['required', 'string', 'max:13', 'unique:users,numero_cuenta,' . $id],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $id],
            'telefono' => ['required', 'string', 'max:20'],
            'id_facultad' => ['required', 'exists:facultad,id'],
            'id_campus' => ['required', 'exists:campus,id'],
        ]);

        $docente->numero_cuenta = $request->numero_cuenta;
        $docente->name = $request->name;
        $docente->email = $request->email;
        $docente->telefono = $request->telefono;
        $docente->pais_telefono = $request->pais_telefono;

        if ($request->filled('password')) {
            $docente->password = Hash::make($request->password);
        }

        $docente->id_facultad = $request->id_facultad;
        $docente->id_campus = $request->id_campus;
        $docente->save();

        return redirect()->route('GestionarDocentes.index')->with('success', 'Docente actualizado exitosamente.');
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
