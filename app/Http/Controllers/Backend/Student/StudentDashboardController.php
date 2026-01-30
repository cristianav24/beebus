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

        // Buscar el registro del estudiante por user_id
        $student = History::with(['colegio', 'beca', 'ruta', 'tarifa'])
            ->where('user_id', $user->id)
            ->first();

        if (!$student) {
            return redirect()->route('login')->with('error', 'No se encontro tu perfil de estudiante.');
        }

        // Si esta inactivo, no deberia llegar aqui (login lo bloquea), pero por seguridad
        if ($student->status == 0) {
            Auth::logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Tu cuenta no esta activa.']);
        }

        $isPending = $student->status == 2;

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
            'statistics',
            'isPending'
        ));
    }

    public function uploadContract(Request $request)
    {
        $request->validate([
            'contract_file' => 'required|file|mimes:pdf|max:5120', // 5MB max
        ]);

        $user = Auth::user();
        // Permitir status 1 (activo) y 2 (pendiente) para subir contrato
        $student = History::where('user_id', $user->id)->whereIn('status', [1, 2])->first();

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

        // Actualizar registro y activar estudiante
        $student->update([
            'contrato_subido' => 1,
            'contrato_url' => $path,
            'contrato_fecha_subida' => now(),
            'contrato_subido_por' => $user->id,
            'status' => 1, // ACTIVAR ESTUDIANTE
        ]);

        return back()->with('success', 'Contrato subido correctamente. Tu cuenta ha sido activada.');
    }

    public function downloadContract()
    {
        $user = Auth::user();
        // Permitir status 1 (activo) y 2 (pendiente) para descargar contrato
        $student = History::where('user_id', $user->id)->whereIn('status', [1, 2])->first();

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
        // Permitir status 1 (activo) y 2 (pendiente) para descargar QR
        $student = History::where('user_id', $user->id)->whereIn('status', [1, 2])->first();

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

    /**
     * Mostrar pagina de firma digital
     */
    public function showSignContract()
    {
        $user = Auth::user();
        $student = History::where('user_id', $user->id)->whereIn('status', [1, 2])->first();

        if (!$student) {
            return redirect()->route('student.dashboard')->with('error', 'No se encontro tu perfil de estudiante.');
        }

        return view('backend.student.sign-contract', compact('student'));
    }

    /**
     * Guardar firma digital y activar estudiante
     */
    public function saveSignature(Request $request)
    {
        $request->validate([
            'pdf_signed' => 'required|string',
        ]);

        $user = Auth::user();
        $student = History::where('user_id', $user->id)->whereIn('status', [1, 2])->first();

        if (!$student) {
            return response()->json(['status' => 'error', 'message' => 'Estudiante no encontrado'], 404);
        }

        try {
            // Decodificar el PDF en base64
            $pdfData = base64_decode($request->pdf_signed);
            if ($pdfData === false) {
                throw new \Exception('Error al decodificar el PDF');
            }

            // Eliminar contrato anterior si existe
            if ($student->contrato_url && file_exists(public_path($student->contrato_url))) {
                unlink(public_path($student->contrato_url));
            }

            // Crear directorio si no existe
            $contractsPath = public_path('contracts');
            if (!file_exists($contractsPath)) {
                mkdir($contractsPath, 0755, true);
            }

            // Guardar el PDF firmado
            $filename = 'contrato_firmado_' . $student->id . '_' . time() . '.pdf';
            file_put_contents($contractsPath . '/' . $filename, $pdfData);

            // Actualizar registro del estudiante
            $student->update([
                'contrato_subido' => 1,
                'contrato_url' => 'contracts/' . $filename,
                'contrato_fecha_subida' => now(),
                'contrato_subido_por' => $user->id,
                'status' => 1, // ACTIVAR ESTUDIANTE
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Contrato firmado y guardado correctamente. Tu cuenta ha sido activada.',
                'redirect' => route('student.dashboard')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al guardar la firma: ' . $e->getMessage()
            ], 500);
        }
    }
}
