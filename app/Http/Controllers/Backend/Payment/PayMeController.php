<?php

namespace App\Http\Controllers\Backend\Payment;

use App\Http\Controllers\Controller;
use App\Models\CreditTransaction;
use App\Models\ParentChildRelationship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayMeController extends Controller
{
    public function __construct()
    {
        // Exclude processResponse from auth middleware (PayMe callback needs public access)
        $this->middleware('auth')->except(['processResponse']);
        $this->middleware('role:guest')->except(['processResponse']);
    }

    /**
     * Show credit recharge form with PayMe
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

        // Predefined amounts in colones
        $predefinedAmounts = [
            500 => '₡500',
            1000 => '₡1,000',
            1500 => '₡1,500',
            2000 => '₡2,000',
            2500 => '₡2,500',
            3000 => '₡3,000',
            3500 => '₡3,500',
            6000 => '₡6,000',
            6500 => '₡6,500'
        ];

        return view('backend.payment.payme-recharge', compact('children', 'totalCredits', 'predefinedAmounts'));
    }

    /**
     * Initialize PayMe payment
     */
    public function initializePayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|integer|min:500|max:6500',
            'student_id' => 'required|exists:histories,id'
        ]);

        try {
            $user = Auth::user();
            $amount = $request->amount;

            // Verify the student belongs to this parent
            $relationship = ParentChildRelationship::where('parent_user_id', $user->id)
                ->where('student_id', $request->student_id)
                ->where('status', 'approved')
                ->with('student')
                ->first();

            if (!$relationship) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para recargar créditos a este estudiante.'
                ], 403);
            }

            $student = $relationship->student;

            // Generate sequential PayMe operation number
            $paymeSeqId = DB::table('payme_sequences')->insertGetId(['created_at' => now()]);
            $purchaseOperationNumber = str_pad($paymeSeqId, 9, '0', STR_PAD_LEFT);

            // Create pending transaction
            $tempTransaction = CreditTransaction::create([
                'history_id' => $student->id,
                'type' => 'recarga_payme',
                'amount' => $amount,
                'balance_before' => $student->creditos,
                'balance_after' => $student->creditos, // Will be updated on success
                'description' => "Recarga con PayMe - Pendiente",
                'payment_method' => 'payme',
                'processed_by' => Auth::id(),
                'verification_status' => 'pending'
            ]);

            // PayMe configuration
            $acquirerId = config('services.payme.acquirer_id');
            $idCommerce = config('services.payme.commerce_id');
            $claveSecreta = config('services.payme.secret_key');
            $purchaseAmount = (string) ($amount * 100); // PayMe requires amount in cents
            $purchaseCurrencyCode = '188'; // 188 for CRC in production)

            // Generate purchase verification (SHA-512 hash) - EXACTLY like the working example
            $stringToHash = $acquirerId . $idCommerce . $purchaseOperationNumber . $purchaseAmount . $purchaseCurrencyCode . $claveSecreta;
            $purchaseVerification = openssl_digest($stringToHash, 'sha512');

            // DEBUG - Log the values
            Log::info('PayMe Purchase Verification Debug', [
                'acquirerId' => $acquirerId,
                'idCommerce' => $idCommerce,
                'purchaseOperationNumber' => $purchaseOperationNumber,
                'purchaseAmount' => $purchaseAmount,
                'purchaseCurrencyCode' => $purchaseCurrencyCode,
                'claveSecreta' => $claveSecreta,
                'stringToHash' => $stringToHash,
                'purchaseVerification' => $purchaseVerification
            ]);

            // Update transaction with operation number
            $tempTransaction->update([
                'description' => "Recarga con PayMe - Operación: {$purchaseOperationNumber}",
                'payme_operation_number' => $purchaseOperationNumber
            ]);

            $transaction = $tempTransaction;

            // Prepare PayMe data
            $paymeData = [
                'acquirerId' => $acquirerId,
                'idCommerce' => $idCommerce,
                'purchaseOperationNumber' => $purchaseOperationNumber,
                'purchaseAmount' => $purchaseAmount,
                'purchaseCurrencyCode' => $purchaseCurrencyCode,
                'language' => 'SP',
                'shippingFirstName' => $user->name,
                'shippingLastName' => 'ape',//importante o da error al abrir el modal para pagos payment
                'shippingEmail' => $user->email,
                'shippingAddress' => 'Costa Rica',
                'shippingZIP' => '10101',
                'shippingCity' => 'San José',
                'shippingState' => 'San José',
                'shippingCountry' => 'CR',
                'userCommerce' => $user->email,
                'userCodePayme' => $user->id . '--' . time(),
                'descriptionProducts' => "Recarga de créditos para {$student->name} - ₡" . number_format($amount, 0),
                'programmingLanguage' => 'PHP',
                'reserved1' => json_encode([
                    'transaction_id' => $transaction->id,
                    'student_id' => $student->id,
                    'user_id' => $user->id
                ]),
                'purchaseVerification' => $purchaseVerification,
                'payme_url' => config('services.payme.payme_url'),
                'student_name' => $student->name,
                'formatted_amount' => '₡' . number_format($amount, 0)
            ];

            return response()->json([
                'success' => true,
                'data' => $paymeData
            ]);
        } catch (\Exception $e) {
            Log::error('PayMe initialization error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al inicializar el pago: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process PayMe response (callback)
     */
    public function processResponse(Request $request)
    {
        try {
            // Get PayMe secret key
            $claveSecreta = config('services.payme.secret_key');

            // Get authorization result first to determine validation logic
            $authorizationResult = $request->input('authorizationResult');
            $purchaseOperationNumber = $request->input('purchaseOperationNumber');
            $authorizationCode = $request->input('authorizationCode');
            $errorCode = $request->input('errorCode');
            $errorMessage = $request->input('errorMessage');
            $reserved1 = $request->input('reserved1');

            // Get purchase verification from PayMe
            $purchaseVerificationPayMe = $request->input('purchaseVerification');

            // SECURITY: Only validate purchaseVerification for successful transactions
            // When user cancels or transaction fails, PayMe may not send purchaseVerification
            if ($authorizationResult === '00') {
                // For successful transactions, purchaseVerification is REQUIRED
                if (empty($purchaseVerificationPayMe)) {
                    Log::error('PayMe verification missing for successful transaction', [
                        'authorizationResult' => $authorizationResult,
                        'request_data' => $request->all()
                    ]);

                    return view('backend.payment.payme-response-simple', [
                        'success' => false,
                        'message' => 'Error de seguridad: falta verificación para transacción exitosa.',
                        'data' => []
                    ]);
                }

                // Generate our own verification
                $purchaseVerificationLocal = hash(
                    'sha512',
                    $request->input('acquirerId') .
                        $request->input('idCommerce') .
                        $purchaseOperationNumber .
                        $request->input('purchaseAmount') .
                        $request->input('purchaseCurrencyCode') .
                        $authorizationResult .
                        $claveSecreta
                );

                // SECURITY: Verify that it matches
                if ($purchaseVerificationPayMe !== $purchaseVerificationLocal) {
                    Log::error('PayMe verification mismatch', [
                        'received' => $purchaseVerificationPayMe,
                        'expected' => $purchaseVerificationLocal,
                        'request_data' => $request->all()
                    ]);

                    return view('backend.payment.payme-response-simple', [
                        'success' => false,
                        'message' => 'Transacción inválida. Los datos fueron alterados en el proceso de respuesta.',
                        'data' => []
                    ]);
                }
            } else {
                // For failed/cancelled transactions, log that verification was not provided
                Log::info('PayMe transaction not successful', [
                    'authorizationResult' => $authorizationResult,
                    'errorCode' => $errorCode,
                    'errorMessage' => $errorMessage,
                    'purchaseOperationNumber' => $purchaseOperationNumber,
                    'has_verification' => !empty($purchaseVerificationPayMe)
                ]);
            }

            DB::beginTransaction();

            // Decode reserved1 to get transaction details
            $transactionData = json_decode($reserved1, true);

            // Find the transaction
            $transaction = CreditTransaction::where('payme_operation_number', $purchaseOperationNumber)
                ->first();

            if (!$transaction) {
                DB::rollback();
                return view('backend.payment.payme-response-simple', [
                    'success' => false,
                    'message' => 'No se encontró la transacción.',
                    'data' => []
                ]);
            }

            // Check if transaction was successful (authorizationResult = '00' means success)
            if ($authorizationResult === '00') {
                // Find the student
                $student = \App\Models\History::find($transaction->history_id);

                if (!$student) {
                    DB::rollback();
                    return view('backend.payment.payme-response-simple', [
                        'success' => false,
                        'message' => 'No se encontró el estudiante.',
                        'data' => []
                    ]);
                }

                // Update student credits
                $previousCredits = $student->creditos;
                $student->creditos += $transaction->amount;
                $student->save();

                // Update transaction
                $transaction->balance_after = $student->creditos;
                $transaction->verification_status = 'verified';
                $transaction->payme_authorization_code = $authorizationCode;
                $transaction->payme_authorization_result = $authorizationResult;
                $transaction->processed_at = now();
                $transaction->save();

                DB::commit();

                return view('backend.payment.payme-response-simple', [
                    'success' => true,
                    'message' => '¡Pago exitoso! Los créditos han sido agregados.',
                    'data' => [
                        'student_name' => $student->name,
                        'amount' => $transaction->amount,
                        'formatted_amount' => '₡' . number_format($transaction->amount, 0),
                        'previous_balance' => $previousCredits,
                        'new_balance' => $student->creditos,
                        'formatted_new_balance' => '₡' . number_format($student->creditos, 0),
                        'authorization_code' => $authorizationCode,
                        'operation_number' => $purchaseOperationNumber,
                        'bin' => $request->input('bin'),
                        'brand' => $request->input('brand'),
                        'payment_reference' => $request->input('paymentReferenceCode')
                    ]
                ]);
            } else {
                // Payment failed or cancelled
                // Determine if it was a user cancellation or other error
                $isCancellation = ($errorCode === '2300' || stripos($errorMessage, 'cancel') !== false);
                $verificationStatus = $isCancellation ? 'cancelled' : 'failed';

                $transaction->verification_status = $verificationStatus;
                $transaction->payme_authorization_result = $authorizationResult;
                $transaction->payme_error_code = $errorCode;
                $transaction->payme_error_message = $errorMessage;
                $transaction->processed_at = now();
                $transaction->save();

                DB::commit();

                // Customize message based on type of failure
                $userMessage = $isCancellation
                    ? 'Operación cancelada. No se realizó ningún cargo.'
                    : 'El pago no fue exitoso.';

                return view('backend.payment.payme-response-simple', [
                    'success' => false,
                    'message' => $userMessage,
                    'is_cancellation' => $isCancellation,
                    'data' => [
                        'error_code' => $errorCode,
                        'error_message' => $errorMessage,
                        'authorization_result' => $authorizationResult,
                        'operation_number' => $purchaseOperationNumber
                    ]
                ]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('PayMe response processing error: ' . $e->getMessage(), [
                'request_data' => $request->all()
            ]);

            return view('backend.payment.payme-response-simple', [
                'success' => false,
                'message' => 'Error al procesar la respuesta del pago: ' . $e->getMessage(),
                'data' => []
            ]);
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
            ->where('type', 'recarga_payme')
            ->with('history')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('backend.payment.payment-history', compact('transactions'));
    }
}
