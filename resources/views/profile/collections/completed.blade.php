@include('profile.collections.plans', [
    'id' => 'completed',
    'title' => 'Concluídos',
    'items' => $completed,
    'visible' => $user->show_completed_public,
    'icon' => 'check',
    'description' => 'Jornadas completas, memórias que ficam.',
])
