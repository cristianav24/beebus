<?php

namespace App\Http\Controllers\Backend\Credits;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Utils\Activity\SaveActivityLogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\History;
use Config;

class CreditsController extends Controller
{
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'cedula' => 'required|string|max:20',
            'amount' => 'required|numeric|min:1|max:10000',
            'payment_method' => 'required|string|in:transfer,card,paypal',
            'payment_receipt' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);
    }

    /**
     * Get named route
     *
     */
    private function getRoute()
    {
        return 'credits';
    }

    /**
     * Show the form for recharging credits.
     *
     * @return \Illuminate\Http\Response
     */
    public function recharge()
    {
        $data = new \stdClass();
        $data->form_action = $this->getRoute() . '.store';
        $data->button_text = 'Recargar Créditos';
        $data->cedula = '';
        $data->amount = '';
        $data->payment_method = '';
        $data->payment_receipt = '';

        return view('backend.credits.form', [
            'data' => $data
        ]);
    }

    /**
     * Store the credit recharge in database.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $new = $request->all();
        try {
            $this->validator($new)->validate();

            // Find history record by cedula
            $targetHistory = History::where('cedula', $new['cedula'])->first();
            
            if (!$targetHistory) {
                return redirect()->route($this->getRoute() . '.recharge')->with('error', 'No se encontró ningún registro con la cédula: ' . $new['cedula']);
            }

            $adminUser = Auth::user();

            // Upload payment receipt
            $receiptFileName = null;
            if ($request->hasFile('payment_receipt')) {
                $file = $request->file('payment_receipt');
                $receiptFileName = $targetHistory->id . "_receipt_" . time() . "." . $file->getClientOriginalExtension();
                $file->move(Config::get('const.UPLOAD_PATH'), $receiptFileName);
            }

            // Add credits to target history record
            $currentBalance = $targetHistory->creditos ?? 0;
            $newBalance = $currentBalance + $new['amount'];

            $targetHistory->update(['creditos' => $newBalance]);

            // Save log for credit recharge
            $logData = [
                'admin_user_id' => $adminUser->id,
                'target_history_id' => $targetHistory->id,
                'target_name' => $targetHistory->name,
                'target_cedula' => $targetHistory->cedula,
                'amount' => $new['amount'],
                'payment_method' => $new['payment_method'],
                'payment_receipt' => $receiptFileName,
                'previous_balance' => $currentBalance,
                'new_balance' => $newBalance,
            ];

            $controller = new SaveActivityLogController();
            $controller->saveLog($logData, "Credit Recharge to History Record");

            return redirect()->route($this->getRoute() . '.recharge')->with('success', 'Créditos recargados exitosamente para ' . $targetHistory->name . ' (Cédula: ' . $targetHistory->cedula . '). Nuevo saldo: ' . $newBalance . ' créditos.');
        } catch (\Exception $e) {
            return redirect()->route($this->getRoute() . '.recharge')->with('error', 'Error al recargar créditos. Por favor, inténtelo nuevamente.');
        }
    }
}
