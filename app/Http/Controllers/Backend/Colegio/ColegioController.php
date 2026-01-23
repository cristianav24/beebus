<?php

namespace App\Http\Controllers\Backend\Colegio;

use App\Http\Controllers\Controller;
use App\Models\Colegio;
use App\Models\History;
use Illuminate\Http\Request;
use Crypt;

class ColegioController extends Controller
{
    public function index()
    {
        $colegios = Colegio::orderBy('nombre')->paginate(15);
        return view('colegios.index', compact('colegios'));
    }

    public function add()
    {
        return view('colegios.create');
    }

    public function create(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'direccion' => 'nullable|string',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'codigo_institucional' => 'nullable|string|max:50|unique:colegios',
            'estado' => 'required|in:activo,inactivo'
        ]);

        Colegio::create($request->all());

        return redirect()->route('colegios.index')
            ->with('success', 'Colegio creado exitosamente.');
    }

    public function edit($id)
    {
        $colegio = Colegio::findOrFail($id);
        return view('colegios.edit', compact('colegio'));
    }

    public function update(Request $request)
    {
        $colegio = Colegio::findOrFail($request->id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'direccion' => 'nullable|string',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'codigo_institucional' => 'nullable|string|max:50|unique:colegios,codigo_institucional,' . $colegio->id,
            'estado' => 'required|in:activo,inactivo'
        ]);

        $colegio->update($request->except(['id']));

        return redirect()->route('colegios.index')
            ->with('success', 'Colegio actualizado exitosamente.');
    }

    public function delete($id)
    {
        $colegio = Colegio::findOrFail($id);
        $colegio->delete();

        return redirect()->route('colegios.index')
            ->with('success', 'Colegio eliminado exitosamente.');
    }

    /**
     * Show students belonging to a specific colegio
     */
    public function viewStudents(Request $request, $id)
    {
        $colegio = Colegio::findOrFail($id);

        // Get students that belong to this colegio
        $query = History::where('colegio_id', $id)
            ->with(['beca', 'ruta']);

        // Add search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('cedula', 'like', "%{$search}%")
                  ->orWhere('seccion', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('name')->paginate(20)->withQueryString();

        $students->getCollection()->transform(function ($student) {
            $student->codigo_qr = Crypt::encryptString($student->id);;
            return $student;
        });

        // Also get students with string-based colegio matching (for backward compatibility)
        if ($students->count() === 0) {
            $students = History::where('colegio', $colegio->nombre)
                ->with(['beca', 'ruta'])
                ->orderBy('name')
                ->paginate(20);
        }

        // Statistics
        $totalStudents = $students->total();
        $activeStudents = History::where(function ($query) use ($id, $colegio) {
            $query->where('colegio_id', $id)
                ->orWhere('colegio', $colegio->nombre);
        })
            ->where('status', 1)
            ->count();
        $inactiveStudents = $totalStudents - $activeStudents;

        $totalCredits = History::where(function ($query) use ($id, $colegio) {
            $query->where('colegio_id', $id)
                ->orWhere('colegio', $colegio->nombre);
        })
            ->where('status', 1)
            ->sum('creditos');

        $stats = [
            'total' => $totalStudents,
            'active' => $activeStudents,
            'inactive' => $inactiveStudents,
            'total_credits' => $totalCredits
        ];

        return view('colegios.students', compact('colegio', 'students', 'stats'));
    }
}
