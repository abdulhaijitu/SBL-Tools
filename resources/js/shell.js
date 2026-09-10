export function registerShell(Alpine) {
    Alpine.store('lang', {
        current: (typeof localStorage !== 'undefined' && localStorage.getItem('sbl_lang')) || 'en',
        init() {
            this.apply(this.current);
        },
        toggle() {
            const next = this.current === 'en' ? 'bn' : 'en';
            this.set(next);
        },
        set(lang) {
            this.current = lang;
            if (typeof localStorage !== 'undefined') {
                localStorage.setItem('sbl_lang', lang);
            }
            this.apply(lang);
            window.dispatchEvent(new CustomEvent('lang-changed', { detail: { lang } }));
        },
        apply(lang) {
            document.documentElement.lang = lang;
            document.documentElement.dataset.lang = lang;

            document.querySelectorAll('[data-en][data-bn]').forEach(el => {
                const text = lang === 'bn' ? el.getAttribute('data-bn') : el.getAttribute('data-en');
                if (text) {
                    if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
                        if (el.hasAttribute('placeholder')) el.placeholder = text;
                    } else {
                        el.textContent = text;
                    }
                }
            });

            document.querySelectorAll('[data-lang-content]').forEach(el => {
                if (el.getAttribute('data-lang-content') === lang) {
                    el.style.display = '';
                } else {
                    el.style.display = 'none';
                }
            });
        }
    });

    Alpine.store('currency', {
        code: document.documentElement.dataset.currency || 'USD',
        rate: Number(document.documentElement.dataset.exchangeRate) || 120,
        busy: false,
        symbol() { return this.code === 'BDT' ? 'BDT' : '$'; },
        convert(value) { return (Number(value) || 0) * (this.code === 'BDT' ? this.rate : 1); },
        format(value, includeSymbol = true) {
            const amount = this.convert(value);
            const result = new Intl.NumberFormat(document.documentElement.lang || 'en', {maximumFractionDigits: 2}).format(amount);
            return includeSymbol ? (this.code === 'BDT' ? `${result} BDT` : `$${result}`) : result;
        },
        async set(value) {
            if (this.busy || !['USD', 'BDT'].includes(value)) return;
            this.busy = true;
            try {
                const response = await fetch('/currency/switch', {
                    method: 'POST', credentials: 'same-origin',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
                    body: JSON.stringify({currency: value}),
                });
                if (!response.ok) throw new Error('Could not update currency. Please try again.');
                this.code = (await response.json()).currency;
                window.dispatchEvent(new CustomEvent('currency-changed', {detail: {code: this.code}}));
            } catch (error) {
                window.dispatchEvent(new CustomEvent('notify', {detail: {message: error.message, type: 'error'}}));
            } finally { this.busy = false; }
        },
    });
    Alpine.data('appShell', () => ({
        isMobile: window.innerWidth < 1024,
        init() {
            this.viewportListener = () => { this.isMobile = window.innerWidth < 1024; if (!this.isMobile) this.sidebarOpen = false; };
            window.addEventListener('resize', this.viewportListener);
            this.$nextTick(() => {
                if (Alpine.store('lang')) Alpine.store('lang').init();
            });
        },
        destroy() { window.removeEventListener('resize', this.viewportListener); },
        sidebarOpen: false, quickActionOpen: false, toasts: [],
        addToast(message, type = 'success') {
            const id = Date.now() + Math.random();
            this.toasts.push({id, message, type});
            setTimeout(() => this.removeToast(id), 7000);
        },
        removeToast(id) { this.toasts = this.toasts.filter(toast => toast.id !== id); },
        closeMenus() {
            const wasOpen = this.sidebarOpen;
            this.sidebarOpen = false;
            this.quickActionOpen = false;
            if (wasOpen) this.$refs.menuButton?.focus();
        },
    }));
}
