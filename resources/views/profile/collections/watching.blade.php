@include('profile.collections.plans', [
    'id' => 'watching',
    'title' => 'Assistindo',
    'items' => $watching,
    'visible' => $user->show_watching_public,
    'icon' => 'play',
    'description' => 'As histórias que estão acontecendo agora.',
])
