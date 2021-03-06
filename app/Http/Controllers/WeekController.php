<?php

namespace App\Http\Controllers;

use App\Models\Year;
use App\Models\Week;
use Carbon\Carbon;

class WeekController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Year $year)
    {
        $weeks = Week::with('team_a', 'team_b', 'team_c', 'team_d', 'team_e', 'team_f')
                ->where('year_id', $year->id)
                ->where('week_date', '>', Carbon::yesterday())->get();

        return view('weeks.show', compact('year', 'weeks'));
    }
}
