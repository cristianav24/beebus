<?php

namespace App\Http\Controllers\Api\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Setting;
use App\Models\History;
use App\Http\Controllers\EmailController;
use App\Models\Beca;
use App\Models\Tarifa;
use App\Models\Paradero;
use App\Models\CreditTransaction;
use Illuminate\Http\Request;
use Response;
use Carbon\Carbon;
use Config;
use File;
use Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\DB;

class ApiAttendanceController extends Controller
{
    public function apiSaveAttendance(Request $request)
    {
        // Request
        $new = $request->all();

        // Get Info de tabla _setting_ para id = 1;
        $getSetting = Setting::find(1);

        // Toda la DATA del Request
        $key = $new['key'];
        $q = $new['q'];
        try {
            $qrId = Crypt::decryptString($new['qr_id']);
        } catch (DecryptException $e) {
            $data = [
                'message' => 'Error Qr!',
            ];
            return response()->json($data, 200);
        }

        // Check the date query if exist
        if (!$request->exists('date')) {
            $date = Carbon::now()
                ->timezone($getSetting->timezone)
                ->format('Y-m-d');
        } else {
            $date = Carbon::parse($new['date'])->format('Y-m-d');
        }

        $location = $new['location'];

        $getSettingsSegunKey = Setting::where('key_app', $key)->first();

        if (!empty($key)) {
            if ($key == $getSettingsSegunKey->key_app) {
                // Marca (Check-In)
                if ($q == 'in') {
                    // Check the time query if exist
                    if (!$request->exists('time')) {
                        $in_time = new Carbon(
                            Carbon::now()
                                ->timezone($getSetting->timezone)
                                ->format('H:i:s'),
                        );
                    } else {
                        $in_time = new Carbon(Carbon::parse($new['time'])->format('H:i:s'));
                    }

                    // Check if user already check-in
                    $checkAlreadyCheckIn = Attendance::where('worker_id', $qrId)
                        ->where(
                            'date',
                            Carbon::now()
                                ->timezone($getSetting->timezone)
                                ->format('Y-m-d'),
                        )
                        ->where('in_time', '<>', null)
                        ->where('late_time', '<>', null)
                        ->where('out_time', null)
                        ->where('out_location', null)
                        ->first();

                    /*if ($checkAlreadyCheckIn) {
                        $data = [
                            'message' => 'already check-in',
                        ];
                        return response()->json($data, 200);
                    }*/

                    // Get late time
                    $startHour = Carbon::createFromFormat('H:i:s', $getSetting->start_time);
                    if (!$in_time->gt($startHour)) {
                        $lateTime = '00:00:00';
                    } else {
                        $lateTime = $in_time->diff($startHour)->format('%H:%I:%S');
                    }

                    // TOMO DATOS DE PERSONA QUE ESTÁ MARCANDO ASISTENCIA...
                    $getUserMarcaInfo = History::find($qrId);

                    // Validar que el usuario tenga ruta_id válido
                    if (!$getUserMarcaInfo || !$getUserMarcaInfo->ruta_id) {
                        $data = [
                            'message' => 'Error! Usuario sin ruta_id válido. Contacte al administrador.',
                        ];
                        return response()->json($data, 200);
                    }

                    //
                    $ruta_id = $getUserMarcaInfo->ruta_id;
                    $beca_id = $getUserMarcaInfo->beca_id;
                    $colegio_id = $getUserMarcaInfo->colegio_id;
                    $tarifa_id = $getUserMarcaInfo->tarifa_id;

                    // CONTROLES //
                    // Determinar monto a cobrar - Prioridad: 1) Beca, 2) Paradero, 3) Tarifa
                    $cuantoVoyARestar = 0;
                    $paradero_id = $getUserMarcaInfo->paradero_id;
                    $fuenteMonto = ''; // Para logging

                    // PRIORIDAD 1: Si tiene beca, usar monto de la beca
                    if ($beca_id) {
                        $beca = Beca::find($beca_id);
                        if ($beca && $beca->estado === 'activa') {
                            $cuantoVoyARestar = $beca->monto_creditos; // 0 = beca completa, >0 = beca parcial
                            $fuenteMonto = 'beca:' . $beca->nombre_beca;
                        }
                    }
                    // PRIORIDAD 2: Si NO tiene beca, verificar paradero
                    elseif ($paradero_id) {
                        $paradero = Paradero::find($paradero_id);
                        if ($paradero && $paradero->estado === 'activo') {
                            // Beca empresarial del paradero: monto 0
                            if ($paradero->es_beca_empresarial && $paradero->monto == 0) {
                                $cuantoVoyARestar = 0;
                                $fuenteMonto = 'paradero:beca_empresarial';
                            } else {
                                $cuantoVoyARestar = $paradero->monto;
                                $fuenteMonto = 'paradero:' . $paradero->nombre;
                            }
                        } else {
                            // Fallback a tarifa si paradero no es valido
                            if ($tarifa_id) {
                                $tarifa = Tarifa::find($tarifa_id);
                                if ($tarifa && $tarifa->estado === 'activa') {
                                    $cuantoVoyARestar = $tarifa->monto;
                                    $fuenteMonto = 'tarifa:' . $tarifa->nombre;
                                }
                            }
                        }
                    }
                    // PRIORIDAD 3: Legacy - usar tarifa directamente
                    elseif ($tarifa_id) {
                        $tarifa = Tarifa::find($tarifa_id);
                        if (!$tarifa || $tarifa->estado !== 'activa') {
                            $data = [
                                'message' => 'Error! Tarifa no válida o inactiva. Contacte al administrador.',
                            ];
                            return response()->json($data, 200);
                        }
                        $cuantoVoyARestar = $tarifa->monto;
                        $fuenteMonto = 'tarifa:' . $tarifa->nombre;
                    } else {
                        $data = [
                            'message' => 'Error! Estudiante sin beca, tarifa ni paradero asignado. Contacte al administrador.',
                        ];
                        return response()->json($data, 200);
                    }
                    $newsCreditos = $getUserMarcaInfo->creditos - $cuantoVoyARestar;
                    $chancesParaMarcar = $getUserMarcaInfo->chancesParaMarcar;

                    // Tomo datos de la persona que marca...
                    $nombreWithCedula = '';
                    $nombreWithCedula = $getUserMarcaInfo->name . ' (' . $getUserMarcaInfo->cedula . ')';
                    $emailDeLaPersona = '';
                    $emailDeLaPersona = $getUserMarcaInfo->email;
                    $colegioDeLaPersona = $getUserMarcaInfo->colegio;
                    $tipoBecaDeLaPersona = $getUserMarcaInfo->tipoBeca;


                    if ($getUserMarcaInfo) {
                        if ($getUserMarcaInfo->status == 1) {
                            $datos = DB::table('attendances')
                                ->where('date', '=', Carbon::now()->format('Y-m-d'))
                                ->where('worker_id', '=', $qrId)
                                ->where('status', '=', 1)
                                ->limit(1) // Solo necesitamos saber si existe uno
                                ->get();
                            //return response()->json(['message' => $datos]);
                            if (count($datos) > 0) {
                                $data = [
                                    'message' => 'ALTO! YA MARCASTE',
                                ];
                                return response()->json($data, 200);
                            }

                            // Veo si le quedan creditos para marcar...
                            else {
                                if ($newsCreditos >= 0) {
                                    // Construyo objeto...
                                    $save = new Attendance();
                                    $save->worker_id = $qrId;
                                    $save->date = $date;
                                    $save->in_location = $location;
                                    $save->in_time = $in_time;
                                    $save->cuantoRestar = $cuantoVoyARestar;
                                    $save->colegio = $colegioDeLaPersona;
                                    $save->tipoBeca = $tipoBecaDeLaPersona;
                                    $save->late_time = $lateTime;
                                    $save->rutaBus = $key;
                                    $save->ruta_id = $ruta_id; //Analizar si va el id de la ruta del estudiante
                                    $save->beca_id = $beca_id;
                                    $save->tarifa_id = $tarifa_id;
                                    $save->colegio_id = $colegio_id;
                                    $save->status = 1;

                                    $createNew = $save->save();

                                    // Saving
                                    if ($createNew) {
                                        $getUserMarcaInfo->creditos = $newsCreditos;
                                        $actualizarDatos = $getUserMarcaInfo->save();

                                        // Registrar transacción de créditos
                                        CreditTransaction::create([
                                            'history_id' => $qrId,
                                            'type' => 'consumo',
                                            'amount' => -$cuantoVoyARestar,
                                            'balance_before' => $getUserMarcaInfo->creditos + $cuantoVoyARestar,
                                            'balance_after' => $newsCreditos,
                                            'description' => 'Consumo por check-in en ruta ' . $key,
                                            'attendance_id' => $save->id,
                                            'ruta_id' => $ruta_id,
                                            'processed_by' => 'system',
                                            'verification_status' => 'verified'
                                        ]);

                                        $data = [
                                            'message' => 'Success!',
                                            'date' => Carbon::parse($date)->format('Y-m-d'),
                                            'time' => Carbon::parse($in_time)->format('H:i:s'),
                                            'location' => $location,
                                            'query' => 'Check-in',
                                            'dat' => count($datos),
                                        ];
                                        return response()->json($data, 200);
                                    } else {
                                        $data = [
                                            'message' => 'Error! Something Went Wrong!',
                                        ];
                                        return response()->json($data, 200);
                                    }
                                } elseif ($chancesParaMarcar > 0) {
                                    // Capturar balance antes de modificar anything
                                    $balanceBeforeChance = $getUserMarcaInfo->creditos;

                                    // Actualizo las chances que le quedan...
                                    $chancesActuales = $getUserMarcaInfo->chancesParaMarcar;
                                    $getUserMarcaInfo->chancesParaMarcar = $chancesActuales - 1;
                                    $actualizarChances = $getUserMarcaInfo->save();
                                    $chancesActualesMail = $getUserMarcaInfo->chancesParaMarcar;

                                    // Veo si tiene chances para marcar...
                                    $save = new Attendance();
                                    $save->worker_id = $qrId;
                                    $save->date = $date;
                                    $save->in_location = $location;
                                    $save->in_time = $in_time;
                                    $save->cuantoRestar = $cuantoVoyARestar;
                                    $save->colegio = $colegioDeLaPersona;
                                    $save->tipoBeca = $tipoBecaDeLaPersona;
                                    $save->late_time = $lateTime;
                                    $save->rutaBus = $key;
                                    $save->ruta_id = $ruta_id;
                                    $save->beca_id = $beca_id;
                                    $save->tarifa_id = $tarifa_id;
                                    $save->uso_chance = 1;
                                    $save->chances_restantes = $chancesActualesMail;
                                    $save->colegio_id = $colegio_id;
                                    $save->status = 1;

                                    $createNew = $save->save();

                                    // Saving
                                    if ($createNew) {
                                        // Calcular balance usando el valor capturado antes
                                        $balanceAfter = $balanceBeforeChance - $cuantoVoyARestar; // Resultado negativo (40 - 400 = -360)

                                        // Actualizar el balance en histories para reflejar la deuda
                                        $getUserMarcaInfo->creditos = $balanceAfter;
                                        $actualizarCreditos = $getUserMarcaInfo->save();

                                        CreditTransaction::create([
                                            'history_id' => $qrId,
                                            'type' => 'chance_debt',
                                            'amount' => -$cuantoVoyARestar, // Monto negativo del costo del viaje
                                            'balance_before' => $balanceBeforeChance, // Créditos que tenía antes (40)
                                            'balance_after' => $balanceAfter, // Balance resultante negativo (-360)
                                            'description' => 'Uso de chance - Sin créditos suficientes. Quedan ' . $chancesActualesMail . ' chances',
                                            'attendance_id' => $save->id,
                                            'ruta_id' => $ruta_id,
                                            'processed_by' => 'system',
                                            'verification_status' => 'verified'
                                        ]);

                                        if ($emailDeLaPersona != '') {
                                            $from = 'contacto@beebuscr.com';
                                            $to = $emailDeLaPersona;
                                            $subject = 'BeeBus - Aviso';
                                            $message = ' - ' . $nombreWithCedula . ' no le quedan creditos y ha utilizado una de las chances. Le quedan: ' . $chancesActualesMail . '.';
                                            $headers = ['Reply-To' => $from];
                                            //mail($to, $subject, $message, $headers);
                                            $correoController = new EmailController();
                                            $resultado = $correoController->enviarCorreoPHPMailer($to, $subject, $message, $headers);
                                        }

                                        $data = [
                                            'message' => 'Success!',
                                            'date' => Carbon::parse($date)->format('Y-m-d'),
                                            'time' => Carbon::parse($in_time)->format('H:i:s'),
                                            'location' => $location,
                                            'query' => 'Check-in',
                                        ];
                                        return response()->json($data, 200);
                                    } else {
                                        $data = [
                                            'message' => 'Error! Something Went Wrong!',
                                        ];
                                        return response()->json($data, 200);
                                    }
                                } else {
                                    $data = [
                                        'message' => 'Error! No tiene creditos disponibles. Tampoco le quedan chances.',
                                    ];
                                    return response()->json($data, 200);
                                }
                            }
                        } elseif ($getUserMarcaInfo->status == 2) {
                            $data = [
                                'message' => 'Error! El usuario esta pendiente de activacion. Contacte al administrador.',
                            ];
                            return response()->json($data, 200);
                        } else {
                            $data = [
                                'message' => 'Error! El usuario esta inactivo.',
                            ];
                            return response()->json($data, 200);
                        }
                    } else {
                        $data = [
                            'message' => 'Error! Something Went Wrong!',
                        ];
                        return response()->json($data, 200);
                    }
                } else {
                    $data = [
                        'message' => 'Error! Wrong Command!',
                    ];
                    return response()->json($data, 200);
                }
            } else {
                $data = [
                    'message' => 'The KEY is Wrong!',
                ];
                return response()->json($data, 200);
            }
        } else {
            $data = [
                'message' => 'Please Setting KEY First!',
            ];
            return response()->json($data, 200);
        }
    }
}
