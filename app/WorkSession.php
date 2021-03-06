<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Auth;
use Carbon\Carbon;

class WorkSession extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'start_time',
        'end_time',
        'total_hours',
        'user_id',
        'project_id'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Returns an active session for a given user if one exists; otherwise returns false
     */
    public static function active($user_id = null)
    {
        $user_id = $user_id ?: Auth::user()->id;
        $work_session = WorkSession::where(['end_time' => null, 'user_id' => $user_id])->first();

        return $work_session ?: false;
    }

    public static function completed($limit = null, $user_id = null)
    {
        $user_id = $user_id ?: Auth::user()->id;

        return $work_sessions = WorkSession::where(['user_id' => $user_id])
                    ->whereNotNull('end_time')
                    ->limit($limit)
                    ->orderBy('end_time', 'desc')
                    ->get();
    }

    public static function summary(Project $project = null, $start = null, $end = null)
    {
        $now = new Carbon();
        $starting_point = $start ?: $now->startOfWeek();
        $end_point = $end ?: new Carbon();

        if ( $project ) {
            return $sessions = \DB::table('work_sessions')
                ->join('projects', 'work_sessions.project_id', '=', 'projects.id')
                ->select(\DB::raw('SUM(work_sessions.total_hours) as total_time, projects.name'))
                ->where([
                    ['work_sessions.user_id', Auth::user()->id],
                    ['work_sessions.project_id', $project->id],
                    ['work_sessions.start_time', '>=', $starting_point],
                    ['work_sessions.end_time', '<=', $end_point],
                    ['work_sessions.deleted_at', null]
                ])->first();
        } else {
            return $sessions = \DB::table('work_sessions')
                ->join('projects', 'work_sessions.project_id', '=', 'projects.id')
                ->select(\DB::raw('SUM(work_sessions.total_hours) as total_time, projects.name'))
                ->where([
                    ['work_sessions.user_id', Auth::user()->id],
                    ['work_sessions.start_time', '>=', $starting_point],
                    ['work_sessions.end_time', '<=', $end_point],
                    ['work_sessions.deleted_at', null]
                ])->groupBy('work_sessions.project_id')->get();
        }
    }

    public function project()
    {
    	return $this->belongsTo('App\Project');
    }

    public function user()
    {
    	return $this->belongsTo('App\User');
    }

    public static function getBiMonthlyDate()
    {
        $now = new Carbon();

        if ( $now->day >= 16 ) {
            return $now->day(16)->hour(0)->minute(0)->second(0);
        } else {
            return $now->startOfMonth();
        }
    }

    public function end()
    {
        $start_time_formatted = new Carbon($this->start_time);

        $this->end_time = new Carbon();
        $this->total_hours = $this->calculateTotalHours( $start_time_formatted, $this->end_time );

        $this->save();
    }

    /**
     * Update the model in the database.
     *
     * @param  array  $attributes
     * @param  array  $options
     * @return bool|int
     */
    public function update(array $attributes = [], array $options = [])
    {
        if (! $this->exists) {
            return false;
        }

        $this->adjusted = true;

        return $this->fill($attributes)->save($options);
    }

    private function calculateTotalHours( Carbon $start_time, Carbon $end_time )
    {
        $time_difference = $end_time->diff($start_time);
        $total_hours = $time_difference->h;

        $minutes = max( ($time_difference->i - 4), 0 );

        if ( $minutes > 45 ) {
            $total_hours += 1;
        } else {
            if ( $minutes % 15 != 0 ) {
                $nearest_quarter = $minutes - ($minutes % 15) + 15;
            } else {
                $nearest_quarter = $minutes;
            }
            $total_hours += $nearest_quarter / 60;
        }

        return $total_hours;
    }
}
