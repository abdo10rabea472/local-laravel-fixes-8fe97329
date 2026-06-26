/**
 * Unified AJAX helper for UNI-LAB.
 * - CSRF token auto-injection
 * - JSON request/response
 * - Button loading state (disabled + spinner text)
 * - Toast notifications (success/error/info)
 * - Partial page swap helper (for filters, sort, pagination)
 *
 * Exposes: window.UL = { request, json, swap, toast, withLoading }
 */
(function () {
    'use strict';

    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const CSRF = csrfMeta ? csrfMeta.getAttribute('content') : '';

    // ---------- Toast ----------
    function ensureToast() {
        let host = document.getElementById('ul-toast-host');
        if (!host) {
            host = document.createElement('div');
            host.id = 'ul-toast-host';
            host.style.cssText = 'position:fixed;bottom:24px;left:50%;transform:translateX(-50%);z-index:99999;display:flex;flex-direction:column;gap:8px;align-items:center;pointer-events:none;';
            document.body.appendChild(host);
        }
        return host;
    }

    function toast(message, type = 'info', timeout = 2800) {
        const host = ensureToast();
        const colors = {
            success: '#059669',
            error: '#dc2626',
            info: '#1f2937',
            warning: '#d97706',
        };
        const el = document.createElement('div');
        el.textContent = message;
        el.setAttribute('role', type === 'error' ? 'alert' : 'status');
        el.style.cssText = `background:${colors[type] || colors.info};color:#fff;padding:12px 22px;border-radius:9999px;box-shadow:0 10px 25px -5px rgba(0,0,0,.35);font-weight:600;font-size:14px;max-width:90vw;opacity:0;transform:translateY(8px);transition:opacity .2s,transform .2s;pointer-events:auto;`;
        host.appendChild(el);
        requestAnimationFrame(() => {
            el.style.opacity = '1';
            el.style.transform = 'translateY(0)';
        });
        setTimeout(() => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(8px)';
            setTimeout(() => el.remove(), 250);
        }, timeout);
    }

    // ---------- Button loading state ----------
    function withLoading(btn, fn) {
        if (!btn) return fn();
        const original = btn.innerHTML;
        const wasDisabled = btn.disabled;
        btn.disabled = true;
        btn.dataset.ulLoading = '1';
        btn.innerHTML = '<span class="inline-block w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin align-middle"></span>';
        const restore = () => {
            btn.disabled = wasDisabled;
            btn.innerHTML = original;
            delete btn.dataset.ulLoading;
        };
        const result = fn();
        if (result && typeof result.then === 'function') {
            return result.finally(restore);
        }
        restore();
        return result;
    }

    // ---------- Core fetch ----------
    async function request(url, options = {}) {
        const opts = Object.assign({ method: 'GET', credentials: 'same-origin' }, options);
        opts.headers = Object.assign(
            {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            options.headers || {}
        );
        if (!['GET', 'HEAD'].includes((opts.method || 'GET').toUpperCase())) {
            opts.headers['X-CSRF-TOKEN'] = CSRF;
        }
        if (opts.body && typeof opts.body === 'object' && !(opts.body instanceof FormData)) {
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(opts.body);
        }
        const res = await fetch(url, opts);
        return res;
    }

    async function json(url, options = {}) {
        const res = await request(url, options);
        let data = null;
        try { data = await res.json(); } catch (_) {}
        if (!res.ok) {
            const msg = (data && (data.message || data.error)) || `Request failed (${res.status})`;
            const err = new Error(msg);
            err.status = res.status;
            err.data = data;
            throw err;
        }
        return data;
    }

    // ---------- Partial swap (fetches a full page, replaces a selector's contents) ----------
    async function swap(url, selector, { pushState = true, scrollTo = null } = {}) {
        const target = document.querySelector(selector);
        if (!target) throw new Error(`swap: selector ${selector} not found`);
        target.classList.add('opacity-50', 'pointer-events-none');
        try {
            const res = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const html = await res.text();
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const next = doc.querySelector(selector);
            if (!next) throw new Error(`swap: selector ${selector} missing in response`);
            target.innerHTML = next.innerHTML;
            if (pushState) {
                window.history.pushState({ ulSwap: selector }, '', url);
            }
            // Update document title
            if (doc.title) document.title = doc.title;
            if (scrollTo) {
                const el = typeof scrollTo === 'string' ? document.querySelector(scrollTo) : scrollTo;
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            target.dispatchEvent(new CustomEvent('ul:swapped', { bubbles: true }));
        } catch (e) {
            console.error('swap failed', e);
            toast('تعذّر تحميل النتائج. حاول مجددًا.', 'error');
            throw e;
        } finally {
            target.classList.remove('opacity-50', 'pointer-events-none');
        }
    }

    // Browser back/forward → reload via swap if we own the state
    window.addEventListener('popstate', (e) => {
        const sel = e.state && e.state.ulSwap;
        if (sel && document.querySelector(sel)) {
            swap(window.location.href, sel, { pushState: false }).catch(() => {});
        }
    });

    // ---------- AJAX form submission (handles Laravel 422 validation + redirects) ----------
    function renderFormErrors(form, errors) {
        // Clear previous AJAX errors + invalid styles
        form.querySelectorAll('[data-ul-error]').forEach((el) => el.remove());
        form.querySelectorAll('[data-ul-invalid]').forEach((el) => {
            el.classList.remove('border-red-500', 'ring-1', 'ring-red-500');
            delete el.dataset.ulInvalid;
        });
        Object.entries(errors || {}).forEach(([field, messages]) => {
            const input = form.querySelector(`[name="${field}"], [name="${field}[]"]`);
            if (!input) return;
            const msg = Array.isArray(messages) ? messages[0] : String(messages);
            input.classList.add('border-red-500', 'ring-1', 'ring-red-500');
            input.dataset.ulInvalid = '1';
            const onFocus = () => {
                input.classList.remove('border-red-500', 'ring-1', 'ring-red-500');
                const sib = input.parentElement?.querySelector('[data-ul-error]');
                if (sib) sib.remove();
                input.removeEventListener('focus', onFocus);
            };
            input.addEventListener('focus', onFocus);
            const span = document.createElement('p');
            span.setAttribute('data-ul-error', '1');
            span.className = 'text-sm text-red-600 dark:text-red-400 mt-1';
            span.textContent = msg;
            (input.parentElement || form).appendChild(span);
        });
        // Focus first invalid
        const firstInvalid = form.querySelector('[data-ul-invalid]');
        if (firstInvalid) firstInvalid.focus({ preventScroll: false });
    }

    async function submitForm(form, { onSuccess, onError, successToast } = {}) {
        if (!form) return;
        const submitBtn = form.querySelector('button[type="submit"], [type="submit"]');
        form.querySelectorAll('[data-ul-error]').forEach((el) => el.remove());
        const url = form.action;
        const method = (form.getAttribute('method') || 'POST').toUpperCase();
        const fd = new FormData(form);
        const realMethod = (fd.get('_method') || method).toString().toUpperCase();
        const fetchMethod = ['GET', 'HEAD'].includes(realMethod) ? realMethod : 'POST';

        const exec = async () => {
            try {
                const res = await fetch(url, {
                    method: fetchMethod,
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                    },
                    body: fetchMethod === 'GET' ? undefined : fd,
                });
                if (res.status === 422) {
                    const data = await res.json().catch(() => ({}));
                    renderFormErrors(form, data.errors || {});
                    toast(data.message || 'يرجى مراجعة الحقول.', 'error');
                    form.dispatchEvent(new CustomEvent('ul:error', { bubbles: true, detail: data }));
                    onError && onError(data);
                    return;
                }
                let data = null;
                try { data = await res.json(); } catch (_) {}
                if (res.redirected && !data) {
                    if (successToast) toast(successToast, 'success');
                    form.dispatchEvent(new CustomEvent('ul:success', { bubbles: true, detail: { redirect: res.url } }));
                    if (onSuccess) onSuccess({ redirect: res.url });
                    else window.location.href = res.url;
                    return;
                }
                if (!res.ok) {
                    const msg = (data && (data.message || data.error)) || `Error (${res.status})`;
                    toast(msg, 'error');
                    form.dispatchEvent(new CustomEvent('ul:error', { bubbles: true, detail: data }));
                    onError && onError(data);
                    return;
                }
                if (successToast) toast(successToast, 'success');
                else if (data && data.message) toast(data.message, 'success');
                form.dispatchEvent(new CustomEvent('ul:success', { bubbles: true, detail: data || {} }));
                if (onSuccess) onSuccess(data || {});
                else if (data && data.redirect) window.location.href = data.redirect;
            } catch (e) {
                console.error('submitForm failed', e);
                toast('تعذّر الاتصال بالخادم.', 'error');
                form.dispatchEvent(new CustomEvent('ul:error', { bubbles: true, detail: { message: e.message } }));
                onError && onError({ message: e.message });
            }
        };

        return submitBtn ? withLoading(submitBtn, exec) : exec();
    }

    // Auto-enable: any form with data-ajax attribute
    document.addEventListener('submit', (e) => {
        const form = e.target.closest('form[data-ajax]');
        if (!form) return;
        e.preventDefault();
        const successToast = form.dataset.successToast || null;
        submitForm(form, { successToast });
    });

    window.UL = { request, json, swap, toast, withLoading, submitForm };
})();

