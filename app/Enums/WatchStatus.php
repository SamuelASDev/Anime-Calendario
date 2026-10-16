<?php

namespace App\Enums;

enum WatchStatus: string
{
    case Planned = 'pretendo_assistir';
    case Watching = 'assistindo';
    case Paused = 'pausado';
    case Completed = 'concluido';

    public function label(): string
    {
        return match ($this) {
            self::Planned => 'Pretendo assistir',
            self::Watching => 'Assistindo',
            self::Paused => 'Pausado',
            self::Completed => 'Concluído',
        };
    }
}
