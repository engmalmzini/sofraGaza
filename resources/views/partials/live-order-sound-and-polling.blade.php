{{-- Live Order Sound Alert & Real-time Auto-Polling Component --}}
@php
    $role = null;
    $liveEndpoint = null;
    $hasSound = false;
    $serverLatestOrderId = 0;

    if (request()->routeIs('partner.*') && auth()->check() && auth()->user()->isPartner()) {
        $role = 'partner';
        $liveEndpoint = route('partner.orders.live');
        $hasSound = true;
        $serverLatestOrderId = auth()->user()->ownedRestaurant?->orders()->max('id') ?? 0;
    } elseif (request()->routeIs('admin.*') && auth()->check() && auth()->user()->isAdmin()) {
        $role = 'admin';
        $liveEndpoint = route('admin.orders.live');
        $hasSound = true;
        $serverLatestOrderId = \App\Models\Order::max('id') ?? 0;
    } elseif (request()->routeIs('courier.*') && auth()->check() && auth()->user()->isCourier()) {
        $role = 'courier';
        $liveEndpoint = route('courier.orders.live');
        $hasSound = false; // couriers get subtle chime or update
        $serverLatestOrderId = auth()->user()->deliveries()->max('id') ?? 0;
    }
@endphp

@if($liveEndpoint)
<div id="live-order-banner" class="fixed top-4 left-4 right-4 sm:left-auto sm:right-6 sm:w-96 z-50 transform -translate-y-28 opacity-0 transition-all duration-300 pointer-events-none" style="display: none;">
    <div class="rounded-2xl bg-stone-900/95 text-white p-4 shadow-2xl border-2 border-primary backdrop-blur-md pointer-events-auto flex items-start gap-3">
        <div class="w-10 h-10 rounded-full bg-primary/20 text-primary flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-[24px]">notifications_active</span>
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between">
                <strong class="font-bold text-sm text-primary flex items-center gap-1">
                    <span>🚨 طلب جديد وصل الآن!</span>
                </strong>
                <button type="button" id="live-banner-close" class="text-stone-400 hover:text-white text-xs p-1" title="إغلاق">
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>
            </div>
            <p id="live-banner-text" class="text-xs text-stone-200 mt-1 leading-relaxed">وصل طلب جديد في النظام.</p>
            <div class="mt-2.5 flex items-center gap-2">
                <a id="live-banner-link" href="#" class="px-3 py-1.5 rounded-lg bg-primary hover:bg-primary-container text-on-primary text-xs font-bold transition-colors">
                    عرض الطلب
                </a>
                <button type="button" id="live-banner-mute" class="px-2.5 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs text-stone-300 transition-colors">
                    إغلاق
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    const ROLE = @json($role);
    const LIVE_URL = @json($liveEndpoint);
    const HAS_SOUND = @json($hasSound);
    const SERVER_LATEST_ID = parseInt(@json($serverLatestOrderId), 10) || 0;
    const POLL_INTERVAL = 4000; // 4 seconds

    const storageKey = 'sofra_last_order_id_' + ROLE;
    const notifiedKey = 'sofra_last_notified_order_id_' + ROLE;

    // Highest known order ID
    let storedLastId = parseInt(localStorage.getItem(storageKey) || '0', 10);
    let lastKnownId = Math.max(SERVER_LATEST_ID, storedLastId);

    // Also check page-rendered DOM elements
    const partnerWrap = document.getElementById('partner-orders-wrapper') || document.getElementById('partner-live-orders-section');
    const adminWrap = document.querySelector('[data-admin-orders-table]');
    const courierWrap = document.getElementById('courier-orders-container');

    if (partnerWrap && partnerWrap.dataset.lastId) {
        lastKnownId = Math.max(lastKnownId, parseInt(partnerWrap.dataset.lastId, 10) || 0);
    } else if (adminWrap && adminWrap.dataset.lastId) {
        lastKnownId = Math.max(lastKnownId, parseInt(adminWrap.dataset.lastId, 10) || 0);
    } else if (courierWrap && courierWrap.dataset.lastId) {
        lastKnownId = Math.max(lastKnownId, parseInt(courierWrap.dataset.lastId, 10) || 0);
    }

    // Persist current max known ID immediately so future page loads never send 0
    localStorage.setItem(storageKey, lastKnownId);

    // Initialize notified key to at least the current latest order ID on page load
    // so existing past orders already rendered in the UI never trigger a ring or popup
    const storedNotified = parseInt(localStorage.getItem(notifiedKey) || '0', 10);
    if (!storedNotified || storedNotified < lastKnownId) {
        localStorage.setItem(notifiedKey, lastKnownId);
    }

    let audioCtx = null;
    let isSoundEnabled = localStorage.getItem('order_sound_enabled') !== 'false';
    let originalDocTitle = document.title;
    let titleFlashInterval = null;
    let titleFlashTimeout = null;
    let bannerHideTimeout = null;

    // Audio Context initialization & unlock
    function getAudioContext() {
        if (!audioCtx) {
            const AudioContextClass = window.AudioContext || window.webkitAudioContext;
            if (AudioContextClass) {
                audioCtx = new AudioContextClass();
            }
        }
        if (audioCtx && audioCtx.state === 'suspended') {
            audioCtx.resume().catch(() => {});
        }
        return audioCtx;
    }

    // Unlocks browser audio policy on user interaction
    function unlockAudio() {
        getAudioContext();
    }
    document.addEventListener('click', unlockAudio, { once: false });
    document.addEventListener('touchstart', unlockAudio, { once: false });
    document.addEventListener('keydown', unlockAudio, { once: false });

    // Synthesized POS / Restaurant order alert chime - plays ONCE
    function playOrderChime() {
        if (!isSoundEnabled) return;

        try {
            const ctx = getAudioContext();
            if (!ctx) return;

            const now = ctx.currentTime;

            function tone(freq, start, dur, vol = 0.35) {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();

                osc.type = 'sine';
                osc.frequency.setValueAtTime(freq, start);

                gain.gain.setValueAtTime(vol, start);
                gain.gain.exponentialRampToValueAtTime(0.0001, start + dur);

                osc.connect(gain);
                gain.connect(ctx.destination);

                osc.start(start);
                osc.stop(start + dur);
            }

            // High double ding-dong chime (plays once)
            // Cycle 1
            tone(784.0, now, 0.20, 0.35);         // G5
            tone(1046.5, now + 0.12, 0.22, 0.35);  // C6
            tone(1318.5, now + 0.25, 0.35, 0.4);   // E6
            tone(1568.0, now + 0.40, 0.55, 0.45);  // G6

            // Cycle 2
            tone(784.0, now + 0.70, 0.20, 0.35);
            tone(1046.5, now + 0.82, 0.22, 0.35);
            tone(1318.5, now + 0.95, 0.35, 0.4);
            tone(1568.0, now + 1.10, 0.65, 0.45);
        } catch (e) {
            console.warn('Web Audio error:', e);
        }
    }

    function flashTitleAlert() {
        stopTitleAlert();
        let flash = false;
        titleFlashInterval = setInterval(() => {
            document.title = flash ? '🚨 (طلب جديد!) سفرة غزة' : originalDocTitle;
            flash = !flash;
        }, 1000);

        titleFlashTimeout = setTimeout(stopTitleAlert, 10000);
    }

    function stopTitleAlert() {
        if (titleFlashInterval) {
            clearInterval(titleFlashInterval);
            titleFlashInterval = null;
        }
        if (titleFlashTimeout) {
            clearTimeout(titleFlashTimeout);
            titleFlashTimeout = null;
        }
        document.title = originalDocTitle;
    }

    window.addEventListener('focus', stopTitleAlert);

    // Show floating banner
    const banner = document.getElementById('live-order-banner');
    const bannerText = document.getElementById('live-banner-text');
    const bannerLink = document.getElementById('live-banner-link');
    const bannerClose = document.getElementById('live-banner-close');
    const bannerMute = document.getElementById('live-banner-mute');

    function showOrderBanner(order) {
        if (!banner) return;
        if (bannerHideTimeout) {
            clearTimeout(bannerHideTimeout);
            bannerHideTimeout = null;
        }

        banner.style.display = 'block';
        requestAnimationFrame(() => {
            banner.classList.remove('-translate-y-28', 'opacity-0');
            banner.classList.add('translate-y-0', 'opacity-100');
        });

        if (bannerText && order) {
            const customer = order.customer ? `من الزبون: ${order.customer}` : '';
            const total = order.total ? `بقيمة ${order.total} ₪` : '';
            const rest = order.restaurant ? `بمطعم: ${order.restaurant}` : '';
            bannerText.textContent = `طلب رقم #${order.id} ${customer} ${rest} ${total}.`;
        }

        if (bannerLink && order) {
            const targetRoute = ROLE === 'partner' 
                ? `/partner/orders/${order.id}` 
                : (ROLE === 'admin' ? `/admin/orders/${order.id}` : `/courier/orders/${order.id}`);
            bannerLink.setAttribute('href', targetRoute);
        }

        // Auto dismiss banner after 8 seconds
        bannerHideTimeout = setTimeout(hideOrderBanner, 8000);
    }

    function hideOrderBanner() {
        if (!banner) return;
        if (bannerHideTimeout) {
            clearTimeout(bannerHideTimeout);
            bannerHideTimeout = null;
        }
        banner.classList.remove('translate-y-0', 'opacity-100');
        banner.classList.add('-translate-y-28', 'opacity-0');
        setTimeout(() => {
            banner.style.display = 'none';
        }, 300);
        stopTitleAlert();
    }

    bannerClose?.addEventListener('click', hideOrderBanner);
    bannerMute?.addEventListener('click', hideOrderBanner);

    banner?.addEventListener('mouseenter', () => {
        if (bannerHideTimeout) {
            clearTimeout(bannerHideTimeout);
            bannerHideTimeout = null;
        }
    });

    banner?.addEventListener('mouseleave', () => {
        if (!bannerHideTimeout && banner.style.display !== 'none') {
            bannerHideTimeout = setTimeout(hideOrderBanner, 3000);
        }
    });

    // Toggle/Test sound buttons
    function updateSoundButtonsUI() {
        document.querySelectorAll('#btn-toggle-sound, .js-order-sound-btn').forEach(btn => {
            const icon = btn.querySelector('.material-symbols-outlined');
            const label = btn.querySelector('#sound-status-label');
            const isTopbarBtn = btn.classList.contains('admin-topbar__icon');

            if (isSoundEnabled) {
                if (icon) {
                    icon.textContent = 'volume_up';
                    if (!isTopbarBtn) {
                        icon.classList.remove('text-stone-400');
                        icon.classList.add('text-primary');
                    } else {
                        icon.classList.remove('text-primary', 'text-stone-400', 'opacity-50');
                    }
                }
                if (label) label.textContent = 'صوت التنبيه: مفعّل';
                btn.setAttribute('title', 'صوت التنبيه: مفعّل (انقر للتجربة)');
            } else {
                if (icon) {
                    icon.textContent = 'volume_off';
                    if (!isTopbarBtn) {
                        icon.classList.remove('text-primary');
                        icon.classList.add('text-stone-400');
                    } else {
                        icon.classList.remove('text-primary');
                        icon.classList.add('opacity-50');
                    }
                }
                if (label) label.textContent = 'صوت التنبيه: مكتوم';
                btn.setAttribute('title', 'صوت التنبيه: مكتوم (انقر للتفعيل)');
            }
        });
    }

    document.querySelectorAll('#btn-toggle-sound, .js-order-sound-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            unlockAudio();
            playOrderChime();
            if (window.showToast) {
                window.showToast('تم تجربة نغمة رنين الطلب بنجاح 🔔');
            }
        });
    });

    updateSoundButtonsUI();

    // Polling function
    let isPolling = false;

    async function pollLiveOrders() {
        if (isPolling) return;
        isPolling = true;

        try {
            const separator = LIVE_URL.includes('?') ? '&' : '?';
            const url = `${LIVE_URL}${separator}last_id=${lastKnownId}&_t=${Date.now()}`;

            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                isPolling = false;
                return;
            }

            const data = await response.json();

            // Check if there is a new order
            if (data.has_new && data.latest_id > lastKnownId) {
                const prevNotified = parseInt(localStorage.getItem(notifiedKey) || '0', 10);
                const isGenuinelyNew = data.latest_id > prevNotified;

                lastKnownId = data.latest_id;
                localStorage.setItem(storageKey, lastKnownId);

                if (isGenuinelyNew) {
                    localStorage.setItem(notifiedKey, data.latest_id);

                    // Sound alert: rings ONCE
                    if (HAS_SOUND) {
                        playOrderChime();
                        flashTitleAlert();
                    }

                    // Show order banner
                    showOrderBanner(data.latest_order);
                }

                // Update container HTML if on partner dashboard / orders
                const partnerContainer = document.getElementById('partner-orders-container');
                if (partnerContainer && data.html) {
                    partnerContainer.innerHTML = data.html;
                }

                // Update Admin orders tbody if on admin orders index
                const adminTbody = document.getElementById('admin-orders-tbody');
                if (adminTbody && data.html) {
                    adminTbody.innerHTML = data.html;
                }

                // Update Courier orders container
                const courierContainer = document.getElementById('courier-orders-container');
                if (courierContainer && data.html) {
                    courierContainer.innerHTML = data.html;
                }

                // Update metric counters
                const partnerCounter = document.getElementById('partner-dash-counter');
                const partnerBadge = document.getElementById('active-orders-counter');
                if (partnerCounter && data.active_count !== undefined) {
                    partnerCounter.textContent = data.active_count;
                }
                if (partnerBadge && data.active_count !== undefined) {
                    partnerBadge.textContent = `${data.active_count} قيد المتابعة`;
                }

                const adminBadge = document.getElementById('admin-pending-orders-badge');
                if (adminBadge && data.pending_count !== undefined) {
                    adminBadge.textContent = data.pending_count;
                }
            } else if (data.latest_id && data.latest_id > lastKnownId) {
                // Keep lastKnownId synced silently
                lastKnownId = data.latest_id;
                localStorage.setItem(storageKey, lastKnownId);
            }

            // Keep HTML fresh if count changes (e.g. status changed from confirmed to delivering)
            if (data.html) {
                const partnerContainer = document.getElementById('partner-orders-container');
                if (partnerContainer && data.active_count !== undefined) {
                    const cards = partnerContainer.querySelectorAll('[data-order-card]');
                    if (cards.length !== data.active_count) {
                        partnerContainer.innerHTML = data.html;
                    }
                }
            }
        } catch (e) {
            console.debug('Live polling connection paused:', e);
        } finally {
            isPolling = false;
        }
    }

    // Start polling loop
    setInterval(pollLiveOrders, POLL_INTERVAL);
    // Initial check after 2s
    setTimeout(pollLiveOrders, 2000);
})();
</script>
@endif
