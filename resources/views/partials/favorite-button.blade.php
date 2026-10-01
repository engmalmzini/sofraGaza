@if($canShop ?? true)
@php
    $type = $type ?? 'restaurant';
    $id = (int) $id;
    $on = $on ?? ($type === 'menu_item'
        ? in_array($id, $favoriteMenuItemIds ?? [], true)
        : in_array($id, $favoriteRestaurantIds ?? [], true));
    $class = $class ?? '';
    $icon = $on ? 'favorite' : 'favorite_border';
    $labelOn = $labelOn ?? 'محفوظ في المفضلة';
    $labelOff = $labelOff ?? 'حفظ في المفضلة';
@endphp
<button
    type="button"
    class="js-fav-toggle {{ $class }}{{ $on ? ' is-on' : '' }}"
    data-fav-type="{{ $type }}"
    data-fav-id="{{ $id }}"
    data-fav-url="{{ route('favorites.toggle') }}"
    data-login-url="{{ route('login') }}"
    aria-pressed="{{ $on ? 'true' : 'false' }}"
    aria-label="{{ $on ? $labelOn : $labelOff }}"
    title="{{ $on ? $labelOn : $labelOff }}"
>
    <span class="material-symbols-outlined{{ $on ? ' fill-1' : '' }}">{{ $icon }}</span>
</button>
@endif
