<?php

namespace App\Http\Controllers\Backend\Transaction;

use App\Http\Controllers\Controller;
use App\Models\CreditTransaction;
use App\Models\History;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin|administrator');
    }

    /**
     * Display a listing of all credit transactions
     */
    public function index(Request $request)
    {
        $query = CreditTransaction::with(['history', 'attendance'])
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('history', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('cedula', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->paginate(25)->withQueryString();

        // Estadísticas
        $stats = $this->getTransactionStats();

        return view('backend.transactions.index', compact('transactions', 'stats'));
    }

    /**
     * Show transaction details
     */
    public function show($id)
    {
        $transaction = CreditTransaction::with(['history', 'attendance'])
            ->findOrFail($id);

        return view('backend.transactions.show', compact('transaction'));
    }

    /**
     * Verify a pending transaction
     */
    public function verify(Request $request, $id)
    {
        $transaction = CreditTransaction::findOrFail($id);

        $request->validate([
            'admin_notes' => 'nullable|string|max:500'
        ]);

        $transaction->update([
            'verification_status' => 'verified',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'admin_notes' => $request->admin_notes
        ]);

        return redirect()->back()->with('success', 'Transacción verificada exitosamente.');
    }

    /**
     * Reject a pending transaction
     */
    public function reject(Request $request, $id)
    {
        $transaction = CreditTransaction::findOrFail($id);

        $request->validate([
            'admin_notes' => 'required|string|max:500'
        ]);

        DB::beginTransaction();
        try {
            // Revertir créditos si ya fueron aplicados
            if ($transaction->type === 'recarga' && $transaction->history) {
                $student = $transaction->history;
                $student->creditos -= $transaction->amount;
                $student->save();
            }

            $transaction->update([
                'verification_status' => 'rejected',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'admin_notes' => $request->admin_notes
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Transacción rechazada y créditos revertidos.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error al rechazar la transacción.');
        }
    }

    /**
     * Get transaction statistics
     */
    private function getTransactionStats()
    {
        $today = now()->format('Y-m-d');
        $thisMonth = now()->format('Y-m');

        return [
            'total_transactions' => CreditTransaction::count(),
            'pending_verifications' => CreditTransaction::where('verification_status', 'pending')->count(),
            'today_transactions' => CreditTransaction::whereDate('created_at', $today)->count(),
            'today_amount' => CreditTransaction::whereDate('created_at', $today)->sum('amount'),
            'month_transactions' => CreditTransaction::where('created_at', 'like', $thisMonth . '%')->count(),
            'month_amount' => CreditTransaction::where('created_at', 'like', $thisMonth . '%')->sum('amount'),
            'total_recharges' => CreditTransaction::where('type', 'recarga')->sum('amount'),
            'total_consumptions' => CreditTransaction::where('type', 'consumo')->sum('amount'),
            'by_payment_method' => CreditTransaction::select('payment_method', DB::raw('count(*) as count'))
                ->groupBy('payment_method')
                ->pluck('count', 'payment_method')
                ->toArray(),
        ];
    }

    /**
     * Export transactions to CSV
     */
    public function export(Request $request)
    {
        $query = CreditTransaction::with(['history', 'attendance'])
            ->orderBy('created_at', 'desc');

        // Apply same filters as index
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('history', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('cedula', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->get();

        $filename = 'transacciones_credito_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fwrite($file, "\xEF\xBB\xBF");

            // Headers
            fputcsv($file, [
                'ID',
                'Estudiante',
                'Cédula',
                'Tipo',
                'Monto',
                'Saldo Anterior',
                'Saldo Posterior',
                'Descripción',
                'Método de Pago',
                'Estado Verificación',
                'Fecha Creación',
                'Fecha Pago',
                'Notas Admin'
            ]);

            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->id,
                    $transaction->history ? $transaction->history->name : 'N/A',
                    $transaction->history ? $transaction->history->cedula : 'N/A',
                    ucfirst($transaction->type),
                    number_format($transaction->amount, 2),
                    number_format($transaction->balance_before, 2),
                    number_format($transaction->balance_after, 2),
                    $transaction->description,
                    $transaction->payment_method ? ucfirst($transaction->payment_method) : 'N/A',
                    ucfirst($transaction->verification_status ?? 'N/A'),
                    $transaction->created_at ? $transaction->created_at->format('Y-m-d H:i:s') : 'N/A',
                    $transaction->payment_date ? $transaction->payment_date->format('Y-m-d') : 'N/A',
                    $transaction->admin_notes ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
