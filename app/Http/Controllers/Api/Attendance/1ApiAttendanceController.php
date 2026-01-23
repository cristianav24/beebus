<?php

namespace App\Http\Controllers\Api\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Setting;
use App\Models\History;

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
        $date = Carbon::now()
            ->timezone($getSetting->timezone)
            ->format('Y-m-d');
        $location = $new['location'];

        $getSettingsSegunKey = Setting::where('key_app', $key)->first();

        if (!empty($key)) {
            if ($key == $getSettingsSegunKey->key_app) {
                // Marca (Check-In)
                if ($q == 'in') {
                    // Get data from request
                    $in_time = new Carbon(
                        Carbon::now()
                            ->timezone($getSetting->timezone)
                            ->format('H:i:s'),
                    );

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
                    // CONTROLES //
                    $cuantoVoyARestar = $getUserMarcaInfo->cuantoRestar;
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
                        $datos = DB::table('attendances')
                            ->where('date', '=', Carbon::now()->format('Y-m-d'))
                            ->where('worker_id', '=', $qrId)
                            ->where('status', '=', 1)
                            ->get();
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
                                $save->status = 1;

                                $createNew = $save->save();

                                // Saving
                                if ($createNew) {
                                    $getUserMarcaInfo->creditos = $newsCreditos;
                                    $actualizarDatos = $getUserMarcaInfo->save();

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
                                $save->cuantoRestar = 'Se utilizó una chance (no quedan creditos), quedan: ' . $chancesActualesMail . ' chances.';
                                $save->colegio = $colegioDeLaPersona;
                                $save->tipoBeca = $tipoBecaDeLaPersona;
                                $save->late_time = $lateTime;
                                $save->rutaBus = $key;

                                $createNew = $save->save();

                                // Saving
                                if ($createNew) {
                                    /* ENVIAR MAIL */
                                    if ($emailDeLaPersona != '') {
                                        $from = 'contacto@beebus.cr';
                                        $to = $emailDeLaPersona;
                                        $subject = 'BeeBus - Aviso';
                                        $message = ' - ' . $nombreWithCedula . ' no le quedan creditos y ha utilizado una de las chances. Le quedan: ' . $chancesActualesMail . '.';
                                        $headers = 'From:' . $from;
                                        mail($to, $subject, $message, $headers);
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
