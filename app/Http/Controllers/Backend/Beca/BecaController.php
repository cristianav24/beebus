<?php

namespace App\Http\Controllers\Backend\Beca;

use App\Http\Controllers\Controller;
use App\Models\Beca;
use Illuminate\Http\Request;

class BecaController extends Controller
{
    public function index()
    {
        $becas = Beca::orderBy('nombre_beca')->paginate(15);
        return view('becas.index', compact('becas'));
    }

    public function add()
    {
        return view('becas.create');
    }

    public function create(Request $request)
    {
        $request->validate([
            'nombre_beca' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'monto_creditos' => 'required|integer|min:0',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'estado' => 'required|in:activa,inactiva,suspendida'
        ]);

        Beca::create($request->all());

        return redirect()->route('becas.index')
            ->with('success', 'Beca creada exitosamente.');
    }

    public function edit($id)
    {
        $beca = Beca::findOrFail($id);
        return view('becas.edit', compact('beca'));
    }

    public function update(Request $request)
    {
        $beca = Beca::findOrFail($request->id);
        
        $request->validate([
            'nombre_beca' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'monto_creditos' => 'required|integer|min:0',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'estado' => 'required|in:activa,inactiva,suspendida'
        ]);

        $beca->update($request->except(['id']));

        return redirect()->route('becas.index')
            ->with('success', 'Beca actualizada exitosamente.');
    }

    public function delete($id)
    {
        $beca = Beca::findOrFail($id);
        $beca->delete();

        return redirect()->route('becas.index')
            ->with('success', 'Beca eliminada exitosamente.');
    }
}