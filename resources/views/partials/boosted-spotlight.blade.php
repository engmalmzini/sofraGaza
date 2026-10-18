@php
    $spotSource = $boostedPlaces ?? $restaurants ?? collect();
    $spotPlaces = collect($spotSource)
        ->filter(fn ($place) => $place instanceof \App\Models\Restaurant && $place->isBoosted())
        ->values()
        ->take(3);
    $spotHero = $spotPlaces->first();
    $spotRest = $spotPlaces->slice(1);
@endphp
@if($spotHero)
    @php
        $heroRating = number_format((float) ($spotHero->avg_rating ?? $spotHero->averageRating()), 1);
        $heroEta = $spotHero->type === 'cafe' ? '20-25 دقيقة' : '25-35 دقيقة';
    @endphp
    <section class="sg-ad-spotlight" aria-label="إعلانات">
        <div class="sg-ad-spotlight__stage{{ $spotRest->isNotEmpty() ? ' has-side' : '' }}">
            <a href="{{ route('restaurants.show', $spotHero) }}" class="sg-ad-hero">
                <img src="{{ $spotHero->coverUrl() }}" alt="" class="sg-ad-hero__img" decoding="async">
                <span class="sg-ad-hero__shade" aria-hidden="true"></span>
                <span class="sg-ad-hero__glow" aria-hidden="true"></span>
                <div class="sg-ad-hero__copy">
                    <em>إعلان مميز</em>
                    <strong>{{ $spotHero->name }}</strong>
                    <p>{{ $spotHero->cuisineLabel() }} • {{ $spotHero->areaLabel() }}</p>
                    <div class="sg-ad-hero__meta">
                        <span>★ {{ $heroRating }}</span>
                        <span>{{ $heroEta }}</span>
                        <span>توصيل سريع</span>
                    </div>
                    <span class="sg-ad-hero__cta">
                        اطلب الآن
                        <span class="material-symbols-outlined">arrow_back</span>
                    </span>
                </div>
            </a>

            @if($spotRest->isNotEmpty())
                <div class="sg-ad-spotlight__side">
                    @foreach($spotRest as $place)
                        <a href="{{ route('restaurants.show', $place) }}" class="sg-ad-side">
                            <img src="{{ $place->coverUrl() }}" alt="" loading="lazy" decoding="async">
                            <div>
                                <em>إعلان</em>
                                <strong>{{ $place->name }}</strong>
                                <span>{{ $place->cuisineLabel() }} • {{ $place->areaLabel() }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endif
