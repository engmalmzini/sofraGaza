<form method="POST" action="{{ $action }}" class="{{ $class ?? 'inline' }}" onsubmit="return confirm(@js($confirm));">
    @csrf
    @method('DELETE')
    @isset($hidden)
        @foreach($hidden as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach
    @endisset
    <button type="submit" class="{{ $buttonClass ?? 'admin-action-btn admin-action-btn--danger admin-action-btn--sm' }}">
        @unless(isset($icon) && $icon === false)
            <span class="material-symbols-outlined">delete</span>
        @endunless
        <span>{{ $label ?? 'حذف' }}</span>
    </button>
</form>
