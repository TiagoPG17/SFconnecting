import './bootstrap';
import Alpine from 'alpinejs';
import sort from '@alpinejs/sort';
import focus from '@alpinejs/focus';

// ── Accesibilidad ────────────────────────────────────────────
Alpine.store('a11y', {
    zoom:      'normal', // 'sm' | 'normal' | 'lg' | 'xl'
    contrast:  false,
    noMotion:  false,
    focusMode: false,

    init() {
        try {
            const saved = JSON.parse(localStorage.getItem('sfcrm_a11y') || '{}');
            if (saved.zoom)      this.zoom      = saved.zoom;
            if (saved.contrast  !== undefined) this.contrast  = saved.contrast;
            if (saved.noMotion  !== undefined) this.noMotion  = saved.noMotion;
            if (saved.focusMode !== undefined) this.focusMode = saved.focusMode;
        } catch {}
        this.apply();
    },

    setZoom(val)      { this.zoom = val;               this._save(); },
    toggleContrast()  { this.contrast  = !this.contrast;  this._save(); },
    toggleMotion()    { this.noMotion  = !this.noMotion;   this._save(); },
    toggleFocus()     { this.focusMode = !this.focusMode;  this._save(); },

    reset() {
        this.zoom = 'normal'; this.contrast = false;
        this.noMotion = false; this.focusMode = false;
        this._save();
    },

    _save() {
        localStorage.setItem('sfcrm_a11y', JSON.stringify({
            zoom: this.zoom, contrast: this.contrast,
            noMotion: this.noMotion, focusMode: this.focusMode,
        }));
        this.apply();
    },

    apply() {
        const el   = document.documentElement;
        const sizes = { sm: '13px', normal: '16px', lg: '18px', xl: '20px' };
        el.style.fontSize = sizes[this.zoom] || '16px';
        el.classList.toggle('a11y-contrast',  this.contrast);
        el.classList.toggle('a11y-no-motion', this.noMotion);
        el.classList.toggle('a11y-focus',     this.focusMode);
    },
});

Alpine.plugin(sort);
Alpine.plugin(focus);

// Toast global store
Alpine.store('toast', {
    items: [],
    add(message, type = 'success', duration = 4000) {
        const id = Date.now();
        this.items.push({ id, message, type });
        setTimeout(() => this.remove(id), duration);
    },
    remove(id) {
        this.items = this.items.filter(t => t.id !== id);
    },
    success(msg)  { this.add(msg, 'success'); },
    error(msg)    { this.add(msg, 'error'); },
    warning(msg)  { this.add(msg, 'warning'); },
});

// API helper global
Alpine.magic('api', () => async (method, url, data = null) => {
    const token = document.querySelector('meta[name="api-token"]')?.content;
    const opts  = {
        method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            ...(token ? { Authorization: `Bearer ${token}` } : {}),
        },
    };
    if (data) opts.body = JSON.stringify(data);
    const res = await fetch(url, opts);
    return res.json();
});

// UI state global (panels, modals, etc.)
Alpine.store('ui', {
    a11yOpen: false,
});

// Input de valores en pesos: formatea con separador de miles mientras se escribe
window.pesoInput = function (initialValue) {
    const fmt = v => String(v).replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    const initRaw = initialValue ? String(initialValue).replace(/\D/g, '') : '';
    return {
        display: initRaw ? fmt(initRaw) : '',
        raw: initRaw,
        onInput(e) {
            const digits = e.target.value.replace(/\D/g, '');
            this.raw = digits;
            this.display = fmt(digits);
        },
        onKeydown(e) {
            const allowed = ['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight', 'Home', 'End'];
            if (allowed.includes(e.key)) return;
            if (!/^\d$/.test(e.key)) e.preventDefault();
        },
        reset() { this.display = ''; this.raw = ''; },
    };
};

window.Alpine = Alpine;
Alpine.start();
