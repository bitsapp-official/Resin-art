<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Auth::user()->settings ?? UserSetting::create(['user_id' => Auth::id()]);
        return view('account.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = Auth::user()->settings ?? UserSetting::create(['user_id' => Auth::id()]);

        $settings->update([
            'order_updates_email' => $request->boolean('order_updates_email'),
            'promotional_email' => $request->boolean('promotional_email'),
            'sms_notifications' => $request->boolean('sms_notifications'),
        ]);

        return back()->with('success', 'Account preferences updated.');
    }
}
