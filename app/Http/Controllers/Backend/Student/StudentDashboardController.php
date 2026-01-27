<?php

namespace App\Http\Controllers\Backend\Student;

use App\Http\Controllers\Controller;
use App\Models\History;
use App\Models\CreditTransaction;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Auth;
use Storage;

class StudentDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();

        // Buscar el registro del estudiante por user_id con relaciones
        $student = History::with(['colegio', 'beca', 'ruta', 'tarifa'])
            ->where('user_id', $user->id)
            ->where('status', 1)
            ->first();

        if (!$student) {
            return redirect()->route('home')->with('error', 'No se encontró tu perfil de estudiante.');
        }

        // Encriptar ID para QR
        $qrData = Crypt::encryptString($student->id);

        // Obtener últimas transacciones
        $recentTransactions = CreditTransaction::where('history_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Obtener última asistencia
        $lastAttendance = Attendance::where('worker_id', $student->id)
            ->orderBy('date', 'desc')
            ->first();

        // Estadísticas
        $statistics = [
            'total_recharges' => CreditTransaction::where('history_id', $student->id)
                ->where('type', 'recarga')
                ->count(),
            'total_consumptions' => CreditTransaction::where('history_id', $student->id)
                ->where('type', 'consumo')
                ->count(),
            'total_attendances' => Attendance::where('worker_id', $student->id)->count(),
        ];

        return view('backend.student.dashboard', compact(
            'student',
            'qrData',
            'recentTransactions',
            'lastAttendance',
            'statistics'
        ));
    }

    public function uploadContract(Request $request)
    {
        $request->validate([
            'contract_file' => 'required|file|mimes:pdf|max:5120', // 5MB max
        ]);

        $user = Auth::user();
        $student = History::where('user_id', $user->id)->where('status', 1)->first();

        if (!$student) {
            return back()->with('error', 'No se encontró tu perfil de estudiante.');
        }

        // Eliminar contrato anterior si existe
        if ($student->contrato_url && file_exists(public_path($student->contrato_url))) {
            unlink(public_path($student->contrato_url));
        }

        // Guardar nuevo contrato en public/contracts
        $file = $request->file('contract_file');
        $filename = 'contrato_' . $student->id . '_' . time() . '.pdf';

        // Crear directorio si no existe
        $contractsPath = public_path('contracts');
        if (!file_exists($contractsPath)) {
            mkdir($contractsPath, 0755, true);
        }

        // Mover archivo
        $file->move($contractsPath, $filename);
        $path = 'contracts/' . $filename;

        // Actualizar registro
        $student->update([
            'contrato_subido' => 1,
            'contrato_url' => $path,
            'contrato_fecha_subida' => now(),
            'contrato_subido_por' => $user->id,
        ]);

        return back()->with('success', 'Contrato subido correctamente.');
    }

    public function downloadContract()
    {
        $user = Auth::user();
        $student = History::where('user_id', $user->id)->where('status', 1)->first();

        if (!$student || !$student->contrato_url) {
            return back()->with('error', 'No se encontró el contrato.');
        }

        $filePath = public_path($student->contrato_url);
        if (!file_exists($filePath)) {
            return back()->with('error', 'El archivo del contrato no existe.');
        }

        return response()->download($filePath, 'Contrato_BeeBus_2026.pdf');
    }

    public function downloadQR()
    {
        $user = Auth::user();
        $student = History::where('user_id', $user->id)->where('status', 1)->first();

        if (!$student) {
            return back()->with('error', 'No se encontró tu perfil de estudiante.');
        }

        $qrData = Crypt::encryptString($student->id);

        return response()->json([
            'success' => true,
            'qr_data' => $qrData,
            'student_id' => $student->id
        ]);
    }

    public function downloadContractTemplate()
    {
        $templatePath = public_path('templates/Contrato_Bee_Bus_2026.pdf');

        if (!file_exists($templatePath)) {
            return back()->with('error', 'El contrato template no está disponible. Contacta al administrador.');
        }

        return response()->download($templatePath, 'Contrato_Bee_Bus_2026.pdf');
    }
}
