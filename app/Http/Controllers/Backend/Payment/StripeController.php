<?php

namespace App\Http\Controllers\Backend\Payment;

use App\Http\Controllers\Controller;
use App\Models\CreditTransaction;
use App\Models\ParentChildRelationship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\CardException;
use Stripe\Exception\ApiErrorException;

class StripeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:guest');
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Show credit recharge form with Stripe
     */
    public function showRechargeForm()
    {
        $user = Auth::user();

        // Get user's children to show current credits
        $children = ParentChildRelationship::with('student')
            ->where('parent_user_id', $user->id)
            ->where('status', 'approved')
            ->get();

        $totalCredits = $children->sum(function ($relationship) {
            return $relationship->student ? $relationship->student->creditos : 0;
        });

        // Predefined amounts in colones (minimum $10 USD = ₡5,000 CRC)
        $predefinedAmounts = [
            5000 => '₡5,000 (~$10)',
            10000 => '₡10,000 (~$20)', 
            15000 => '₡15,000 (~$30)',
            25000 => '₡25,000 (~$50)',
            50000 => '₡50,000 (~$100)',
            75000 => '₡75,000 (~$150)'
        ];

        return view('backend.payment.stripe-recharge', compact('children', 'totalCredits', 'predefinedAmounts'));
    }

    /**
     * Create payment intent for Stripe
     */
    public function createPaymentIntent(Request $request)
    {
        $request->validate([
            'amount' => 'required|integer|min:5000|max:500000', // Between ₡5,000 and ₡500,000
            'student_id' => 'required|exists:histories,id'
        ]);

        try {
            $user = Auth::user();
            $amount = $request->amount;

            // Verify the student belongs to this parent
            $relationship = ParentChildRelationship::where('parent_user_id', $user->id)
                ->where('student_id', $request->student_id)
                ->where('status', 'approved')
                ->first();

            if (!$relationship) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para recargar créditos a este estudiante.'
                ], 403);
            }

            // Convert colones to USD cents (Stripe requires minimum 50 cents USD)
            // Approximate rate: 1 USD = 500 CRC, so 1 CRC = 0.002 USD
            // Convert to USD cents: amount_crc * 0.002 * 100
            $usdAmount = ($amount * 0.002) * 100; // Convert to USD cents

            // Ensure minimum Stripe amount (50 cents)
            if ($usdAmount < 50) {
                return response()->json([
                    'success' => false,
                    'message' => 'El monto mínimo para pagos con tarjeta es ₡5,000 (aproximadamente $10 USD)'
                ], 400);
            }

            // Create payment intent
            $paymentIntent = PaymentIntent::create([
                'amount' => round($usdAmount),
                'currency' => 'usd', // Use USD instead of CRC
                'metadata' => [
                    'user_id' => $user->id,
                    'student_id' => $request->student_id,
                    'amount_colones' => $amount,
                    'type' => 'credit_recharge'
                ],
                'description' => "Recarga de créditos para {$relationship->student->name} - ₡" . number_format($amount, 0) . " (~$" . number_format($usdAmount / 100, 2) . " USD)"
            ]);

            return response()->json([
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'amount' => $amount,
                'formatted_amount' => '₡' . number_format($amount, 0)
            ]);
        } catch (ApiErrorException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el pago: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process successful payment and add credits
     */
    public function processPayment(Request $request)
    {
        $request->validate([
            'payment_intent_id' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            // Retrieve the payment intent from Stripe
            $paymentIntent = PaymentIntent::retrieve($request->payment_intent_id);

            if ($paymentIntent->status !== 'succeeded') {
                return response()->json([
                    'success' => false,
                    'message' => 'El pago no fue completado exitosamente.'
                ], 400);
            }

            $userId = $paymentIntent->metadata->user_id;
            $studentId = $paymentIntent->metadata->student_id;
            $amount = $paymentIntent->metadata->amount_colones;

            // Verify user matches authenticated user
            if ($userId != Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autorizado.'
                ], 403);
            }

            // Verify relationship still exists
            $relationship = ParentChildRelationship::where('parent_user_id', $userId)
                ->where('student_id', $studentId)
                ->where('status', 'approved')
                ->with('student')
                ->first();

            if (!$relationship) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para este estudiante.'
                ], 403);
            }

            $student = $relationship->student;

            // Update student credits
            $previousCredits = $student->creditos;
            $student->creditos += $amount;
            $student->save();

            // Record credit transaction
            CreditTransaction::create([
                'history_id' => $student->id,
                'type' => 'recarga_stripe',
                'amount' => $amount,
                'balance_before' => $previousCredits,
                'balance_after' => $student->creditos,
                'description' => "Recarga con tarjeta vía Stripe - Pago ID: {$paymentIntent->id}",
                'payment_method' => 'stripe',
                'stripe_payment_intent_id' => $paymentIntent->id,
                'processed_by' => Auth::id(),
                'processed_at' => now(),
                'verification_status' => 'verified'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "¡Recarga exitosa! Se agregaron ₡" . number_format($amount, 0) . " créditos a {$student->name}.",
                'student_name' => $student->name,
                'amount_added' => $amount,
                'previous_balance' => $previousCredits,
                'new_balance' => $student->creditos,
                'formatted_amount' => '₡' . number_format($amount, 0),
                'formatted_new_balance' => '₡' . number_format($student->creditos, 0)
            ]);
        } catch (ApiErrorException $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar el pago: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la transacción: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment history for current user
     */
    public function getPaymentHistory()
    {
        $user = Auth::user();

        // Get all children IDs for this parent
        $childrenIds = ParentChildRelationship::where('parent_user_id', $user->id)
            ->where('status', 'approved')
            ->pluck('student_id');

        // Get credit transactions for these children
        $transactions = CreditTransaction::whereIn('history_id', $childrenIds)
            ->where('type', 'recarga_stripe')
            ->with('history')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('backend.payment.payment-history', compact('transactions'));
    }
}
