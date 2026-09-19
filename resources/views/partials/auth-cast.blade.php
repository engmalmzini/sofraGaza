<div class="auth-cast" data-auth-cast hidden aria-hidden="true">
    <div
        class="auth-buddy auth-buddy--chef"
        data-buddy
        data-look="{{ asset('images/auth/chef-look.png') }}?v={{ @filemtime(public_path('images/auth/chef-look.png')) }}"
        data-shy="{{ asset('images/auth/chef-shy.png') }}?v={{ @filemtime(public_path('images/auth/chef-shy.png')) }}"
    >
        <span class="auth-sprite auth-sprite--look" style="background-image: url('{{ asset('images/auth/chef-look.png') }}?v={{ @filemtime(public_path('images/auth/chef-look.png')) }}')"></span>
        <span class="auth-sprite auth-sprite--shy" style="background-image: url('{{ asset('images/auth/chef-shy.png') }}?v={{ @filemtime(public_path('images/auth/chef-shy.png')) }}')"></span>
    </div>
    <div
        class="auth-buddy auth-buddy--rider"
        data-buddy
        data-look="{{ asset('images/auth/rider-look.png') }}?v={{ @filemtime(public_path('images/auth/rider-look.png')) }}"
        data-shy="{{ asset('images/auth/rider-shy.png') }}?v={{ @filemtime(public_path('images/auth/rider-shy.png')) }}"
    >
        <span class="auth-sprite auth-sprite--look" style="background-image: url('{{ asset('images/auth/rider-look.png') }}?v={{ @filemtime(public_path('images/auth/rider-look.png')) }}')"></span>
        <span class="auth-sprite auth-sprite--shy" style="background-image: url('{{ asset('images/auth/rider-shy.png') }}?v={{ @filemtime(public_path('images/auth/rider-shy.png')) }}')"></span>
    </div>
</div>
