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

class GestionarAdminsController extends Controller
{
    public function index()
    {
        $admins = User::whereHas('role', function($query) {
            $query->where('nombre_role', 'admin'); // O el nombre correcto de tu rol
        })->get();

        $facultades = Facultad::all();
        $campus = Campus::all();

        return view('Administrador.GestionarAdmins.index', compact('admins', 'facultades', 'campus'));
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

        $role = Role::where('nombre_role', 'admin')->first();

        if (!$role) {
            return redirect()->back()->with('error', 'El rol de administrador no existe.');
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

        return redirect()->route('GestionarAdmins.index')->with('success', 'Administrador creado correctamente.');
    }

    public function edit($id)
    {
        $editando = User::findOrFail($id);
        $admins = User::whereHas('role', function ($query){
            $query->where('nombre_role', 'admin');
        })->get();

        $facultades = Facultad::all();
        $campus = Campus::all();
        $abrirModalEdicion = true;

        return view('Administrador.GestionarAdmins.index', compact('editando', 'admins', 'facultades', 'campus', 'abrirModalEdicion'));
    }

    public function update(Request $request, $id)
    {
        $admin = User::findOrFail($id);

        $request->validate([
            'numero_cuenta' => ['required', 'string', 'max:13', 'unique:users,numero_cuenta,' . $id],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $id],
            'telefono' => ['required', 'string', 'max:20'],
            'id_facultad' => ['required', 'exists:facultad,id'],
            'id_campus' => ['required', 'exists:campus,id'],
        ]);

        $admin->numero_cuenta = $request->numero_cuenta;
        $admin->name = $request->name;
        $admin->email = $request->email;
        $admin->telefono = $request->telefono;
        $admin->pais_telefono = $request->pais_telefono;

        if ($request->filled('password')) {
            $admin->password = Hash::make($request->password);
        }

        $admin->id_facultad = $request->id_facultad;
        $admin->id_campus = $request->id_campus;
        $admin->save();

        return redirect()->route('GestionarAdmins.index')->with('success', 'Administrador actualizado correctamente.');
    }



     public function show(Request $request)
    {

    }

    public function destroy($id)
    {
        $gestionarAdmin = User::findOrFail($id);
        $gestionarAdmin->delete();
        return redirect()->route('GestionarAdmins.index')->with('success', 'Administrador eliminado correctamente');
    }

}