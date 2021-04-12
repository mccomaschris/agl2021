<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\Player;
use App\Models\Year;
use Illuminate\Support\Facades\DB;

class PlayerController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  \App\Player  $player
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        $seasons = Player::where('user_id', $user->id)->pluck('id');

        $scores = DB::table('scores')
            ->join('players', 'scores.player_id', '=', 'players.id')
            ->join('users', 'players.user_id', '=', 'users.id')
            ->join('weeks', 'scores.foreign_key', '=', 'weeks.id')
            ->join('years', 'weeks.year_id', '=', 'years.id')
            ->select('weeks.*', 'users.*', 'scores.*', 'years.name as year_name', 'weeks.id as week_id' )
            ->whereIn('scores.player_id', $seasons)
            ->where('absent', 0)
            ->where('scores.gross', '>', 0)
            ->where('scores.score_type', 'weekly_score')
            ->orderBy('weeks.week_date', 'desc')
            ->get();

        $total_scores = $scores->count();
        $gross_avg = $scores->avg('gross');
        $net_avg = $scores->avg('net');
        $low_gross = $scores->min('gross');
        $low_net = $scores->min('net');
        $holes = $scores->count() * 9;
        $eagle = $scores->sum('eagle');
        $birdie = $scores->sum('birdie');
        $par = $scores->sum('par');
        $bogey = $scores->sum('bogey');
        $double_bogey = $scores->sum('double_bogey');

        $individual_championships = DB::table('players')
            ->join('years', 'years.id', '=', 'players.year_id')
            ->where('champion', 1)
            ->where('user_id', $user->id)
            ->orderBy('name', 'desc')
            ->get();

        $teams = Player::where('user_id', $user->id)->pluck('team_id');
        $team_championships = DB::table('teams')
                            ->select('years.name as year_name', 'teams.name as team_name')
                            ->join('years', 'years.id', '=', 'teams.year_id')
                            ->whereIn('teams.id', $teams)
                            ->where('champions', 1)
                            ->orderBy('years.name', 'desc')
                            ->get();

        $weekly_wins = DB::table('scores')
            ->join('weeks', 'scores.foreign_key', '=', 'weeks.id')
            ->join('years', 'weeks.year_id', '=', 'years.id')
            ->whereIn('scores.player_id', $seasons)
            ->where('weekly_winner', 1)
            ->orderBy('weeks.week_date', 'desc')
            ->get();

        return view('players.show', compact('user', 'scores', 'gross_avg', 'net_avg', 'total_scores', 'low_gross', 'low_net',
                        'holes', 'eagle', 'birdie', 'par', 'bogey', 'double_bogey', 'individual_championships', 'team_championships', 'weekly_wins'));

    }
}
