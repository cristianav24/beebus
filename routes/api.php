<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('attendance/apiSaveAttendance', 'Api\Attendance\ApiAttendanceController@apiSaveAttendance');

Route::post('attendance/apiSaveAttendanceTest', 'Api\Attendance\ApiAttendanceTestController@apiSaveAttendance');

Route::get('/helper/{code}', function ($code) {return App\Helpers\Helper::checkingCode($code);});
Route::get('/helper', function () {return App\Helpers\Helper::getInfo();});
Route::get('/write', function () {return App\Helpers\Helper::write();});

/*
|--------------------------------------------------------------------------
| Public Cascade API (Zona -> Colegio -> Ruta -> Paradero)
|--------------------------------------------------------------------------
*/
Route::get('/zonas', function () {
    return App\Models\Zona::where('estado', 'activo')->orderBy('nombre')->get(['id', 'nombre']);
});
Route::get('/zonas/{zonaId}/colegios', function ($zonaId) {
    return App\Models\Colegio::where('zona_id', $zonaId)->where('estado', 'activo')->orderBy('nombre')->get(['id', 'nombre', 'direccion']);
});
Route::get('/colegios/{colegioId}/rutas', function ($colegioId) {
    return App\Models\Setting::where('colegio_id', $colegioId)->where('status', 'activo')->orderBy('key_app')->get(['id', 'key_app', 'start_time', 'out_time']);
});
Route::get('/rutas/{rutaId}/paraderos', function ($rutaId) {
    return App\Models\Paradero::where('ruta_id', $rutaId)->where('estado', 'activo')->orderBy('orden')->get(['id', 'nombre', 'hora', 'monto', 'es_beca_empresarial', 'orden']);
});
