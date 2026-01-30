<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    private function getRoute()
    {
        return 'settings';
    }
    public function store(Request $request)
    {
        $request->validate([
            'start_time' => 'required',
            'out_time' => 'required',
            'key_app' => 'required|unique:settings,key_app',
            'timezone' => 'required',
            'status' => 'required|in:activo,inactivo',
            'colegio_id' => 'nullable|exists:colegios,id'
        ]);

        $setting = Setting::create($request->all());
        return redirect()->route($this->getRoute())->with('success','Creado correctamente');
  }
}