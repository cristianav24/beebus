<?php

namespace App\Http\Controllers\Backend\Paradero;

use App\Http\Controllers\Controller;
use App\Models\Paradero;
use App\Models\Setting;
use App\Models\Colegio;
use App\Models\Zona;
use Illuminate\Http\Request;

class ParaderoController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:administrator|admin']);
    }

    /**
     * Listar paraderos agrupados por ruta
     */
    public function index(Request $request)
    {
        $zonas = Zona::where('estado', 'activo')->orderBy('nombre')->get();
        $colegios = Colegio::where('estado', 'activo')->orderBy('nombre')->get();
        $rutas = Setting::where('status', 'activo')->orderBy('key_app')->get();

        // Filtros
        $query = Paradero::with(['ruta.colegio.zona']);

        if ($request->filled('ruta_id')) {
            $query->where('ruta_id', $request->ruta_id);
        }

        if ($request->filled('colegio_id')) {
            $query->whereHas('ruta', function ($q) use ($request) {
                $q->where('colegio_id', $request->colegio_id);
            });
        }

        if ($request->filled('zona_id')) {
            $query->whereHas('ruta.colegio', function ($q) use ($request) {
                $q->where('zona_id', $request->zona_id);
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('search')) {
            $query->where('nombre', 'like', '%' . $request->search . '%');
        }

        $paraderos = $query->orderBy('ruta_id')
            ->orderBy('orden')
            ->paginate(50)
            ->withQueryString();

        // Estadisticas
        $stats = [
            'total' => Paradero::count(),
            'activos' => Paradero::where('estado', 'activo')->count(),
            'inactivos' => Paradero::where('estado', 'inactivo')->count(),
            'becas_empresariales' => Paradero::where('es_beca_empresarial', 1)->count(),
        ];

        return view('backend.paraderos.index', compact(
            'paraderos',
            'zonas',
            'colegios',
            'rutas',
            'stats'
        ));
    }

    /**
     * Mostrar formulario para crear paradero
     */
    public function add(Request $request)
    {
        $zonas = Zona::where('estado', 'activo')->orderBy('nombre')->get();
        $rutas = Setting::where('status', 'activo')->orderBy('key_app')->get();

        // Si viene con ruta_id preseleccionada
        $selectedRutaId = $request->get('ruta_id');

        return view('backend.paraderos.form', compact('zonas', 'rutas', 'selectedRutaId'));
    }

    /**
     * Guardar nuevo paradero
     */
    public function create(Request $request)
    {
        $request->validate([
            'ruta_id' => 'required|exists:settings,id',
            'nombre' => 'required|string|max:255',
            'hora' => 'nullable|string|max:20',
            'monto' => 'required|integer|min:0',
            'es_beca_empresarial' => 'nullable|boolean',
            'orden' => 'nullable|integer|min:0',
            'estado' => 'required|in:activo,inactivo',
        ]);

        // Si no se especifica orden, ponerlo al final
        $orden = $request->orden;
        if (is_null($orden)) {
            $maxOrden = Paradero::where('ruta_id', $request->ruta_id)->max('orden');
            $orden = ($maxOrden ?? 0) + 1;
        }

        Paradero::create([
            'ruta_id' => $request->ruta_id,
            'nombre' => $request->nombre,
            'hora' => $request->hora,
            'monto' => $request->monto,
            'es_beca_empresarial' => $request->es_beca_empresarial ?? 0,
            'orden' => $orden,
            'estado' => $request->estado,
        ]);

        return redirect()->route('paraderos.index', ['ruta_id' => $request->ruta_id])
            ->with('success', 'Paradero creado exitosamente.');
    }

    /**
     * Mostrar formulario para editar paradero
     */
    public function edit($id)
    {
        $paradero = Paradero::with('ruta.colegio.zona')->findOrFail($id);
        $zonas = Zona::where('estado', 'activo')->orderBy('nombre')->get();
        $rutas = Setting::where('status', 'activo')->orderBy('key_app')->get();

        return view('backend.paraderos.form', compact('paradero', 'zonas', 'rutas'));
    }

    /**
     * Actualizar paradero
     */
    public function update(Request $request)
    {
        $paradero = Paradero::findOrFail($request->id);

        $request->validate([
            'ruta_id' => 'required|exists:settings,id',
            'nombre' => 'required|string|max:255',
            'hora' => 'nullable|string|max:20',
            'monto' => 'required|integer|min:0',
            'es_beca_empresarial' => 'nullable|boolean',
            'orden' => 'nullable|integer|min:0',
            'estado' => 'required|in:activo,inactivo',
        ]);

        $paradero->update([
            'ruta_id' => $request->ruta_id,
            'nombre' => $request->nombre,
            'hora' => $request->hora,
            'monto' => $request->monto,
            'es_beca_empresarial' => $request->es_beca_empresarial ?? 0,
            'orden' => $request->orden ?? $paradero->orden,
            'estado' => $request->estado,
        ]);

        return redirect()->route('paraderos.index', ['ruta_id' => $paradero->ruta_id])
            ->with('success', 'Paradero actualizado exitosamente.');
    }

    /**
     * Eliminar paradero
     */
    public function delete($id)
    {
        $paradero = Paradero::findOrFail($id);
        $rutaId = $paradero->ruta_id;

        // Verificar si hay estudiantes usando este paradero
        $estudiantesCount = \App\Models\History::where('paradero_id', $id)->count();
        if ($estudiantesCount > 0) {
            return redirect()->route('paraderos.index', ['ruta_id' => $rutaId])
                ->with('error', "No se puede eliminar el paradero porque tiene {$estudiantesCount} estudiante(s) asignado(s).");
        }

        $paradero->delete();

        return redirect()->route('paraderos.index', ['ruta_id' => $rutaId])
            ->with('success', 'Paradero eliminado exitosamente.');
    }

    /**
     * Ver paraderos de una ruta especifica
     */
    public function porRuta($rutaId)
    {
        $ruta = Setting::with(['colegio.zona', 'paraderos' => function ($q) {
            $q->orderBy('orden');
        }])->findOrFail($rutaId);

        return view('backend.paraderos.por-ruta', compact('ruta'));
    }

    /**
     * Reordenar paraderos via AJAX
     */
    public function reordenar(Request $request)
    {
        $request->validate([
            'paraderos' => 'required|array',
            'paraderos.*.id' => 'required|exists:paraderos,id',
            'paraderos.*.orden' => 'required|integer|min:0',
        ]);

        foreach ($request->paraderos as $item) {
            Paradero::where('id', $item['id'])->update(['orden' => $item['orden']]);
        }

        return response()->json(['success' => true, 'message' => 'Orden actualizado']);
    }

    /**
     * Duplicar paraderos de una ruta a otra
     */
    public function duplicar(Request $request)
    {
        $request->validate([
            'ruta_origen_id' => 'required|exists:settings,id',
            'ruta_destino_id' => 'required|exists:settings,id|different:ruta_origen_id',
        ]);

        $paraderosOrigen = Paradero::where('ruta_id', $request->ruta_origen_id)
            ->orderBy('orden')
            ->get();

        if ($paraderosOrigen->isEmpty()) {
            return back()->with('error', 'La ruta de origen no tiene paraderos.');
        }

        foreach ($paraderosOrigen as $paradero) {
            Paradero::create([
                'ruta_id' => $request->ruta_destino_id,
                'nombre' => $paradero->nombre,
                'hora' => $paradero->hora,
                'monto' => $paradero->monto,
                'es_beca_empresarial' => $paradero->es_beca_empresarial,
                'orden' => $paradero->orden,
                'estado' => 'activo',
            ]);
        }

        return redirect()->route('paraderos.index', ['ruta_id' => $request->ruta_destino_id])
            ->with('success', 'Se duplicaron ' . $paraderosOrigen->count() . ' paraderos exitosamente.');
    }
}
