import axios from 'axios';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const csrfMeta = document.querySelector('meta[name="csrf-token"]');
if (csrfMeta) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfMeta.getAttribute('content');
}

// Toast helper
window.orviaToast = function (message, type = 'success') {
    const existing = document.getElementById('orvia-toast');
    if (existing) existing.remove();

    const el = document.createElement('div');
    el.id = 'orvia-toast';
    el.className =
        'fixed right-5 top-5 z-[100] flex items-center gap-2.5 rounded-xl px-4 py-3 text-sm font-semibold shadow-2xl border transition-all duration-300 ' +
        (type === 'error'
            ? 'bg-[#2a1216] border-[#f31415]/40 text-white'
            : 'bg-[#1e1529] border-[#a332f7]/50 text-white');
    el.textContent = message;
    document.body.appendChild(el);

    setTimeout(() => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(-8px)';
        setTimeout(() => el.remove(), 250);
    }, 3000);
};

// Share helper (works for Guest, User, Institution, Admin)
window.orviaShare = async function (url, endpoint, countElId) {
    try {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(url);
        } else {
            const ta = document.createElement('textarea');
            ta.value = url;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            ta.remove();
        }
        window.orviaToast('Tautan berhasil disalin ke clipboard!');

        if (endpoint) {
            const res = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfMeta ? csrfMeta.getAttribute('content') : '',
                    Accept: 'application/json',
                },
            });
            if (res.ok) {
                const data = await res.json();
                if (countElId && typeof data.share_count !== 'undefined') {
                    const el = document.getElementById(countElId);
                    if (el) el.textContent = data.share_count;
                }
            }
        }
    } catch (e) {
        window.orviaToast('Gagal menyalin tautan.', 'error');
    }
};

// Like toggle helper (for Posts & Articles)
window.orviaToggleLike = async function (endpoint, btnEl, countElId) {
    try {
        const res = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfMeta ? csrfMeta.getAttribute('content') : '',
                Accept: 'application/json',
            },
        });
        if (res.status === 401 || res.status === 419) {
            window.location.href = '/login';
            return;
        }
        if (!res.ok) return;
        const data = await res.json();
        const countEl = document.getElementById(countElId);
        if (countEl) countEl.textContent = data.like_count;
        if (btnEl) {
            btnEl.dataset.liked = data.liked ? '1' : '0';
            const iconWrap = btnEl.querySelector('[data-like-circle]');
            if (iconWrap) {
                iconWrap.classList.toggle('bg-[#A332F7]', data.liked);
                iconWrap.classList.toggle('border-[#A332F7]', data.liked);
                iconWrap.classList.toggle('text-white', data.liked);
                iconWrap.classList.toggle('border-white/25', !data.liked);
            }
        }
    } catch (e) {
        console.error(e);
    }
};
