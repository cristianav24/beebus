<?php

namespace App\Http\Controllers\Api\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Setting;
use App\Models\History;
use DB;
use Illuminate\Http\Request;
use Response;
use Carbon\Carbon;
use Config;
use File;
use Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

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
        $date = Carbon::now()->timezone($getSetting->timezone)->format('Y-m-d');
        $location = $new['location'];
		
		$getSettingsSegunKey = Setting::where('key_app', $key)->first();

        if (!empty($key)) {
            if ($key == $getSettingsSegunKey->key_app) {

                // Marca (Check-In)
                if ($q == 'in') {

                    // Get data from request
                    $in_time = new Carbon(Carbon::now()->timezone($getSetting->timezone)->format('H:i:s'));

                    // Check if user already check-in
                    $checkAlreadyCheckIn = Attendance::where('worker_id', $qrId)
                        ->where('date', Carbon::now()->timezone($getSetting->timezone)->format('Y-m-d'))
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
                        $lateTime = "00:00:00";
                    } else {
                        $lateTime = $in_time->diff($startHour)->format('%H:%I:%S');
                    }
					
					// TOMO DATOS DE PERSONA QUE ESTÁ MARCANDO ASISTENCIA...
					$getUserMarcaInfo = History::find($qrId);
					// CONTROLES //
					$cuantoVoyARestar = $getUserMarcaInfo->cuantoRestar;
					$newsCreditos = ($getUserMarcaInfo->creditos) - $cuantoVoyARestar;
					$chancesParaMarcar = $getUserMarcaInfo->chancesParaMarcar;
					
					// Tomo datos de la persona que marca...
                    $nombreWithCedula = ""; $nombreWithCedula = $getUserMarcaInfo->name.' ('.$getUserMarcaInfo->cedula.')';
                    $emailDeLaPersona = ""; $emailDeLaPersona = $getUserMarcaInfo->email;
					$colegioDeLaPersona = $getUserMarcaInfo->colegio;
					$tipoBecaDeLaPersona = $getUserMarcaInfo->tipoBeca;
					
					// PHP New //
					$ultimaMarcaDelDiaDeHoy = "";						
					$fechaHoy = Carbon::now()->timezone($getSetting->timezone)->format('Y-m-d');
					
					$resultads = DB::select(DB::raw("SELECT * FROM attendances WHERE worker_id = :worker_id and date = :date ORDER BY id DESC LIMIT 1"), 
					array('worker_id' => $qrId,'date' => $fechaHoy,));
					
					foreach($resultads as $r){
						$ultimaMarcaDelDiaDeHoy = $r->in_time;
					}
					// New //
                    
					// Nunca ha marcado, por ende no controlo nada...
					if($ultimaMarcaDelDiaDeHoy == "")
					{
						if ($getUserMarcaInfo) {
								
								// Veo si le quedan creditos para marcar...
								if($newsCreditos >= 0) 
								{
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
										];
										return response()->json($data, 200);
									} else {			
										$data = [
											'message' => 'Error! Something Went Wrong!',
										];
										return response()->json($data, 200);
									}
									
								} else if($chancesParaMarcar > 0) {
									
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
									$save->cuantoRestar = "Se utilizó una chance (no quedan creditos), quedan: ".$chancesActualesMail." chances.";
									$save->colegio = $colegioDeLaPersona;
									$save ->tipoBeca = $tipoBecaDeLaPersona;
									$save->late_time = $lateTime;
									$save->rutaBus = $key;

									$createNew = $save->save();

									// Saving
									if ($createNew) {
										
										/* ENVIAR MAIL */
										if($emailDeLaPersona != "")
										{
											$from = "contacto@beebus.cr";
											$to = $emailDeLaPersona;
											$subject = "BeeBus - Aviso";
											$message = " - ".$nombreWithCedula." no le quedan creditos y ha utilizado una de las chances. Le quedan: ".$chancesActualesMail.".";
											$headers = "From:" . $from;
											mail($to,$subject,$message, $headers);
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
								
							} else {
								$data = [
									'message' => 'Error! Something Went Wrong!',
								];
								return response()->json($data, 200);
							}
					}
					// SI YA TIENE MARCAS EN EL SISTEMA!
					else 
					{
						// Obtengo diferencia en minutos entre horas (del dia de hoy)
						$UltimaMarca = $ultimaMarcaDelDiaDeHoy; 
						$MarcaDeHoy = $in_time;
						$intervaloEntreFechasEnMinutos = $MarcaDeHoy->diff($UltimaMarca)->format('%I');
						$int_intervaloEntreFechasEnMinutos = (int)$intervaloEntreFechasEnMinutos;
						
						if($int_intervaloEntreFechasEnMinutos >= 15) 
						{
							
							if ($getUserMarcaInfo) {
								
								// Veo si le quedan creditos para marcar...
								if($newsCreditos >= 0) 
								{
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
										];
										return response()->json($data, 200);
									} else {			
										$data = [
											'message' => 'Error! Something Went Wrong!',
										];
										return response()->json($data, 200);
									}
									
								} else if($chancesParaMarcar > 0) {
									
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
									$save->cuantoRestar = "Se utilizó una chance (no quedan creditos), quedan: ".$chancesActualesMail." chances.";
									$save->colegio = $colegioDeLaPersona;
									$save ->tipoBeca = $tipoBecaDeLaPersona;
									$save->late_time = $lateTime;
									$save->rutaBus = $key;

									$createNew = $save->save();

									// Saving
									if ($createNew) {
										
										/* ENVIAR MAIL */
										if($emailDeLaPersona != "")
										{
											$from = "contacto@beebus.cr";
											$to = $emailDeLaPersona;
											$subject = "BeeBus - Aviso";
											$message = " - ".$nombreWithCedula." no le quedan creditos y ha utilizado una de las chances. Le quedan: ".$chancesActualesMail.".";
											$headers = "From:" . $from;
											mail($to,$subject,$message, $headers);
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
								
							} else {
								$data = [
									'message' => 'Error! Something Went Wrong!',
								];
								return response()->json($data, 200);
							}
						} // SI PASARON MAS DE 10 MINUTOS DEJO MARCAR...
						else {
							$data = ['Mensaje' => 'ALTO! YA MARCASTE',];
							return response()->json($data, 200);
						}
					} // CONTROL DE MARCA , SI YA TIENE EN EL SISTEMA ...
					
				} /*checkin*/

				// Marca (Check-Out)
				if ($q == 'out') {
				
					// Get data from request
                    $out_time = new Carbon(Carbon::now()->timezone($getSetting->timezone)->format('H:i:s'));
                    $getOutHour = new Carbon($getSetting->out_time);

                    // Get data in_time from DB
                    // To get data work hour
                    $getInTime = Attendance::where('worker_id', $qrId)
                        ->where('date', Carbon::now()->timezone($getSetting->timezone)->format('Y-m-d'))
                        ->where('out_time', null)
                        ->where('out_location', null)
                        ->first();

                    if (!$getInTime) {
                        $data = [
                            'message' => 'check-in first',
                        ];
                        return response()->json($data, 200);
                    }

					

                    // Get data from request
                    $in_time = new Carbon(Carbon::now()->timezone($getSetting->timezone)->format('H:i:s'));

					// Get data total working hour
                    $getWorkHour = $out_time->diff($in_time)->format('%H:%I:%S');

                    // Get over time
                    if ($in_time->gt($getOutHour) || !$out_time->gt($getOutHour)) {
                        $getOverTime = "00:00:00";
                    } else {
                        $getOverTime = $out_time->diff($getOutHour)->format('%H:%I:%S');
                    }

                    // Early out time
                    if ($in_time->gt($getOutHour)) {
                        $earlyOutTime = "00:00:00";
                    } else {
                        $earlyOutTime = $getOutHour->diff($out_time)->format('%H:%I:%S');
                    }

                    // Check if user already check-in
                    $checkAlreadyCheckIn = Attendance::where('worker_id', $qrId)
                        ->where('date', Carbon::now()->timezone($getSetting->timezone)->format('Y-m-d'))
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
                        $lateTime = "00:00:00";
                    } else {
                        $lateTime = $in_time->diff($startHour)->format('%H:%I:%S');
                    }
					
					// TOMO DATOS DE PERSONA QUE ESTÁ MARCANDO ASISTENCIA...
					$getUserMarcaInfo = History::find($qrId);
					// CONTROLES //
					$cuantoVoyARestar = $getUserMarcaInfo->cuantoRestar;
					$newsCreditos = ($getUserMarcaInfo->creditos) - $cuantoVoyARestar;
					$chancesParaMarcar = $getUserMarcaInfo->chancesParaMarcar;
					
					// Tomo datos de la persona que marca...
                    $nombreWithCedula = ""; $nombreWithCedula = $getUserMarcaInfo->name.' ('.$getUserMarcaInfo->cedula.')';
                    $emailDeLaPersona = ""; $emailDeLaPersona = $getUserMarcaInfo->email;
					$colegioDeLaPersona = $getUserMarcaInfo->colegio;
					$tipoBecaDeLaPersona = $getUserMarcaInfo->tipoBeca;
                    
						if ($getUserMarcaInfo) {
								
								// Veo si le quedan creditos para marcar...
								if($newsCreditos >= 0) 
								{
									// Construyo objeto... SALIDA
									
									// Update the data
									$getInTime->out_time = $out_time;
									$getInTime->over_time = $getOverTime;
									$getInTime->work_hour = $getWorkHour;
									$getInTime->early_out_time = $earlyOutTime;
									$getInTime->out_location = $location;
									$getInTime->cuantoRestar = $cuantoVoyARestar;
									$getInTime->colegio = $colegioDeLaPersona;
									$getInTime->tipoBeca = $tipoBecaDeLaPersona;
									$getInTime->late_time = $lateTime;
									$getInTime->rutaBus = $key;
									
									$updateData = $getInTime->save();

									// Saving
									if ($updateData) {
										
										$getUserMarcaInfo->creditos = $newsCreditos;
										$actualizarDatos = $getUserMarcaInfo->save();
										
										$data = [
											'message' => 'Success!',
											'date' => Carbon::parse($date)->format('Y-m-d'),
											'time' => Carbon::parse($in_time)->format('H:i:s'),
											'location' => $location,
											'query' => 'Check-out',
										];
										return response()->json($data, 200);
									} else {			
										$data = [
											'message' => 'Error! Something Went Wrong!',
										];
										return response()->json($data, 200);
									}
									
								} else if($chancesParaMarcar > 0) {
									
									// Actualizo las chances que le quedan...
									$chancesActuales = $getUserMarcaInfo->chancesParaMarcar;
									$getUserMarcaInfo->chancesParaMarcar = $chancesActuales - 1;
									$actualizarChances = $getUserMarcaInfo->save();
									$chancesActualesMail = $getUserMarcaInfo->chancesParaMarcar;
									
									// Construyo objeto... SALIDA
									
									// Update the data
									$getInTime->out_time = $out_time;
									$getInTime->over_time = $getOverTime;
									$getInTime->work_hour = $getWorkHour;
									$getInTime->early_out_time = $earlyOutTime;
									$getInTime->out_location = $location;
									$getInTime->cuantoRestar = "Se utilizó una chance (no quedan creditos), quedan: ".$chancesActualesMail." chances.";
									$getInTime->colegio = $colegioDeLaPersona;
									$getInTime->tipoBeca = $tipoBecaDeLaPersona;
									$getInTime->late_time = $lateTime;
									$getInTime->rutaBus = $key;
									
									$updateData = $getInTime->save();

									// Saving
									if ($updateData) {
										
										/* ENVIAR MAIL */
										if($emailDeLaPersona != "")
										{
											$from = "contacto@beebus.cr";
											$to = $emailDeLaPersona;
											$subject = "BeeBus - Aviso";
											$message = " - ".$nombreWithCedula." no le quedan creditos y ha utilizado una de las chances. Le quedan: ".$chancesActualesMail.".";
											$headers = "From:" . $from;
											mail($to,$subject,$message, $headers);
										}
										
										$data = [
											'message' => 'Success!',
											'date' => Carbon::parse($date)->format('Y-m-d'),
											'time' => Carbon::parse($in_time)->format('H:i:s'),
											'location' => $location,
											'query' => 'Check-out',
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
			} else  {
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
} /* CLASS */