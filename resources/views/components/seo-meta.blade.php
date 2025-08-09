@props([
    'title' => 'Lose Your Weight - Der kostenlose Kalorienzähler für die Schweiz',
    'description' => 'Erreiche dein Wunschgewicht mit dem einfachen und komplett kostenlosen Kalorienzähler für die Schweiz. Kein Abo, keine versteckten Kosten.',
    'image' => url('/social-preview.jpg'), // Platzhalter-Bild, das du erstellen solltest
])

<meta name="description" content="{{ $description }}">
<meta name="keywords" content="kalorienzähler kostenlos schweiz, abnehmen app gratis, kalorien tracker ohne kosten">

<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:image" content="{{ $image }}">

<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="{{ url()->current() }}">
<meta property="twitter:title" content="{{ $title }}">
<meta property="twitter:description" content="{{ $description }}">
<meta property="twitter:image" content="{{ $image }}">