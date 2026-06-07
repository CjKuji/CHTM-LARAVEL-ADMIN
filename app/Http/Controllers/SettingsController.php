<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(Request $request): View
    {
        return view('settings.index', [
            'activeMenu' => 'settings',
            'notifications' => $request->session()->get('settings.notifications', [
                'checkIns'     => true,
                'checkOuts'    => true,
                'reservations' => true,
                'ratings'      => true,
            ]),
            'appearance'        => $request->session()->get('settings.appearance', ['darkMode' => false]),
            'twoFactorEnabled'  => $request->session()->get('settings.two_factor', true),
            'loginAlertEnabled' => $request->session()->get('settings.login_alert', true),
            'activeTab'         => $request->string('tab', 'notifications')->toString(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $tab = $request->string('tab', 'notifications')->toString();

        if ($tab === 'notifications') {
            $request->session()->put('settings.notifications', [
                'checkIns'     => $request->boolean('checkIns'),
                'checkOuts'    => $request->boolean('checkOuts'),
                'reservations' => $request->boolean('reservations'),
                'ratings'      => $request->boolean('ratings'),
            ]);
        }

        if ($tab === 'appearance') {
            $request->session()->put('settings.appearance', [
                'darkMode' => $request->boolean('darkMode'),
            ]);
        }

        if ($tab === 'admin') {
            $request->session()->put('settings.two_factor', $request->boolean('twoFactorEnabled'));
            $request->session()->put('settings.login_alert', $request->boolean('loginAlertEnabled'));
        }

        // FIXED: Explicitly retain the active tab context parameter within the redirect route
        return redirect()
            ->to(route('settings.index', ['tab' => $tab]))
            ->with('status', 'Settings saved successfully.');
    }
}