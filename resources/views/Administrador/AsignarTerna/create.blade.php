<x-app-layout>
    <div class="max-w-6xl mx-auto mt-10">

        <!-- Botón Agregar Terna -->
        <div class="flex justify-center mb-6">
            <button
                data-modal-target="add-terna-modal"
                data-modal-toggle="add-terna-modal"
                class="px-6 py-2 rounded-md font-semibold text-gray-900 shadow-md"
                style="background-color: #FFC436;">
                Agregar Terna
            </button>
        </div>


        <!-- Tabla -->
        <div class="overflow-x-auto shadow-md sm:rounded-lg">
            <table id="search-table" class="min-w-full text-gray-800 bg-gray-200">
                <thead style="background-color: #004CBE;" class="text-xs uppercase text-white">
                    <tr>
                        <th class="px-6 py-3">Estudiante</th>
                        <th class="px-6 py-3">Docente #1</th>
                        <th class="px-6 py-3">Docente #2</th>
                        <th class="px-6 py-3">Docente #3</th>
                        <th class="px-6 py-3">Docente #4 (opcional)</th>
                        <th class="px-6 py-3">Estado</th>
                        <th class="px-6 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ternas as $terna)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            @php
                                // Obtener el estudiante de esta terna
                                $estudiante = $terna->users()->whereHas('role', function($query) {
                                    $query->where('nombre_role', 'alumno');
                                })->first();
                                
                                // Obtener los docentes de esta terna
                                $docentes = $terna->users()->whereHas('role', function($query) {
                                    $query->where('nombre_role', 'docente');
                                })->get();
                            @endphp
                            
                            <!-- Estudiante -->
                            <td class="px-6 py-4 font-medium">
                                @if($estudiante)
                                    {{ $estudiante->name }} - {{ $estudiante->numero_cuenta }}
                                @else
                                    <span class="text-gray-400">No asignado</span>
                                @endif
                            </td>
                            
                            <!-- Docentes -->
                            @for($i = 0; $i < 4; $i++)
                                <td class="px-6 py-4">
                                    @if(isset($docentes[$i]))
                                        {{ $docentes[$i]->name }}
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            @endfor
                            
                            <!-- Estado -->
                            <td class="px-6 py-4">
                                @if($terna->estado_terna == 'Pendiente')
                                    <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded">Pendiente</span>
                                @elseif($terna->estado_terna == 'En Progreso')
                                    <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">En Progreso</span>
                                @elseif($terna->estado_terna == 'Aprobado')
                                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">Aprobado</span>
                                @endif
                            </td>
                            
                            <!-- Acciones -->
                            <td class="px-6 py-4 flex space-x-2">
                                <a href="#" class="text-blue-600 hover:text-blue-800" title="Editar">✏️</a>
                                <form action="{{ route('AsignarTerna.destroy', $terna->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800" title="Eliminar"
                                            onclick="return confirm('¿Estás seguro de eliminar esta terna?')">🗑️</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr class="bg-white border-b">
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                No hay ternas asignadas todavía
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!--FINAL DE LA TABAL DE TERNAS -->


    <!-- Modal Agregar Terna -->
    <div id="add-terna-modal" tabindex="-1" aria-hidden="true"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50
               justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-lg max-h-full">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <!-- Modal header -->
                <div
                    class="flex items-center justify-between p-4 border-b rounded-t
                           dark:border-gray-600 border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Agregar Terna
                    </h3>
                    <button type="button"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900
                               rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center
                               dark:hover:bg-gray-600 dark:hover:text-white"
                        data-modal-hide="add-terna-modal">
                        ✖️
                        <span class="sr-only">Cerrar modal</span>
                    </button>
                </div>

                <!-- Modal body -->
                <div class="p-4">
                    <form class="space-y-4" action="{{ route('AsignarTerna.store') }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Estudiante -->
                            <div>
                                <label for="estudiante"
                                    class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Estudiante</label>
                                <select id="estudiante" name="estudiante"
                                    class="bg-gray-50 border border-gray-300 text-sm rounded-lg block w-full p-2.5
                                        dark:bg-gray-600 dark:border-gray-500 dark:text-white" required>
                                    <option value="" disabled selected>Selecciona un estudiante</option>
                                    @foreach($alumnos as $estudiante)
                                        <option value="{{ $estudiante->id }}">
                                            {{ $estudiante->name }} - {{ $estudiante->numero_cuenta }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Docente #1 -->
                            <div>
                                <label for="docente1"
                                    class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Docente #1</label>
                                <select id="docente1" name="docente1"
                                    class="bg-gray-50 border border-gray-300 text-sm rounded-lg block w-full p-2.5
                                        dark:bg-gray-600 dark:border-gray-500 dark:text-white" required>
                                    <option value="" disabled selected>Selecciona un Docente</option>
                                    @foreach($docentes as $docente)
                                        <option value="{{ $docente->id }}">
                                            {{ $docente->name }} - {{ $docente->numero_cuenta }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Docente #2 -->
                            <div>
                                <label for="docente2"
                                    class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Docente #2</label>
                                <select id="docente2" name="docente2"
                                    class="bg-gray-50 border border-gray-300 text-sm rounded-lg block w-full p-2.5
                                        dark:bg-gray-600 dark:border-gray-500 dark:text-white" required>
                                    <option value="" disabled selected>Selecciona un Docente</option>
                                    @foreach($docentes as $docente)
                                        <option value="{{ $docente->id }}">
                                            {{ $docente->name }} - {{ $docente->numero_cuenta }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Docente #3 -->
                            <div>
                                <label for="docente3"
                                    class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Docente #3</label>
                                <select id="docente3" name="docente3"
                                    class="bg-gray-50 border border-gray-300 text-sm rounded-lg block w-full p-2.5
                                        dark:bg-gray-600 dark:border-gray-500 dark:text-white" required>
                                    <option value="" disabled selected>Selecciona un Docente</option>
                                    @foreach($docentes as $docente)
                                        <option value="{{ $docente->id }}">
                                            {{ $docente->name }} - {{ $docente->numero_cuenta }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Docente #4 (opcional) -->
                            <div>
                                <label for="docente4"
                                    class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Docente #4 (Opcional)</label>
                                <select id="docente4" name="docente4"
                                    class="bg-gray-50 border border-gray-300 text-sm rounded-lg block w-full p-2.5
                                        dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                                    <option value="" selected>Selecciona un Docente (Opcional)</option>
                                    @foreach($docentes as $docente)
                                        <option value="{{ $docente->id }}">
                                            {{ $docente->name }} - {{ $docente->numero_cuenta }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div
                            class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mt-6">
                            <button type="button" data-modal-hide="add-terna-modal"
                                class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400 text-gray-900">
                                Cancelar
                            </button>
                            <button type="submit" class="px-4 py-2 rounded text-gray-900 shadow-md"
                                style="background-color: #FFC436;">
                                Guardar
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        div.dataTables_filter {
            margin-bottom: 1rem;
        }
    </style>
</x-app-layout>