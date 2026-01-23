<?php

namespace App\Http\Controllers\Backend\Parent;

use App\Http\Controllers\Controller;
use App\Models\History;
use App\Models\ParentChildRelationship;
use App\Models\CreditTransaction;
use App\Models\ParentProfile;
use Illuminate\Http\Request;
use Auth;
use DB;

class ParentDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();

        // Verificar que sea un padre (role_id = 4)
        /*if ($user->role !== '4') {
            abort(403, 'Acceso denegado. Solo padres pueden acceder a este dashboard.');
        }*/

        // Verificar que el perfil esté completo
        $parent = ParentProfile::where('user_id', $user->id)->first();
        if (!$parent || !$parent->isProfileComplete()) {
            return redirect()->route('parent.profile.reminder');
        }

        // Obtener relaciones aprobadas del padre
        $approvedRelationships = ParentChildRelationship::where('parent_user_id', $user->id)
            ->where('status', 'approved')
            ->with(['student.colegio', 'student.beca', 'student.ruta'])
            ->get();

        // Obtener relaciones pendientes
        $pendingRelationships = ParentChildRelationship::where('parent_user_id', $user->id)
            ->where('status', 'pending')
            ->with('student')
            ->get();

        // Calcular estadísticas generales
        $totalStudents = $approvedRelationships->count();
        $totalCredits = 0;
        $totalRecharges = 0;
        $totalConsumptions = 0;

        foreach ($approvedRelationships as $relationship) {
            $student = $relationship->student;
            $totalCredits += $student->creditos;

            // Contar transacciones por tipo
            $recharges = CreditTransaction::where('history_id', $student->id)
                ->where('type', 'recarga')
                ->count();
            $consumptions = CreditTransaction::where('history_id', $student->id)
                ->where('type', 'consumo')
                ->count();

            $totalRecharges += $recharges;
            $totalConsumptions += $consumptions;
        }

        $statistics = [
            'total_students' => $totalStudents,
            'total_credits' => $totalCredits,
            'total_recharges' => $totalRecharges,
            'total_consumptions' => $totalConsumptions,
            'pending_requests' => $pendingRelationships->count()
        ];

        return view('backend.parent.dashboard', compact(
            'approvedRelationships',
            'pendingRelationships',
            'statistics'
        ));
    }

    public function assignChildren()
    {
        $user = Auth::user();
        
        // Verificar que el perfil esté completo
        $parent = ParentProfile::where('user_id', $user->id)->first();
        if (!$parent || !$parent->isProfileComplete()) {
            return redirect()->route('parent.profile.reminder');
        }
        
        return view('backend.parent.assign-children');
    }

    public function searchStudents(Request $request)
    {
        $searchTerm = $request->get('search');

        if (empty($searchTerm) || strlen($searchTerm) < 3) {
            return response()->json(['students' => []]);
        }

        $students = History::where('status', 1)
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('cedula', 'LIKE', '%' . $searchTerm . '%');
            })
            ->with(['colegio', 'beca'])
            ->limit(10)
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'cedula' => $student->cedula,
                    'colegio' => $student->colegio ? $student->colegio : $student->colegio,
                    'seccion' => $student->seccion,
                    'beca' => $student->beca ? $student->beca->nombre_beca : ($student->tipoBeca ?: 'Sin beca'),
                    'creditos' => $student->creditos
                ];
            });

        return response()->json(['students' => $students]);
    }

    public function requestRelationship(Request $request)
    {
        $user = Auth::user();
        
        // Verificar que el perfil esté completo
        $parent = ParentProfile::where('user_id', $user->id)->first();
        if (!$parent || !$parent->isProfileComplete()) {
            return response()->json([
                'success' => false,
                'message' => 'Debes completar tu perfil antes de poder hacer solicitudes.',
                'redirect' => route('parent.profile.reminder')
            ]);
        }
        
        $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'required|integer|exists:histories,id'
        ]);
        $created = 0;
        $existing = 0;

        foreach ($request->student_ids as $studentId) {
            // Verificar si ya existe una relación
            $existingRelation = ParentChildRelationship::where('parent_user_id', $user->id)
                ->where('student_id', $studentId)
                ->first();

            if (!$existingRelation) {
                ParentChildRelationship::create([
                    'parent_user_id' => $user->id,
                    'student_id' => $studentId,
                    'status' => 'pending',
                    'requested_at' => now()
                ]);
                $created++;
            } else {
                $existing++;
            }
        }

        $message = "Se han creado $created solicitudes.";
        if ($existing > 0) {
            $message .= " $existing relaciones ya existían.";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'created' => $created,
            'existing' => $existing
        ]);
    }

    public function studentTransactions($studentId)
    {
        $user = Auth::user();

        // Verificar que el padre tiene acceso a este estudiante
        $relationship = ParentChildRelationship::where('parent_user_id', $user->id)
            ->where('student_id', $studentId)
            ->where('status', 'approved')
            ->first();

        if (!$relationship) {
            abort(403, 'No tienes acceso a la información de este estudiante.');
        }

        $student = $relationship->student;

        $transactions = CreditTransaction::where('history_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('backend.parent.student-transactions', compact('student', 'transactions'));
    }
}
