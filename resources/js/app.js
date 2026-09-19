import './bootstrap';

const header = document.getElementById('site-header');

if (header) {
    const syncHeader = () => {
        header.classList.toggle('is-scrolled', window.scrollY > 8);
    };

    syncHeader();
    window.addEventListener('scroll', () => {
        window.requestAnimationFrame(syncHeader);
    }, { passive: true });
}

document.addEventListener('click', (event) => {
    document.querySelectorAll('details.area-picker[open], details.admin-topbar__user[open]').forEach((picker) => {
        if (!picker.contains(event.target)) {
            picker.removeAttribute('open');
        }
    });
});

const initLiveSearch = (wrap) => {
    const form = wrap.querySelector('form');
    const input = wrap.querySelector('input[type="search"], input[name="q"]');
    const panel = wrap.querySelector('.header-suggest, .admin-suggest');
    const suggestUrl = wrap.dataset.suggestUrl;
    const isAdmin = wrap.classList.contains('admin-search-wrap');
    const ns = isAdmin ? 'admin-suggest' : 'header-suggest';

    if (!form || !input || !panel || !suggestUrl) {
        return;
    }

    let timer;
    let controller;
    let items = [];
    let activeIndex = -1;

    const hide = () => {
        panel.hidden = true;
        panel.innerHTML = '';
        items = [];
        activeIndex = -1;
    };

    const escapeHtml = (value) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');

    const render = (results) => {
        if (!input.value.trim()) {
            hide();
            return;
        }

        if (!results.length) {
            panel.innerHTML = `<p class="${ns}__empty">لا توجد نتائج مطابقة</p>`;
            panel.hidden = false;
            items = [];
            return;
        }

        panel.innerHTML = results.map((result, index) => {
            const thumb = result.image
                ? `<img class="${ns}__thumb" src="${escapeHtml(result.image)}" alt="">`
                : `<span class="${ns}__thumb ${ns}__thumb--icon"><span class="material-symbols-outlined">${escapeHtml(result.icon || 'search')}</span></span>`;

            return `<a class="${ns}__item" data-index="${index}" href="${escapeHtml(result.url)}">
                ${thumb}
                <span class="${ns}__copy">
                    <span class="${ns}__title">${escapeHtml(result.title)}</span>
                    <span class="${ns}__meta">${escapeHtml(result.subtitle)}</span>
                </span>
            </a>`;
        }).join('');

        items = [...panel.querySelectorAll(`.${ns}__item`)];
        activeIndex = -1;
        panel.hidden = false;
        widenShekels(panel);
    };

    const fetchResults = async (query) => {
        controller?.abort();
        controller = new AbortController();

        const params = new URLSearchParams({ q: query });
        if (wrap.dataset.scope) {
            params.set('scope', wrap.dataset.scope);
        }
        if (wrap.dataset.restaurantId) {
            params.set('restaurant_id', wrap.dataset.restaurantId);
        }

        const response = await fetch(`${suggestUrl}?${params}`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            signal: controller.signal,
        });
        const data = await response.json();
        render(data.results || []);
    };

    input.addEventListener('input', () => {
        const query = input.value.trim();
        clearTimeout(timer);

        if (!query) {
            hide();
            return;
        }

        timer = setTimeout(() => {
            fetchResults(query).catch((error) => {
                if (error.name !== 'AbortError') {
                    hide();
                }
            });
        }, 180);
    });

    input.addEventListener('keydown', (event) => {
        if (panel.hidden || !items.length) {
            return;
        }

        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            const delta = event.key === 'ArrowDown' ? 1 : -1;
            activeIndex = (activeIndex + delta + items.length) % items.length;
            items.forEach((item, index) => item.classList.toggle('is-active', index === activeIndex));
        }

        if (event.key === 'Enter' && activeIndex >= 0) {
            event.preventDefault();
            items[activeIndex].click();
        }

        if (event.key === 'Escape') {
            hide();
        }
    });

    form.addEventListener('submit', (event) => {
        if (activeIndex >= 0 && items[activeIndex]) {
            event.preventDefault();
            items[activeIndex].click();
            return;
        }

        if (wrap.dataset.submit === 'suggest') {
            event.preventDefault();
            if (items.length) {
                items[0].click();
            }
        }
    });

    document.addEventListener('click', (event) => {
        if (!wrap.contains(event.target)) {
            hide();
        }
    });
};

document.querySelectorAll('.header-search-wrap, .admin-search-wrap').forEach(initLiveSearch);

const money = (value, digits = 1) => Number(value || 0).toLocaleString('en-US', {
    minimumFractionDigits: digits,
    maximumFractionDigits: digits,
});

const ilsMark = '<span class="ils">₪</span>';

const widenShekels = (root = document.body) => {
    if (!root) {
        return;
    }

    if (root.nodeType === Node.TEXT_NODE) {
        root = root.parentElement;
    }

    if (!root) {
        return;
    }

    const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
        acceptNode(node) {
            if (!node.nodeValue.includes('₪')) {
                return NodeFilter.FILTER_REJECT;
            }

            const parent = node.parentElement;

            if (!parent || parent.closest('.ils, script, style, textarea, noscript, code, pre')) {
                return NodeFilter.FILTER_REJECT;
            }

            return NodeFilter.FILTER_ACCEPT;
        },
    });

    const nodes = [];

    while (walker.nextNode()) {
        nodes.push(walker.currentNode);
    }

    nodes.forEach((node) => {
        const parts = node.nodeValue.split('₪');
        const frag = document.createDocumentFragment();

        parts.forEach((part, index) => {
            if (part) {
                frag.appendChild(document.createTextNode(part));
            }

            if (index < parts.length - 1) {
                const mark = document.createElement('span');
                mark.className = 'ils';
                mark.textContent = '₪';
                frag.appendChild(mark);
            }
        });

        node.parentNode?.replaceChild(frag, node);
    });
};

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

const showToast = (message, type = 'success') => {
    const toast = document.getElementById('app-toast');

    if (!toast || !message) {
        return;
    }

    const text = toast.querySelector('.app-toast__text');
    const icon = toast.querySelector('.app-toast__icon');

    if (text) {
        text.textContent = message;
    }

    if (icon) {
        icon.textContent = type === 'error' ? 'error' : 'check_circle';
    }

    toast.classList.toggle('app-toast--error', type === 'error');
    toast.hidden = false;
    requestAnimationFrame(() => toast.classList.add('is-visible'));
    clearTimeout(showToast.timer);
    showToast.timer = setTimeout(() => {
        toast.classList.remove('is-visible');
        setTimeout(() => {
            toast.hidden = true;
        }, 280);
    }, 3500);
};

const setHidden = (el, hidden) => {
    if (el) {
        el.classList.toggle('hidden', hidden);
    }
};

const applyCart = (cart) => {
    if (!cart) {
        return;
    }

    const count = Number(cart.count || 0);
    const badge = document.querySelector('[data-header-cart-badge]');

    if (badge) {
        badge.hidden = count === 0;
        badge.textContent = count > 9 ? '9+' : String(count);
    }

    document.querySelectorAll('[data-cart-count]').forEach((el) => {
        el.textContent = String(count);
    });

    document.querySelectorAll('[data-cart-total], [data-cart-grand-total]').forEach((el) => {
        el.innerHTML = el.hasAttribute('data-cart-grand-total')
            ? `${money(cart.total, 1)} ${ilsMark}`
            : money(cart.total, 1);
    });

    document.querySelectorAll('[data-cart-subtotal]').forEach((el) => {
        el.innerHTML = `${money(cart.subtotal, el.closest('[data-drawer-summary]') ? 1 : 0)} ${ilsMark}`;
    });

    document.querySelectorAll('[data-cart-points]').forEach((el) => {
        el.textContent = `+ ${cart.points} نقطة ولاء`;
    });

    document.querySelectorAll('[data-cart-vip]').forEach((el) => {
        el.textContent = `مشمول خصم الـ VIP (${cart.discount_percent}%)`;
        el.classList.toggle('hidden', !cart.discount_percent);
    });

    document.querySelectorAll('[data-cart-discount-row]').forEach((el) => {
        el.classList.toggle('hidden', !cart.discount_percent);
    });

    document.querySelectorAll('[data-cart-discount-percent]').forEach((el) => {
        el.textContent = String(cart.discount_percent || 0);
    });

    document.querySelectorAll('[data-cart-discount-amount]').forEach((el) => {
        el.innerHTML = `- ${money(cart.discount_amount, 1)} ${ilsMark}`;
    });

    document.querySelectorAll('[data-cart-delivery]').forEach((el) => {
        const free = Number(cart.delivery_fee) === 0;
        el.innerHTML = free
            ? (el.closest('[data-drawer-summary]') ? `0.0 ${ilsMark} (مجاناً)` : 'مجاناً')
            : `${money(cart.delivery_fee, el.closest('[data-drawer-summary]') ? 1 : 0)} ${ilsMark}`;
        el.classList.toggle('text-secondary', free);
        el.classList.toggle('font-semibold', true);
    });

    setHidden(document.getElementById('mobile-cart-pill'), count === 0);
    setHidden(document.querySelector('[data-desktop-cart-summary]'), count === 0);
    setHidden(document.querySelector('[data-desktop-cart-clear]'), count === 0);
    setHidden(document.querySelector('[data-drawer-summary]'), count === 0);

    const desktopCount = document.querySelector('[data-desktop-cart-count]');
    if (desktopCount) {
        desktopCount.textContent = String(count);
    }

    renderCartLines(cart);
    widenShekels(document.body);
};

const cartLineForms = (id, qty, compact) => {
    const token = csrfToken();
    const minus = Math.max(0, qty - 1);
    const btnClass = compact
        ? 'w-6 h-6 rounded-full flex items-center justify-center text-on-surface'
        : 'w-4 h-4 flex items-center justify-center text-slate-500 text-xs font-bold';
    const plusClass = compact
        ? 'w-6 h-6 rounded-full flex items-center justify-center text-primary'
        : 'w-4 h-4 flex items-center justify-center text-slate-500 text-xs font-bold';
    const wrapClass = compact
        ? 'flex items-center gap-2 bg-surface-container-lowest rounded-full p-1 shadow-xs'
        : 'flex items-center gap-2 bg-surface-container-low rounded-lg px-2 py-0.5 border border-slate-200/50';
    const qtyClass = compact ? 'font-label-md text-[13px] font-bold px-1' : 'text-xs font-semibold px-1';

    return `<form method="POST" action="/cart" class="${wrapClass}">
        <input type="hidden" name="_token" value="${token}">
        <input type="hidden" name="_method" value="PATCH">
        <input type="hidden" name="item_id" value="${id}">
        <button type="submit" name="quantity" value="${minus}" class="${btnClass}">${compact ? '<span class="material-symbols-outlined text-[16px]">remove</span>' : '-'}</button>
        <span class="${qtyClass}">${qty}</span>
        <button type="submit" name="quantity" value="${qty + 1}" class="${plusClass}">${compact ? '<span class="material-symbols-outlined text-[16px]">add</span>' : '+'}</button>
    </form>`;
};

const escapeHtml = (value) => String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;');

const renderCartLines = (cart) => {
    const desktop = document.querySelector('[data-desktop-cart-lines]');
    const drawer = document.querySelector('[data-drawer-lines]');

    if (desktop) {
        desktop.innerHTML = cart.lines?.length
            ? cart.lines.map((line) => `
                <div class="pt-2 first:pt-0 flex flex-col gap-1.5 border-t border-slate-100 first:border-0" data-line-id="${line.id}">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-[13px] font-semibold text-on-surface truncate">${escapeHtml(line.name)}</span>
                        <span class="text-[13px] font-bold text-on-surface shrink-0">${money(line.line_total, 0)} ${ilsMark}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        ${cartLineForms(line.id, line.qty, false)}
                        <span class="text-[11px] text-tertiary flex items-center gap-0.5 font-medium">
                            <span class="material-symbols-outlined text-[13px]">stars</span>+${line.points} نقطة
                        </span>
                    </div>
                </div>
            `).join('')
            : '<p data-cart-empty class="text-sm text-on-surface-variant text-center py-8">سلتك فارغة. أضف طبقاً للبدء.</p>';
    }

    if (drawer) {
        drawer.innerHTML = cart.lines?.length
            ? cart.lines.map((line) => `
                <div class="flex items-center justify-between p-2.5 rounded-lg bg-surface-container-low" data-line-id="${line.id}">
                    <div class="flex-1 min-w-0 pl-2">
                        <h4 class="font-label-md text-[13px] text-on-surface font-bold truncate">${escapeHtml(line.name)}</h4>
                        <span class="font-label-md text-[13px] text-primary font-bold">${money(line.line_total, 0)} ${ilsMark}</span>
                    </div>
                    ${cartLineForms(line.id, line.qty, true)}
                </div>
            `).join('')
            : '<p data-cart-empty class="text-sm text-on-surface-variant text-center py-6">سلتك فارغة. أضف طبقاً للبدء.</p>';
    }
};

const submitCartForm = async (form, submitter) => {
    const action = form.getAttribute('action') || '';

    if (!action || action === '#') {
        showToast('تعذر تحديد الصنف المضاف', 'error');
        return false;
    }

    const body = new FormData(form);

    if (submitter?.name) {
        body.set(submitter.name, submitter.value);
    }

    if (submitter) {
        submitter.disabled = true;
    }

    try {
        const response = await fetch(action, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body,
        });

        const data = await response.json().catch(() => null);

        if (!response.ok) {
            showToast(data?.message || 'تعذر تحديث السلة', 'error');
            return false;
        }

        applyCart(data.cart);
        showToast(data.message || 'تم تحديث السلة');
        return true;
    } catch {
        showToast('تعذر الاتصال بالخادم', 'error');
        return false;
    } finally {
        if (submitter) {
            submitter.disabled = false;
        }
    }
};

const isCartForm = (form) => {
    if (form.dataset.noAjax === 'true') {
        return false;
    }

    if (form.id === 'customize-form' || form.hasAttribute('data-ajax-cart')) {
        return true;
    }

    const action = form.getAttribute('action') || '';

    if (/\/cart\/\d+/.test(action)) {
        return true;
    }

    const spoof = (form.querySelector('input[name="_method"]')?.value || '').toUpperCase();

    return action.includes('/cart') && (spoof === 'PATCH' || spoof === 'DELETE' || Boolean(form.querySelector('[name="item_id"]')));
};

document.addEventListener('submit', async (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement) || !isCartForm(form)) {
        return;
    }

    event.preventDefault();

    const ok = await submitCartForm(form, event.submitter);

    if (ok && form.id === 'customize-form') {
        const modal = document.getElementById('customizeModal');
        if (modal) {
            modal.hidden = true;
            document.body.style.overflow = '';
        }
    }
});

const flashToast = document.getElementById('app-toast');
if (flashToast?.dataset.success) {
    showToast(flashToast.dataset.success);
} else if (flashToast?.dataset.error) {
    showToast(flashToast.dataset.error, 'error');
}

const initRestaurantShow = () => {
    const page = document.querySelector('.page-restaurant-show');

    if (!page) {
        return;
    }

    const mobileRoot = page.querySelector('.restaurant-mobile');
    const desktopRoot = page.querySelector('.restaurant-desktop');
    const cartDrawer = document.getElementById('cartDrawer');
    const customModal = document.getElementById('customizeModal');
    const customForm = document.getElementById('customize-form');
    const qtyInput = document.getElementById('customize-qty');
    const qtyLabel = document.getElementById('modalQuantity');
    const totalLabel = document.getElementById('modalTotalCalculated');
    const titleLabel = document.getElementById('modalDishTitle');
    const descLabel = document.getElementById('modalDishDesc');
    let unitPrice = 0;
    let quantity = 1;

    const isDesktop = () => window.matchMedia('(min-width: 1024px)').matches;

    const setOpen = (el, open) => {
        if (!el) {
            return;
        }

        el.hidden = !open;
        document.body.style.overflow = open ? 'hidden' : '';
    };

    const recalcModal = () => {
        if (qtyInput) {
            qtyInput.value = String(quantity);
        }

        if (qtyLabel) {
            qtyLabel.textContent = String(quantity);
        }

        if (totalLabel) {
            totalLabel.innerHTML = `${Math.round(unitPrice * quantity)} ${ilsMark}`;
        }
    };

    mobileRoot?.querySelectorAll('.category-pill').forEach((pill) => {
        pill.addEventListener('click', () => {
            mobileRoot.querySelectorAll('.category-pill').forEach((btn) => btn.classList.remove('is-active'));
            pill.classList.add('is-active');

            const filter = pill.dataset.filter;
            mobileRoot.querySelectorAll('.dish-item').forEach((item) => {
                const match = filter === 'all' || item.dataset.category === filter || item.classList.contains(filter);
                item.classList.toggle('hidden', !match);
            });
        });
    });

    desktopRoot?.querySelectorAll('.category-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            desktopRoot.querySelectorAll('.category-btn').forEach((item) => item.classList.remove('is-active'));
            btn.classList.add('is-active');

            const target = document.getElementById(`section-${btn.dataset.section}`);
            target?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    const bindSearch = (input, items) => {
        input?.addEventListener('input', () => {
            const term = input.value.trim().toLowerCase();
            items.forEach((item) => {
                const name = (item.dataset.name || '').toLowerCase();
                item.classList.toggle('hidden', Boolean(term) && !name.includes(term));
            });
        });
    };

    bindSearch(mobileRoot?.querySelector('.menu-search'), [...(mobileRoot?.querySelectorAll('.dish-item') || [])]);
    bindSearch(desktopRoot?.querySelector('.menu-search'), [...(desktopRoot?.querySelectorAll('.dish-item') || [])]);

    page.querySelectorAll('[data-open-customizer]').forEach((button) => {
        button.addEventListener('click', () => {
            if (!customForm) {
                return;
            }

            customForm.setAttribute('action', button.dataset.addUrl || '#');
            unitPrice = Number(button.dataset.itemPrice || 0);
            quantity = 1;

            if (titleLabel) {
                titleLabel.textContent = button.dataset.itemName || 'تخصيص الوجبة';
            }

            if (descLabel) {
                descLabel.textContent = button.dataset.itemDesc || 'اختر الكمية والإضافات المفضلة لوجبتك';
            }

            customForm.querySelectorAll('.addon-check').forEach((box) => {
                box.checked = false;
            });

            const notes = document.getElementById('orderNotes');
            if (notes) {
                notes.value = '';
            }

            recalcModal();
            setOpen(customModal, true);
        });
    });

    customModal?.querySelector('.js-qty-minus')?.addEventListener('click', () => {
        quantity = Math.max(1, quantity - 1);
        recalcModal();
    });

    customModal?.querySelector('.js-qty-plus')?.addEventListener('click', () => {
        quantity = Math.min(20, quantity + 1);
        recalcModal();
    });

    customModal?.querySelector('.js-close-customizer')?.addEventListener('click', () => setOpen(customModal, false));
    customModal?.addEventListener('click', (event) => {
        if (event.target === customModal) {
            setOpen(customModal, false);
        }
    });

    page.querySelectorAll('.js-open-cart').forEach((button) => {
        button.addEventListener('click', () => setOpen(cartDrawer, true));
    });

    cartDrawer?.querySelector('.js-close-cart')?.addEventListener('click', () => setOpen(cartDrawer, false));
    cartDrawer?.addEventListener('click', (event) => {
        if (event.target === cartDrawer) {
            setOpen(cartDrawer, false);
        }
    });

    page.querySelectorAll('.share-page').forEach((button) => {
        button.addEventListener('click', async () => {
            const payload = { title: document.title, url: window.location.href };

            try {
                if (navigator.share) {
                    await navigator.share(payload);
                } else {
                    await navigator.clipboard.writeText(payload.url);
                    button.setAttribute('title', 'تم نسخ الرابط');
                }
            } catch {
                // user cancelled share
            }
        });
    });

    page.querySelectorAll('.fav-page').forEach((button) => {
        button.addEventListener('click', () => {
            const icon = button.querySelector('.material-symbols-outlined');
            const active = icon?.textContent?.trim() === 'favorite';

            if (icon) {
                icon.textContent = active ? 'favorite_border' : 'favorite';
                icon.classList.toggle('fill-1', !active);
            }

            button.classList.toggle('text-primary', !active);
        });
    });

    const hash = window.location.hash;
    if (hash.startsWith('#dish-')) {
        const id = hash.slice(6);
        const root = isDesktop() ? desktopRoot : mobileRoot;
        const target = root?.querySelector(`[data-dish-id="${id}"]`) || document.getElementById(`dish-${id}`);
        target?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
};

initRestaurantShow();
widenShekels(document.body);

const adminSidebar = document.getElementById('admin-sidebar');
const adminScrim = document.getElementById('admin-scrim');
const toggleAdminNav = (open) => {
    adminSidebar?.classList.toggle('is-open', open);
    if (adminScrim) {
        adminScrim.hidden = !open;
    }
};
document.getElementById('admin-menu-btn')?.addEventListener('click', () => toggleAdminNav(true));
adminScrim?.addEventListener('click', () => toggleAdminNav(false));

const initAuthCast = () => {
    const cast = document.querySelector('[data-auth-cast]');
    if (!cast) {
        return;
    }

    const buddies = [...cast.querySelectorAll('[data-buddy]')];
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const clamp = (value, min, max) => Math.min(max, Math.max(min, value));
    const COVER_FRAMES = [
        [0, 7],
        [3, 6],
        [1, 5],
        [2, 3],
        [3, 1],
        [0, 0],
    ];
    let shy = false;
    let coverIndex = 0;
    let coverTimer = 0;
    let shyReady = false;

    const applyLookFrame = (buddy, x, y) => {
        const col = Math.round(((Number(x) + 1) / 2) * 7);
        const row = Math.round(((Number(y) + 1) / 2) * 7);
        buddy.style.setProperty('--look-col', String(clamp(col, 0, 7)));
        buddy.style.setProperty('--look-row', String(clamp(row, 0, 7)));
    };

    const applyShyFrame = (col, row) => {
        buddies.forEach((buddy) => {
            buddy.style.setProperty('--shy-col', String(col));
            buddy.style.setProperty('--shy-row', String(row));
        });
    };

    const stopCover = () => {
        window.clearTimeout(coverTimer);
        coverTimer = 0;
    };

    const playCover = () => {
        if (!shyReady) {
            return;
        }

        stopCover();
        cast.classList.add('is-shy');

        if (reduce) {
            coverIndex = COVER_FRAMES.length - 1;
            applyShyFrame(0, 0);
            return;
        }

        const tick = () => {
            if (!shy) {
                return;
            }

            const frame = COVER_FRAMES[Math.min(coverIndex, COVER_FRAMES.length - 1)];
            applyShyFrame(frame[0], frame[1]);

            if (coverIndex < COVER_FRAMES.length - 1) {
                coverIndex += 1;
                coverTimer = window.setTimeout(tick, 55);
            }
        };

        tick();
    };

    const playUncover = () => {
        stopCover();
        const tick = () => {
            const frame = COVER_FRAMES[Math.max(coverIndex, 0)];
            applyShyFrame(frame[0], frame[1]);

            if (coverIndex > 0) {
                coverIndex -= 1;
                coverTimer = window.setTimeout(tick, 40);
                return;
            }

            cast.classList.remove('is-shy');
        };

        if (reduce || coverIndex <= 0) {
            coverIndex = 0;
            applyShyFrame(COVER_FRAMES[0][0], COVER_FRAMES[0][1]);
            cast.classList.remove('is-shy');
            return;
        }

        tick();
    };

    const lookAt = (x, y) => {
        if (reduce || shy) {
            return;
        }

        buddies.forEach((buddy) => {
            const box = buddy.getBoundingClientRect();
            const dx = clamp((x - (box.left + box.width / 2)) / 70, -1, 1);
            const dy = clamp((y - (box.top + box.height / 2)) / 70, -1, 1);
            buddy.style.setProperty('--look-x', dx.toFixed(3));
            buddy.style.setProperty('--look-y', dy.toFixed(3));
            applyLookFrame(buddy, dx, dy);
        });
    };

    const setMood = (mood) => {
        const nextShy = mood === 'shy';
        cast.classList.toggle('is-happy', mood === 'happy');
        cast.classList.toggle('is-worried', mood === 'worried');

        if (nextShy) {
            shy = true;
            playCover();
            return;
        }

        if (shy) {
            shy = false;
            playUncover();
        }

        if (mood === 'happy') {
            buddies.forEach((buddy) => applyLookFrame(buddy, 0, 0.35));
        }

        if (mood === 'worried') {
            buddies.forEach((buddy) => applyLookFrame(buddy, 0, -0.15));
        }
    };

    document.addEventListener('mousemove', (event) => {
        if (event.sourceCapabilities?.firesTouchEvents) {
            return;
        }

        lookAt(event.clientX, event.clientY);
    }, { passive: true });

    document.addEventListener('touchstart', (event) => {
        const point = event.touches[0];
        if (point) {
            lookAt(point.clientX, point.clientY);
        }
    }, { passive: true });

    document.querySelectorAll('.auth-form input, .auth-form textarea, .auth-form select').forEach((field) => {
        field.addEventListener('focus', () => {
            if (field.type === 'password') {
                setMood('shy');
                return;
            }

            setMood(null);
            const box = field.getBoundingClientRect();
            lookAt(box.left + box.width / 2, box.top + box.height / 2);
        });

        field.addEventListener('blur', () => {
            if (field.type === 'password') {
                setMood(null);
            }
        });
    });

    document.querySelectorAll('.auth-submit').forEach((button) => {
        button.addEventListener('mouseenter', () => {
            if (!shy) {
                setMood('happy');
            }
        });
        button.addEventListener('mouseleave', () => {
            if (cast.classList.contains('is-happy')) {
                setMood(null);
            }
        });
        button.addEventListener('click', () => setMood('happy'));
    });

    const toast = document.getElementById('app-toast');
    if (toast?.dataset.error) {
        setMood('worried');
    }

    const decodedSheets = [];
    const loadSheet = (src) => new Promise((resolve) => {
        if (!src) {
            resolve(false);
            return;
        }

        const image = new Image();
        image.onload = () => {
            decodedSheets.push(image);
            if (typeof image.decode === 'function') {
                image.decode().then(() => resolve(true)).catch(() => resolve(true));
                return;
            }

            resolve(true);
        };
        image.onerror = () => resolve(false);
        image.src = src;
    });

    Promise.all(buddies.map((buddy) => loadSheet(buddy.dataset.look))).then((loaded) => {
        if (!loaded.every(Boolean)) {
            return;
        }

        buddies.forEach((buddy) => applyLookFrame(buddy, 0, 0));
        cast.hidden = false;
    });

    Promise.all(buddies.map((buddy) => loadSheet(buddy.dataset.shy))).then((loaded) => {
        shyReady = loaded.every(Boolean);
        if (!shyReady) {
            return;
        }

        applyShyFrame(COVER_FRAMES[0][0], COVER_FRAMES[0][1]);

        const active = document.activeElement;
        if (shy || (active instanceof HTMLInputElement && active.type === 'password' && active.closest('.auth-form'))) {
            shy = true;
            playCover();
        }
    });
};

initAuthCast();

const initPartnerWizard = () => {
    const root = document.querySelector('[data-partner-wizard]');
    if (!root) {
        return;
    }

    const panels = [...root.querySelectorAll('[data-step-panel]')];
    const buttons = [...root.querySelectorAll('[data-step-btn]')];
    const prev = root.querySelector('[data-wizard-prev]');
    const next = root.querySelector('[data-wizard-next]');
    const submit = root.querySelector('[data-wizard-submit]');
    const intro = root.querySelector('[data-wizard-intro]');
    const total = panels.length;
    let step = Number(root.dataset.startStep || 1);

    const panelAt = (index) => panels.find((panel) => Number(panel.dataset.stepPanel) === index);

    const validateStep = (index) => {
        const panel = panelAt(index);
        if (!panel) {
            return true;
        }

        const fields = [...panel.querySelectorAll('input, select, textarea')].filter((field) => {
            if (field.disabled || field.type === 'hidden') {
                return false;
            }

            if (field.type === 'file') {
                return false;
            }

            return true;
        });

        for (const field of fields) {
            if (field.name === 'password_confirmation') {
                const password = root.querySelector('input[name="password"]');
                if (password && field.value !== password.value) {
                    field.setCustomValidity('تأكيد كلمة المرور غير مطابق.');
                } else {
                    field.setCustomValidity('');
                }
            }

            if (!field.checkValidity()) {
                field.reportValidity();
                return false;
            }
        }

        return true;
    };

    const go = (index) => {
        step = Math.min(total, Math.max(1, index));
        root.dataset.current = String(step);

        panels.forEach((panel) => {
            panel.hidden = Number(panel.dataset.stepPanel) !== step;
        });

        buttons.forEach((button) => {
            const value = Number(button.dataset.stepBtn);
            button.classList.toggle('is-current', value === step);
            button.classList.toggle('is-done', value < step);
            if (value === step) {
                button.setAttribute('aria-current', 'step');
            } else {
                button.removeAttribute('aria-current');
            }
        });

        if (prev) {
            prev.hidden = step === 1;
        }

        if (next) {
            next.hidden = step === total;
        }

        if (submit) {
            submit.hidden = step !== total;
        }

        if (intro) {
            intro.hidden = step !== 1;
        }
    };

    const advance = (target) => {
        if (target > step) {
            for (let index = step; index < target; index += 1) {
                if (!validateStep(index)) {
                    go(index);
                    return;
                }
            }
        }

        go(target);
    };

    buttons.forEach((button) => {
        button.addEventListener('click', () => advance(Number(button.dataset.stepBtn)));
    });

    prev?.addEventListener('click', () => go(step - 1));
    next?.addEventListener('click', () => advance(step + 1));

    go(step);
};

initPartnerWizard();

const initNiceSelects = () => {
    const instances = [];

    const closeAll = (except) => {
        instances.forEach(({ root, toggle, menu }) => {
            if (root === except) {
                return;
            }
            root.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
            menu.hidden = true;
            if (menu.parentNode === document.body) {
                root.appendChild(menu);
            }
        });
    };

    document.querySelectorAll('select:not([multiple]):not([data-native]):not(.sg-select__native)').forEach((select) => {
        if (select.closest('.sg-select')) {
            return;
        }

        const boxed = !select.closest('.auth-field') && !select.classList.contains('bg-transparent');
        const root = document.createElement('div');
        root.className = boxed ? 'sg-select sg-select--boxed' : 'sg-select';

        const toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.className = 'sg-select__toggle';
        toggle.setAttribute('aria-haspopup', 'listbox');
        toggle.setAttribute('aria-expanded', 'false');

        const value = document.createElement('span');
        value.className = 'sg-select__value';

        const caret = document.createElement('span');
        caret.className = 'sg-select__caret material-symbols-outlined';
        caret.textContent = 'expand_more';

        toggle.append(value, caret);

        const menu = document.createElement('div');
        menu.className = 'sg-select__menu';
        menu.hidden = true;
        menu.setAttribute('role', 'listbox');

        const syncLabel = () => {
            const current = select.options[select.selectedIndex];
            const placeholder = !current || current.value === '';
            value.textContent = current?.textContent?.trim() || 'اختر';
            value.classList.toggle('is-placeholder', placeholder);
        };

        const placeMenu = () => {
            const box = toggle.getBoundingClientRect();
            const width = Math.max(box.width, 168);
            const rtl = document.documentElement.dir === 'rtl';
            menu.style.minWidth = `${width}px`;
            menu.style.right = 'auto';
            const left = rtl ? box.right - width : box.left;
            menu.style.left = `${Math.max(8, Math.min(left, window.innerWidth - width - 8))}px`;
            const need = Math.min(menu.scrollHeight || 220, 268);
            if (window.innerHeight - box.bottom < need && box.top > need) {
                menu.style.top = 'auto';
                menu.style.bottom = `${window.innerHeight - box.top + 6}px`;
            } else {
                menu.style.bottom = 'auto';
                menu.style.top = `${box.bottom + 6}px`;
            }
        };

        const setOpen = (open) => {
            closeAll(open ? root : null);
            root.classList.toggle('is-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            menu.hidden = !open;
            if (open) {
                document.body.appendChild(menu);
                placeMenu();
                const selected = menu.querySelector('.sg-select__option.is-selected');
                selected?.scrollIntoView({ block: 'nearest' });
            } else if (menu.parentNode === document.body) {
                root.appendChild(menu);
            }
        };

        const paintOptions = () => {
            menu.replaceChildren();
            [...select.options].forEach((option, index) => {
                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'sg-select__option';
                item.setAttribute('role', 'option');
                item.dataset.index = String(index);
                item.textContent = option.textContent?.trim() || '';
                item.disabled = option.disabled;
                item.classList.toggle('is-disabled', option.disabled);
                item.classList.toggle('is-selected', option.selected);
                item.addEventListener('click', () => {
                    if (option.disabled) {
                        return;
                    }
                    select.selectedIndex = index;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    syncLabel();
                    paintOptions();
                    setOpen(false);
                    toggle.focus();
                });
                menu.append(item);
            });
        };

        select.classList.add('sg-select__native');
        select.parentNode.insertBefore(root, select);
        root.append(select, toggle, menu);
        paintOptions();
        syncLabel();
        instances.push({ root, toggle, menu });

        toggle.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            setOpen(menu.hidden);
        });
        select.addEventListener('change', () => {
            paintOptions();
            syncLabel();
        });

        toggle.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowDown' || event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                setOpen(true);
            }
            if (event.key === 'Escape') {
                setOpen(false);
            }
        });
    });

    document.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof Element)) {
            closeAll();
            return;
        }
        if (!target.closest('.sg-select') && !target.closest('.sg-select__menu')) {
            closeAll();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAll();
        }
    });

    window.addEventListener('resize', () => closeAll(), { passive: true });
    window.addEventListener('scroll', () => closeAll(), { passive: true });
};

initNiceSelects();

const initRedeemForm = () => {
    const form = document.querySelector('[data-redeem-form]');
    if (!form) {
        return;
    }

    const balance = Number(form.dataset.balance || 0);
    const nameNode = form.querySelector('[data-redeem-name]');
    const costNode = form.querySelector('[data-redeem-cost]');
    const leftNode = form.querySelector('[data-redeem-left]');
    const submit = form.querySelector('[data-redeem-submit]');
    const warning = form.querySelector('[data-redeem-warning]');

    const selectedCard = () => form.querySelector('.sg-redeem__item input:checked')?.closest('.sg-redeem__item');

    const sync = () => {
        form.querySelectorAll('.sg-redeem__item').forEach((item) => {
            item.classList.toggle('is-selected', item.querySelector('input')?.checked === true);
        });

        const input = form.querySelector('.sg-redeem__item input:checked');
        const cost = Number(input?.dataset.cost || 0);
        const canSubmit = Boolean(input) && balance >= cost;

        if (nameNode) {
            nameNode.textContent = input?.dataset.name || 'لم يُحدد بعد';
        }
        if (costNode) {
            costNode.textContent = String(cost);
        }
        if (leftNode) {
            leftNode.textContent = String(Math.max(0, balance - cost));
        }
        if (submit) {
            submit.disabled = !canSubmit;
        }
        if (warning) {
            warning.hidden = canSubmit;
        }
    };

    form.addEventListener('change', sync);
    form.querySelectorAll('[data-redeem-address]').forEach((button) => {
        button.addEventListener('click', () => {
            const address = form.querySelector('#redeem-address');
            const phone = form.querySelector('#redeem-phone');
            if (address) {
                address.value = button.dataset.details || '';
            }
            if (phone) {
                phone.value = button.dataset.phone || phone.value;
            }
            form.querySelectorAll('[data-redeem-address]').forEach((item) => item.classList.remove('is-active'));
            button.classList.add('is-active');
        });
    });

    selectedCard();
    sync();
};

initRedeemForm();

const initListingFilter = () => {
    const overlay = document.querySelector('[data-filter-overlay]');
    const openBtn = document.querySelector('[data-filter-open]');
    if (!overlay || !openBtn) {
        return;
    }

    document.body.appendChild(overlay);

    const close = () => {
        overlay.hidden = true;
        document.body.classList.remove('sg-filter-lock');
        openBtn.focus();
    };

    const open = () => {
        overlay.hidden = false;
        document.body.classList.add('sg-filter-lock');
    };

    openBtn.addEventListener('click', (event) => {
        event.preventDefault();
        open();
    });

    overlay.addEventListener('click', (event) => {
        if (event.target === overlay) {
            close();
        }
    });

    overlay.querySelectorAll('[data-filter-close]').forEach((button) => {
        button.addEventListener('click', close);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !overlay.hidden) {
            close();
        }
    });
};

initListingFilter();


