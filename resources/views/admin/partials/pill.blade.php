@php
    $tone = [
        'pending_confirmation' => 'wait',
        'pending' => 'wait',
        'confirmed' => 'run',
        'preparing' => 'run',
        'delivering' => 'run',
        'delivered' => 'ok',
        'approved' => 'ok',
        'rejected' => 'off',
        'cancelled' => 'off',
    ][$status ?? ''] ?? 'off';
@endphp
<span class="admin-pill admin-pill--{{ $tone }}">{{ $label }}</span>
