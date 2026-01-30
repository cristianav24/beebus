<?php

namespace App\Http\Controllers\Backend\Zona;

use App\Http\Controllers\Controller;
use App\Models\Zona;
use Illuminate\Http\Request;

class ZonaController extends Controller
{
    public function index()
    {
        $zonas = Zona::withCount('colegios')->orderBy('nombre')->paginate(15);
        return view('zonas.index', compact('zonas'));
    }

    public function add()
    {
        return view('zonas.create');
    }

    public function create(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:zonas',
            'estado' => 'required|in:activo,inactivo'
        ]);

        Zona::create($request->all());

        return redirect()->route('zonas.index')
            ->with('success', 'Zona creada exitosamente.');
    }

    public function edit($id)
    {
        $zona = Zona::findOrFail($id);
        return view('zonas.edit', compact('zona'));
    }

    public function update(Request $request)
    {
        $zona = Zona::findOrFail($request->id);

        $request->validate([
            'nombre' => 'required|string|max:100|unique:zonas,nombre,' . $zona->id,
            'estado' => 'required|in:activo,inactivo'
        ]);

        $zona->update($request->except(['id']));

        return redirect()->route('zonas.index')
            ->with('success', 'Zona actualizada exitosamente.');
    }

    public function delete($id)
    {
        $zona = Zona::findOrFail($id);

        if ($zona->colegios()->count() > 0) {
            return redirect()->route('zonas.index')
                ->with('error', 'No se puede eliminar la zona porque tiene colegios asignados.');
        }

        $zona->delete();

        return redirect()->route('zonas.index')
            ->with('success', 'Zona eliminada exitosamente.');
    }
}
