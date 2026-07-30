<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit()
    {
        $autoReturnTime = Setting::get('auto_return_time', '17:00');

        return view('admin.settings.edit', compact('autoReturnTime'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'auto_return_time' => 'required|date_format:H:i',
        ]);

        Setting::set('auto_return_time', $request->auto_return_time);

        return redirect()->route('admin.settings.edit')
            ->with('success', 'Configuración actualizada exitosamente.');
    }
}
