<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\History;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = auth()->user();

        // Redirect parents to their dashboard
        if ($user->role == 4) {
            return redirect()->route('parent.dashboard');
        }

        // Redirect students to their dashboard
        if ($user->role == 3) {
            return redirect()->route('student.dashboard');
        }

        // Get start time to check late worker
        $getSetting = Setting::find(1);

        // Get all data for summary
        $userCount = User::count();
        $attendaceToday = Attendance::where('date', Carbon::now()->format('Y-m-d'))->count();
        $qrCodeCount = History::count();
        $attendanceLateToday = Attendance::where('date', Carbon::now()->format('Y-m-d'))
            ->where('in_time', '>', $getSetting->start_time)
            ->count();

        return view('home', compact('userCount', 'attendaceToday', 'qrCodeCount', 'attendanceLateToday'));
    }
}
