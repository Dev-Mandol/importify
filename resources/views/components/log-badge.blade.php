@props(['level'])

@php
    $map = [
        'info'    => ['class' => 'bg-info text-dark',    'icon' => 'bi-info-circle-fill'],
        'warning' => ['class' => 'bg-warning text-dark', 'icon' => 'bi-exclamation-triangle-fill'],
        'error'   => ['class' => 'bg-danger',             'icon' => 'bi-x-octagon-fill'],
        'debug'   => ['class' => 'bg-secondary',          'icon' => 'bi-bug-fill'],
    ];

    $config = $map[strtolower($level ?? '')] ?? ['class' => 'bg-secondary', 'icon' => 'bi-circle-fill'];
@endphp

<span class="badge {{ $config['class'] }} d-inline-flex align-items-center gap-1 px-2 py-1">
    <i class="bi {{ $config['icon'] }}"></i>
    {{ ucfirst($level ?? 'unknown') }}
</span>
