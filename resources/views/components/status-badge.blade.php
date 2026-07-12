@props(['status'])

@php
    $map = [
        'pending'    => ['label' => 'Pending',    'class' => 'bg-warning text-dark', 'icon' => 'bi-clock'],
        'processing' => ['label' => 'Processing', 'class' => 'bg-info text-dark',    'icon' => 'bi-arrow-repeat'],
        'completed'  => ['label' => 'Completed',  'class' => 'bg-success',            'icon' => 'bi-check-circle-fill'],
        'failed'     => ['label' => 'Failed',     'class' => 'bg-danger',             'icon' => 'bi-x-circle-fill'],
        'imported'   => ['label' => 'Imported',   'class' => 'bg-success',            'icon' => 'bi-check-circle-fill'],
        'skipped'    => ['label' => 'Skipped',    'class' => 'bg-secondary',          'icon' => 'bi-dash-circle'],
    ];

    $config = $map[strtolower($status ?? '')] ?? ['label' => ucfirst($status ?? 'Unknown'), 'class' => 'bg-secondary', 'icon' => 'bi-question-circle'];
@endphp

<span class="badge {{ $config['class'] }} d-inline-flex align-items-center gap-1 px-2 py-1">
    <i class="bi {{ $config['icon'] }}"></i>
    {{ $config['label'] }}
</span>
