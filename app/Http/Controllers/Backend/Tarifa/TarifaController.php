<?php

namespace App\Http\Controllers\Backend\Tarifa;

use App\Http\Controllers\Controller;
use App\Models\Tarifa;
use App\Models\History;
use Illuminate\Http\Request;

class TarifaController extends Controller
{
    public function index()
    {
        $tarifas = Tarifa::orderBy('monto')->paginate(20);
        return view('tarifas.index', compact('tarifas'));
    }

    public function add()
    {
        return view('tarifas.create');
    }

    public function create(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'monto' => 'required|integer|min:0',
            'descripcion' => 'nullable|string',
            'estado' => 'required|in:activa,inactiva'
        ]);

        Tarifa::create($request->all());

        return redirect()->route('tarifas.index')
            ->with('success', 'Tarifa creada exitosamente.');
    }

    public function edit($id)
    {
        $tarifa = Tarifa::findOrFail($id);
        return view('tarifas.edit', compact('tarifa'));
    }

    public function update(Request $request)
    {
        $tarifa = Tarifa::findOrFail($request->id);

        $request->validate([
            'nombre' => 'required|string|max:100',
            'monto' => 'required|integer|min:0',
            'descripcion' => 'nullable|string',
            'estado' => 'required|in:activa,inactiva'
        ]);

        $tarifa->update($request->except(['id']));

        return redirect()->route('tarifas.index')
            ->with('success', 'Tarifa actualizada exitosamente.');
    }

    public function delete($id)
    {
        $tarifa = Tarifa::findOrFail($id);

        // Verificar si hay estudiantes usando esta tarifa
        $estudiantesCount = History::where('tarifa_id', $id)->count();
        if ($estudiantesCount > 0) {
            return redirect()->route('tarifas.index')
                ->with('error', "No se puede eliminar. Hay {$estudiantesCount} estudiantes usando esta tarifa.");
        }

        $tarifa->delete();

        return redirect()->route('tarifas.index')
            ->with('success', 'Tarifa eliminada exitosamente.');
    }

    /**
     * Ver estudiantes que usan esta tarifa
     */
    public function viewStudents(Request $request, $id)
    {
        $tarifa = Tarifa::findOrFail($id);

        $query = History::where('tarifa_id', $id)
            ->with(['colegio', 'ruta']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('cedula', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('name')->paginate(20)->withQueryString();

        $stats = [
            'total' => History::where('tarifa_id', $id)->count(),
            'active' => History::where('tarifa_id', $id)->where('status', 1)->count(),
        ];
        $stats['inactive'] = $stats['total'] - $stats['active'];

        return view('tarifas.students', compact('tarifa', 'students', 'stats'));
    }
}
