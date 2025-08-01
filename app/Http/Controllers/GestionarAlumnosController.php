<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Role;
use App\Models\Campus;
use App\Models\Facultad;
use App\Mail\CredencialesMail;

class GestionarAlumnosController extends Controller
{
     public function index()
    {
        // Obtenemos el campus del administrador autenticado
        $adminCampus = Auth::user()->id_campus;
        
        // Filtramos alumnos por el campus del administrador
        $alumnos = User::whereHas('role', function($query) {
            $query->where('nombre_role', 'alumno');
        })->where('id_campus', $adminCampus)->get();
        
        // Cargamos los campus y facultades para los selectores
        $campus = Campus::all();
        $facultades = Facultad::all();
        
        // Devuelve la vista con los datos de los alumnos, campus y facultades
        return view ('Administrador.GestionarAlumnos.index', compact('alumnos', 'campus', 'facultades'));
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

        // Buscar el ID del rol alumno
        $role = Role::where('nombre_role', 'alumno')->first();
        
        if (!$role) {
            return redirect()->back()->with('error', 'El rol de alumno no existe en el sistema.');
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

        return redirect()->route('AsignarTerna.create')->with('success', 'Alumno creado exitosamente.');
    }

    public function edit($id)
    {
        $adminCampus = Auth::user()->id_campus;
        
        $editando = User::findOrFail($id);
        
        // Verificar que el alumno pertenezca al mismo campus que el administrador
        if ($editando->id_campus != $adminCampus) {
            return redirect()->route('GestionarAlumnos.index')
                ->with('error', 'No tienes permiso para editar alumnos de otro campus.');
        }
        
        $alumnos = User::whereHas('role', function ($query){
            $query->where('nombre_role', 'alumno');
        })->where('id_campus', $adminCampus)->get();

        $facultades = Facultad::all();
        $campus = Campus::all();
        $abrirModalEdicion = true;

        return view('Administrador.GestionarAlumnos.index', compact('editando', 'alumnos', 'facultades','campus', 'abrirModalEdicion'));
    }


    public function update(Request $request, $id)
    {
        $alumno = User::findOrFail($id);

        $request->validate([
            'numero_cuenta' => ['required', 'string', 'max:13', 'unique:users,numero_cuenta,' . $id],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $id],
            'telefono' => ['required', 'string', 'max:20'],
            'id_facultad' => ['required', 'exists:facultad,id'],
            'id_campus' => ['required', 'exists:campus,id'],
        ]);

        $alumno->numero_cuenta = $request->numero_cuenta;
        $alumno->name = $request->name;
        $alumno->email = $request->email;
        $alumno->telefono = $request->telefono;
        $alumno->pais_telefono = $request->pais_telefono;


        // Actualiza la contraseña solo si se llenó el campo
    if ($request->filled('password')) {
        $alumno->password = Hash::make($request->password);
    }

    $alumno->id_facultad = $request->id_facultad;
    $alumno->id_campus = $request->id_campus;
    $alumno->save();

    return redirect()->route('GestionarAlumnos.index')->with('success', 'Alumno actualizado exitosamente.');
    }

     public function show(Request $request)
    {
        
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Si es un alumno, verificar si está en alguna terna
        if ($user->isAlumno()) {
            // Obtener todas las ternas a las que pertenece el alumno
            $ternasIds = DB::table('user_terna_transitiva')
                ->where('id_user', $id)
                ->pluck('id_terna')
                ->toArray();
                
            // Para cada terna, verificar si hay otros alumnos
            foreach ($ternasIds as $ternaId) {
                $terna = \App\Models\Terna::find($ternaId);
                
                // Contar cuántos alumnos hay en esta terna
                $alumnosCount = $terna->users()
                    ->whereHas('role', function($query) {
                        $query->where('nombre_role', 'alumno');
                    })
                    ->count();
                    
                // Si solo hay un alumno (el que estamos eliminando), eliminar la terna completa
                if ($alumnosCount <= 1) {
                    // Obtener los informes asociados a esta terna para eliminar los archivos físicos
                    $informes = \App\Models\Informe::where('id_terna', $ternaId)->get();
                    
                    foreach ($informes as $informe) {
                        // Eliminar el archivo físico del servidor
                        \Illuminate\Support\Facades\Storage::delete('informes/' . $informe->nombre_archivo);
                        // También verificar en la carpeta private/informes por si acaso
                        \Illuminate\Support\Facades\Storage::delete('private/informes/' . $informe->nombre_archivo);
                    }
                    
                    // Eliminar los registros de informes de la base de datos
                    \App\Models\Informe::where('id_terna', $ternaId)->delete();
                    
                    // Eliminar las relaciones en la tabla pivote
                    \App\Models\UserTernaTransitiva::where('id_terna', $ternaId)->delete();
                    
                    // Eliminar la terna
                    $terna->delete();
                }
            }
        }
        
        // Finalmente eliminar el usuario
        $user->delete();
        
        return redirect()->route('GestionarAlumnos.index')
            ->with('success', 'Usuario eliminado correctamente');
    }
}