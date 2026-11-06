{{-- Live Order Sound Alert & Real-time Auto-Polling Component --}}
@php
    $role = null;
    $liveEndpoint = null;
    $hasSound = false;
    $desktopNotify = false;
    $serverLatestOrderId = 0;
    $logoPath = public_path('images/logo.png');
    $desktopIconUrl = file_exists($logoPath) ? asset('images/logo.png').'?v='.filemtime($logoPath) : asset('favicon-32.png');
    $desktopBadgeUrl = asset('favicon-32.png');

    if (request()->routeIs('partner.*') && auth()->check() && auth()->user()->isPartner()) {
        $role = 'partner';
        $liveEndpoint = route('partner.orders.live');
        $hasSound = true;
        $desktopNotify = true;
        $serverLatestOrderId = auth()->user()->ownedRestaurant?->orders()->visibleToRestaurant()->max('id') ?? 0;
    } elseif (request()->routeIs('admin.*') && auth()->check() && auth()->user()->isAdmin() && auth()->user()->canAccessAdmin('orders')) {
        $role = 'admin';
        $liveEndpoint = route('admin.orders.live');
        $hasSound = true;
        $desktopNotify = true;
        $serverLatestOrderId = \App\Models\Order::max('id') ?? 0;
    } elseif (request()->routeIs('courier.*') && auth()->check() && auth()->user()->isCourier()) {
        $role = 'courier';
        $liveEndpoint = route('courier.orders.live');
        $hasSound = false; // couriers get subtle chime or update
        $serverLatestOrderId = auth()->user()->deliveries()->max('id') ?? 0;
    }
@endphp

@if($liveEndpoint)
@if($desktopNotify)
<div id="desktop-notify-prompt" class="fixed bottom-4 left-4 right-4 sm:left-auto sm:right-6 sm:w-[26rem] z-50 hidden" role="dialog" aria-labelledby="desktop-notify-title">
    <div class="rounded-2xl bg-white text-stone-900 p-4 shadow-2xl border border-primary/30">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[22px]">notifications_active</span>
            </div>
            <div class="flex-1 min-w-0">
                <strong id="desktop-notify-title" class="block text-sm font-extrabold">تفعيل إشعارات الجهاز</strong>
                <p class="text-xs text-stone-600 mt-1 leading-relaxed">
                    مثل واتساب على كروم: يظهر إشعار على جانب ويندوز عند وصول طلب، والضغط عليه يفتح الطلب ويطبعه على الطابعة.
                </p>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <button type="button" id="desktop-notify-enable" class="admin-btn admin-btn--primary text-xs py-1.5 px-3">
                        تفعيل الإشعارات
                    </button>
                    <button type="button" id="desktop-notify-later" class="admin-btn admin-btn--ghost text-xs py-1.5 px-3">
                        لاحقاً
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
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
                <a id="live-banner-print" href="#" target="_blank" rel="noopener" class="px-3 py-1.5 rounded-lg bg-white text-stone-900 text-xs font-bold transition-colors hidden">
                    طباعة الطلب
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
    const DESKTOP_NOTIFY = @json($desktopNotify);
    const ICON_URL = @json($desktopIconUrl);
    const BADGE_URL = @json($desktopBadgeUrl);
    const SERVER_LATEST_ID = parseInt(@json($serverLatestOrderId), 10) || 0;
    const POLL_INTERVAL = 4000; // 4 seconds
    const promptDismissKey = 'sofra_desktop_notify_dismissed_' + ROLE;

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

    function orderPageUrl(order) {
        const id = order && Number(order.id);
        if (ROLE === 'partner') {
            return id ? `/partner/orders/${id}` : '/partner/orders';
        }
        if (ROLE === 'admin') {
            return id ? `/admin/orders/${id}` : '/admin/orders';
        }
        return id ? `/courier/orders/${id}` : '/courier';
    }

    function orderPrintUrl(order) {
        const id = order && Number(order.id);
        if (!id || order.id === 'تجربة') {
            return '';
        }
        if (ROLE === 'partner') {
            return `/partner/orders/${id}/print`;
        }
        if (ROLE === 'admin') {
            return `/admin/orders/${id}/print`;
        }
        return '';
    }

    function orderNotificationLines(order) {
        if (!order) {
            return [];
        }

        const lines = [];
        const who = [order.customer, order.phone].filter(Boolean).join(' · ');
        if (who) {
            lines.push(who);
        }
        if (order.items) {
            lines.push(order.items);
        } else if (order.items_count) {
            lines.push(order.items_count + ' أصناف');
        }

        const money = [];
        if (ROLE !== 'partner' && order.restaurant) {
            money.push(order.restaurant);
        }
        if (order.area) {
            money.push(order.area);
        }
        if (order.total) {
            money.push(order.total + ' ₪');
        }
        if (order.payment) {
            money.push(order.payment);
        }
        if (money.length) {
            lines.push(money.join(' · '));
        }
        if (order.notes) {
            lines.push('ملاحظة: ' + order.notes);
        }

        return lines;
    }

    function desktopNotificationCopy(order) {
        if (order && order.customer === 'إشعار تجريبي') {
            return {
                title: 'تجربة إشعار سفرة غزة',
                body: 'إذا ظهر هذا على جانب ويندوز فالإشعارات تعمل. اضغط لفتح صفحة الطلبات.',
            };
        }

        const lines = orderNotificationLines(order);
        lines.push(ROLE === 'partner' || ROLE === 'admin' ? 'اضغط للطباعة' : 'اضغط لفتح الطلب');

        if (ROLE === 'partner') {
            return {
                title: order && order.id ? `طلب جديد #${order.id}` : 'طلب جديد مدفوع لمطعمك',
                body: lines.join('\n') || 'وصل طلب جديد بعد تأكيد الدفع. اضغط للطباعة.',
            };
        }

        return {
            title: order && order.id
                ? `طلب #${order.id}${order.restaurant ? ' — ' + order.restaurant : ''}`
                : 'طلب جديد في سفرة غزة',
            body: lines.join('\n') || 'وصل طلب جديد. اضغط للطباعة.',
        };
    }

    function updateDesktopNotifyButtons() {
        const permission = ('Notification' in window) ? Notification.permission : 'denied';
        document.querySelectorAll('.js-desktop-notify-btn').forEach((btn) => {
            const icon = btn.querySelector('.material-symbols-outlined');
            const label = btn.querySelector('.js-desktop-notify-label');
            if (permission === 'granted') {
                if (icon) icon.textContent = 'notifications_active';
                if (label) label.textContent = 'إشعارات الجهاز: مفعّلة';
                btn.setAttribute('title', 'إشعارات ويندوز مفعّلة (انقر للتجربة)');
                btn.classList.remove('opacity-50');
            } else if (permission === 'denied') {
                if (icon) icon.textContent = 'notifications_off';
                if (label) label.textContent = 'إشعارات الجهاز: محظورة';
                btn.setAttribute('title', 'الإشعارات محظورة من المتصفح — اسمح بها من إعدادات كروم');
                btn.classList.add('opacity-50');
            } else {
                if (icon) icon.textContent = 'notification_add';
                if (label) label.textContent = 'تفعيل إشعارات الجهاز';
                btn.setAttribute('title', 'فعّل إشعارات ويندوز للطلبات الجديدة');
                btn.classList.remove('opacity-50');
            }
        });
    }

    function hideDesktopNotifyPrompt() {
        const prompt = document.getElementById('desktop-notify-prompt');
        if (prompt) prompt.classList.add('hidden');
    }

    function showDesktopNotifyPrompt() {
        if (!DESKTOP_NOTIFY || !('Notification' in window)) return;
        if (Notification.permission !== 'default') return;
        if (localStorage.getItem(promptDismissKey) === '1') return;
        const prompt = document.getElementById('desktop-notify-prompt');
        if (prompt) prompt.classList.remove('hidden');
    }

    async function registerOrderServiceWorker() {
        if (!DESKTOP_NOTIFY || !('serviceWorker' in navigator)) return null;
        try {
            return await navigator.serviceWorker.register('/sw.js', {
                scope: '/',
                updateViaCache: 'none',
            });
        } catch (e) {
            return null;
        }
    }

    async function requestDesktopNotifyPermission() {
        if (!('Notification' in window)) {
            if (window.showToast) {
                window.showToast('هذا المتصفح لا يدعم إشعارات الجهاز.');
            }
            return 'denied';
        }

        let permission = Notification.permission;
        if (permission === 'default') {
            permission = await Notification.requestPermission();
        }

        updateDesktopNotifyButtons();

        if (permission === 'granted') {
            hideDesktopNotifyPrompt();
            localStorage.removeItem(promptDismissKey);
            await registerOrderServiceWorker();
            if (window.showToast) {
                window.showToast('تم تفعيل إشعارات الجهاز. ستظهر على جانب ويندوز عند وصول طلب.');
            }
        } else if (permission === 'denied' && window.showToast) {
            window.showToast('الإشعارات محظورة. من إعدادات كروم اسمح بالإشعارات لموقع سفرة غزة.');
        }

        return permission;
    }

    async function showDesktopNotification(order) {
        if (!DESKTOP_NOTIFY || !('Notification' in window) || Notification.permission !== 'granted') {
            return;
        }

        const copy = desktopNotificationCopy(order);
        const url = orderPageUrl(order);
        const printUrl = orderPrintUrl(order);
        const options = {
            body: copy.body,
            icon: ICON_URL,
            badge: BADGE_URL,
            tag: 'sofra-order-' + (order && order.id ? order.id : Date.now()),
            renotify: true,
            requireInteraction: true,
            dir: 'rtl',
            lang: 'ar',
            data: { url: printUrl || url, openUrl: url, printUrl: printUrl, role: ROLE },
            actions: printUrl ? [
                { action: 'print', title: 'طباعة' },
                { action: 'open', title: 'فتح الطلب' },
            ] : [],
        };

        try {
            const registration = await navigator.serviceWorker?.ready;
            if (registration && registration.showNotification) {
                await registration.showNotification(copy.title, options);
                return;
            }
        } catch (e) {}

        try {
            const notification = new Notification(copy.title, options);
            notification.onclick = function () {
                window.focus();
                window.location.href = printUrl || url;
                notification.close();
            };
        } catch (e) {}
    }

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.addEventListener('message', (event) => {
            if (event.data && event.data.type === 'SOFRA_OPEN_URL' && event.data.url) {
                window.location.href = event.data.url;
            }
        });
    }

    registerOrderServiceWorker();
    updateDesktopNotifyButtons();
    setTimeout(showDesktopNotifyPrompt, 1200);

    document.getElementById('desktop-notify-enable')?.addEventListener('click', async (e) => {
        e.preventDefault();
        const permission = await requestDesktopNotifyPermission();
        if (permission === 'granted') {
            await showDesktopNotification({ id: 'تجربة', customer: 'إشعار تجريبي', total: '' });
        }
    });

    document.getElementById('desktop-notify-later')?.addEventListener('click', (e) => {
        e.preventDefault();
        localStorage.setItem(promptDismissKey, '1');
        hideDesktopNotifyPrompt();
    });

    document.querySelectorAll('.js-desktop-notify-btn').forEach((btn) => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const permission = ('Notification' in window) ? Notification.permission : 'denied';
            if (permission === 'granted') {
                await showDesktopNotification({
                    id: 'تجربة',
                    customer: 'إشعار تجريبي',
                    restaurant: 'سفرة غزة',
                    total: '',
                });
                if (window.showToast) {
                    window.showToast('تم إرسال إشعار تجريبي إلى ويندوز.');
                }
                return;
            }
            await requestDesktopNotifyPermission();
        });
    });

    // Show floating banner
    const banner = document.getElementById('live-order-banner');
    const bannerText = document.getElementById('live-banner-text');
    const bannerLink = document.getElementById('live-banner-link');
    const bannerPrint = document.getElementById('live-banner-print');
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
            const details = orderNotificationLines(order).join(' · ');
            bannerText.textContent = details
                ? `طلب #${order.id} — ${details}`
                : `طلب رقم #${order.id} وصل الآن.`;
        }

        if (bannerLink && order) {
            bannerLink.setAttribute('href', orderPageUrl(order));
        }
        if (bannerPrint) {
            const printUrl = orderPrintUrl(order);
            if (printUrl) {
                bannerPrint.setAttribute('href', printUrl);
                bannerPrint.classList.remove('hidden');
            } else {
                bannerPrint.classList.add('hidden');
            }
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

                    // Windows / Chrome desktop toast — click opens the order page
                    showDesktopNotification(data.latest_order);

                    // Show in-page banner
                    showOrderBanner(data.latest_order);
                }

                // Update container HTML if on partner dashboard / orders
                const partnerContainer = document.getElementById('partner-orders-container');
                if (partnerContainer && (data.board_html || data.html) && partnerContainer.querySelector('[data-order-board]')) {
                    if (!document.body.classList.contains('is-dragging-order')) {
                        partnerContainer.innerHTML = data.board_html || data.html;
                    }
                }

                // Update Admin orders tbody if on admin orders index
                const adminTbody = document.getElementById('admin-orders-tbody');
                if (adminTbody && data.html) {
                    adminTbody.innerHTML = data.html;
                }

                const adminBoard = document.getElementById('admin-orders-board');
                if (adminBoard && data.board_html && !document.body.classList.contains('is-dragging-order')) {
                    adminBoard.innerHTML = data.board_html;
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

            // Keep HTML fresh if board signature / counts change
            if (!document.body.classList.contains('is-dragging-order')) {
                const signature = data.board_signature || '';
                const prevSignature = window.__sofraBoardSignature || '';
                if (signature && signature !== prevSignature) {
                    window.__sofraBoardSignature = signature;
                    const partnerContainer = document.getElementById('partner-orders-container');
                    if (partnerContainer && (data.board_html || data.html) && partnerContainer.querySelector('[data-order-board]')) {
                        partnerContainer.innerHTML = data.board_html || data.html;
                    }
                    const adminBoard = document.getElementById('admin-orders-board');
                    if (adminBoard && data.board_html) {
                        adminBoard.innerHTML = data.board_html;
                    }
                } else if (data.html) {
                    const partnerContainer = document.getElementById('partner-orders-container');
                    if (partnerContainer && data.active_count !== undefined && !partnerContainer.querySelector('[data-order-board]')) {
                        const cards = partnerContainer.querySelectorAll('[data-order-card]');
                        if (cards.length !== data.active_count) {
                            partnerContainer.innerHTML = data.html;
                        }
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
