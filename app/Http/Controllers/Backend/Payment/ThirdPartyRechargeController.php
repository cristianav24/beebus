<?php

namespace App\Http\Controllers\Backend\Payment;

use App\Http\Controllers\Controller;
use App\Models\CreditTransaction;
use App\Models\History;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class ThirdPartyRechargeController extends Controller
{
    /**
     * Show search form for student by DNI/Cedula
     */
    public function showSearchForm()
    {
        return view('backend.payment.third-party-search');
    }

    /**
     * Search student by DNI/Cedula
     */
    public function searchStudent(Request $request)
    {
        $request->validate([
            'cedula' => 'required|string|min:3'
        ]);

        try {
            // Search student by cedula
            $student = History::where('cedula', $request->cedula)
                ->where('status', 1)
                ->first();

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró ningún estudiante con la cédula proporcionada.'
                ], 404);
            }

            // Store student ID in session for the recharge process
            Session::put('third_party_student_id', $student->id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'cedula' => $student->cedula,
                    'creditos' => $student->creditos,
                    'colegio' => $student->colegio
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Third party student search error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar el estudiante: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show recharge form for third party
     */
    public function showRechargeForm()
    {
        // Verify student is in session
        $studentId = Session::get('third_party_student_id');

        if (!$studentId) {
            return redirect()->route('third-party.search')
                ->with('error', 'Por favor, busque primero al estudiante.');
        }

        $student = History::find($studentId);

        if (!$student) {
            Session::forget('third_party_student_id');
            return redirect()->route('third-party.search')
                ->with('error', 'Estudiante no encontrado.');
        }

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

        return view('backend.payment.third-party-recharge', compact('student', 'predefinedAmounts'));
    }

    /**
     * Initialize PayMe payment for third party
     */
    public function initializePayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|integer|min:500|max:6500',
            'student_id' => 'required|exists:histories,id',
            'payer_name' => 'required|string|max:255',
            'payer_lastname' => 'required|string|max:255',
            'payer_email' => 'required|email'
        ]);

        try {
            $amount = $request->amount;
            $student = History::find($request->student_id);

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estudiante no encontrado.'
                ], 404);
            }

            // Verify student is the one in session
            $sessionStudentId = Session::get('third_party_student_id');
            if ($sessionStudentId != $student->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'El estudiante seleccionado no coincide con la sesión.'
                ], 403);
            }

            // Create pending transaction
            $payerFullName = $request->payer_name . ' ' . $request->payer_lastname;
            $tempTransaction = CreditTransaction::create([
                'history_id' => $student->id,
                'type' => 'recarga_tercero',
                'amount' => $amount,
                'balance_before' => $student->creditos,
                'balance_after' => $student->creditos, // Will be updated on success
                'description' => "Recarga por tercero - Pendiente (Pagador: {$payerFullName})",
                'payment_method' => 'payme',
                'verification_status' => 'pending'
            ]);

            // Generate unique 9-digit purchase operation number
            $purchaseOperationNumber = str_pad($tempTransaction->id, 9, '0', STR_PAD_LEFT);

            // PayMe configuration
            $acquirerId = config('services.payme.acquirer_id');
            $idCommerce = config('services.payme.commerce_id');
            $claveSecreta = config('services.payme.secret_key');
            $purchaseAmount = (string) ($amount * 100); // PayMe requires amount in cents
            $purchaseCurrencyCode = '188'; // CRC

            // Generate purchase verification (SHA-512 hash)
            $stringToHash = $acquirerId . $idCommerce . $purchaseOperationNumber . $purchaseAmount . $purchaseCurrencyCode . $claveSecreta;
            $purchaseVerification = openssl_digest($stringToHash, 'sha512');

            // DEBUG - Log the values
            Log::info('Third Party PayMe Purchase Debug', [
                'acquirerId' => $acquirerId,
                'idCommerce' => $idCommerce,
                'purchaseOperationNumber' => $purchaseOperationNumber,
                'purchaseAmount' => $purchaseAmount,
                'purchaseCurrencyCode' => $purchaseCurrencyCode,
                'purchaseVerification' => $purchaseVerification,
                'payer_name' => $payerFullName,
                'payer_email' => $request->payer_email,
                'student_name' => $student->name
            ]);

            // Update transaction with operation number
            $tempTransaction->update([
                'description' => "Recarga por tercero - Operación: {$purchaseOperationNumber} (Pagador: {$payerFullName})",
                'payme_operation_number' => $purchaseOperationNumber,
                'admin_notes' => json_encode([
                    'payer_name' => $request->payer_name,
                    'payer_lastname' => $request->payer_lastname,
                    'payer_fullname' => $payerFullName,
                    'payer_email' => $request->payer_email
                ])
            ]);

            // Prepare PayMe data
            $paymeData = [
                'acquirerId' => $acquirerId,
                'idCommerce' => $idCommerce,
                'purchaseOperationNumber' => $purchaseOperationNumber,
                'purchaseAmount' => $purchaseAmount,
                'purchaseCurrencyCode' => $purchaseCurrencyCode,
                'language' => 'SP',
                'shippingFirstName' => $request->payer_name,
                'shippingLastName' => $request->payer_lastname,
                'shippingEmail' => $request->payer_email,
                'shippingAddress' => 'Costa Rica',
                'shippingZIP' => '10101',
                'shippingCity' => 'San José',
                'shippingState' => 'San José',
                'shippingCountry' => 'CR',
                'userCommerce' => $request->payer_email,
                'userCodePayme' => 'TP-' . time(),
                'descriptionProducts' => "Recarga de créditos para {$student->name} - ₡" . number_format($amount, 0),
                'programmingLanguage' => 'PHP',
                'reserved1' => json_encode([
                    'transaction_id' => $tempTransaction->id,
                    'student_id' => $student->id,
                    'payer_name' => $request->payer_name,
                    'payer_lastname' => $request->payer_lastname,
                    'payer_fullname' => $payerFullName,
                    'payer_email' => $request->payer_email
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
            Log::error('Third party PayMe initialization error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al inicializar el pago: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process PayMe response for third party (callback)
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
                    Log::error('Third Party PayMe verification missing for successful transaction', [
                        'authorizationResult' => $authorizationResult,
                        'request_data' => $request->all()
                    ]);

                    return view('backend.payment.third-party-response', [
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
                    Log::error('Third Party PayMe verification mismatch', [
                        'received' => $purchaseVerificationPayMe,
                        'expected' => $purchaseVerificationLocal,
                        'request_data' => $request->all()
                    ]);

                    return view('backend.payment.third-party-response', [
                        'success' => false,
                        'message' => 'Transacción inválida. Los datos fueron alterados en el proceso de respuesta.',
                        'data' => []
                    ]);
                }
            } else {
                // For failed/cancelled transactions, log that verification was not provided
                Log::info('Third Party PayMe transaction not successful', [
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
                return view('backend.payment.third-party-response', [
                    'success' => false,
                    'message' => 'No se encontró la transacción.',
                    'data' => []
                ]);
            }

            // Check if transaction was successful (authorizationResult = '00' means success)
            if ($authorizationResult === '00') {
                // Find the student
                $student = History::find($transaction->history_id);

                if (!$student) {
                    DB::rollback();
                    return view('backend.payment.third-party-response', [
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

                // Clear session
                Session::forget('third_party_student_id');

                return view('backend.payment.third-party-response', [
                    'success' => true,
                    'message' => '¡Pago exitoso! Los créditos han sido agregados al estudiante.',
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

                // Get student info to restore session context
                $student = History::find($transaction->history_id);

                if ($student) {
                    // Restore student in session for retry
                    Session::put('third_party_student_id', $student->id);
                }

                // Customize message and redirect based on type of failure
                if ($isCancellation) {
                    // User cancelled - redirect to recharge form with message
                    return redirect()
                        ->route('third-party.recharge-form')
                        ->with('warning', 'Operación cancelada. No se realizó ningún cargo. Puede intentar nuevamente si lo desea.');
                } else {
                    // Payment error (card declined, etc) - redirect to recharge form with error details
                    $errorDetail = $errorMessage ? " ({$errorMessage})" : '';
                    return redirect()
                        ->route('third-party.recharge-form')
                        ->with('error', "El pago no fue exitoso{$errorDetail}. Por favor, verifique los datos e intente nuevamente.");
                }
            }
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Third party PayMe response processing error: ' . $e->getMessage(), [
                'request_data' => $request->all()
            ]);

            return view('backend.payment.third-party-response', [
                'success' => false,
                'message' => 'Error al procesar la respuesta del pago: ' . $e->getMessage(),
                'data' => []
            ]);
        }
    }
}
