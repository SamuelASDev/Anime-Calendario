<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class WatchPlan extends Model
{
    protected $fillable = [
        'user_id',
        'anime_id',
        'episodes_watched',
        'episodes_per_day',
        'start_date',
        'watch_status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function anime()
    {
        return $this->belongsTo(Anime::class);
    }

    public function days()
    {
        return $this->hasMany(WatchPlanDay::class);
    }

    public function logs()
    {
        return $this->hasMany(WatchLog::class);
    }

        public function getCurrentEpisodeCalculatedAttribute(): int
    {
        $this->loadMissing('anime', 'days', 'logs');

        if (!$this->start_date) {
            return (int) $this->episodes_watched;
        }

        $startDate = Carbon::parse($this->start_date)->startOfDay();
        $today = Carbon::today();

        $daysMap = $this->days->keyBy('day_of_week');
        $logsMap = $this->logs->keyBy(function ($log) {
            return Carbon::parse($log->watched_date)->format('Y-m-d');
        });

        $currentEpisode = (int) $this->episodes_watched;
        $date = $startDate->copy();

        while ($date->lt($today)) {
            $dateKey = $date->format('Y-m-d');
            $dayOfWeek = $date->dayOfWeek;

            $dayConfig = $daysMap[$dayOfWeek] ?? null;
            $log = $logsMap[$dateKey] ?? null;

            if ($log) {
                $currentEpisode += (int) $log->episodes_watched_today;
            } elseif ($dayConfig) {
                if (!$dayConfig->is_variable && (int) $dayConfig->episodes_planned > 0) {
                    $currentEpisode += (int) $dayConfig->episodes_planned;
                }
            }

            if (!empty($this->anime?->episodes) && $currentEpisode >= $this->anime->episodes) {
                return (int) $this->anime->episodes;
            }

            $date->addDay();
        }

        return $currentEpisode;
    }
}