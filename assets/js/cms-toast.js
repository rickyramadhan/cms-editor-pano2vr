/*
 * cms-toast.js — satu-satunya pintu notifikasi CMS.
 * Toast muncul di atas-tengah layar, punya bar durasi yang berjalan mundur, lalu menghilang sendiri.
 * Warna menandai jenisnya: hijau = berhasil, merah = dihapus / gagal, kuning = perhatian, biru = info.
 *
 *   cmsToast('Tersimpan.')                    jenis ditebak dari kalimatnya (dihapus → merah, gagal → merah, dst.)
 *   cmsToast('Gagal: …', 'error')             jenis ditentukan sendiri: success | delete | error | warn | info
 *   cmsToast.success / .delete / .error / .warn / .info (pesan, opsi)
 *   opsi: { title, duration (ms) }
 *
 * Markup server: elemen dengan atribut data-cms-flash="success|error" dibaca lalu dijadikan toast (dan dihapus dari halaman).
 * Elemen status: atribut data-cms-toast memantau teks elemen itu dan menampilkan hasil akhir (bukan pesan "memproses…") sebagai toast;
 * data-cms-toast="only" juga menyembunyikan teks aslinya di halaman.
 */
(() => {
    'use strict';
    if (window.cmsToast) return;

    const svg = path => '<svg viewBox="0 0 24 24" aria-hidden="true">' + path + '</svg>';
    const ICONS = {
        success: svg('<path d="M5 12.5l4.6 4.6L19 7.6"/>'),
        delete: svg('<path d="M4 7h16"/><path d="M9 7V4.6c0-.3.3-.6.6-.6h4.8c.3 0 .6.3.6.6V7"/><path d="M6.2 7l.8 12.2c.1.5.5.8 1 .8h8c.5 0 .9-.3 1-.8L17.8 7"/><path d="M10 11v5.5M14 11v5.5"/>'),
        error: svg('<path d="M6.5 6.5l11 11M17.5 6.5l-11 11"/>'),
        warn: svg('<path d="M12 4l9 15.5H3z"/><path d="M12 10v4.2"/><path d="M12 17.2v.1"/>'),
        info: svg('<circle cx="12" cy="12" r="9"/><path d="M12 11v5.2"/><path d="M12 7.9v.1"/>')
    };
    const CLOSE = svg('<path d="M6 6l12 12M18 6L6 18"/>');
    const TITLES = {success: 'Berhasil', delete: 'Dihapus', error: 'Gagal', warn: 'Perhatian', info: 'Info'};
    const BASE_MS = {success: 4000, delete: 4000, info: 4000, warn: 6000, error: 8000};
    const MAX_VISIBLE = 4;
    const calm = () => window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let host = null;

    // Tebak jenis dari kalimatnya (dipakai bila pemanggil tidak menyebut jenis).
    const classify = text => {
        const failed = /\b(gagal|error|galat|ditolak|dilarang|tidak valid|tidak dapat|tidak bisa|tidak ditemukan|tidak diizinkan|wajib diisi|kedaluwarsa)\b/i.test(text);
        if (failed) return /\b(berhasil|disimpan|diterapkan|selesai|ditambahkan|diproses)\b/i.test(text) && !/^(gagal|error)\b/i.test(text) ? 'warn' : 'error';
        if (/(,\s*(tetapi|namun)\b|\bperhatian\b|\b(dulu|terlebih dahulu)\b|^(pilih|centang|isi|ubah|tentukan|tunggu|simpan dulu)\b)/i.test(text)) return 'warn';
        if (/\b(dihapus|terhapus|dibuang|dilepas)\b/i.test(text)) return 'delete';
        if (/\b(dibatalkan|dikembalikan)\b|^(urungkan|ulangi)\b/i.test(text)) return 'info';
        return 'success';
    };

    const ensureHost = () => {
        if (host && host.isConnected) return host;
        host = document.createElement('div');
        host.className = 'cms-toasts';
        host.setAttribute('role', 'region');
        host.setAttribute('aria-label', 'Notifikasi');
        (document.body || document.documentElement).appendChild(host);
        return host;
    };

    function cmsToast(message, type, options) {
        const text = String(message ?? '').replace(/\s+/g, ' ').trim();
        if (!text) return null;
        const opts = options || {};
        const kind = type === true ? 'error' : (ICONS[type] ? type : classify(text));
        const root = ensureHost();

        // pesan yang sama masih tampil: cukup mulai ulang bar durasinya (tidak menumpuk)
        const same = Array.from(root.children).find(item => item._text === text && item._kind === kind && !item._leaving);
        if (same) { same._restart(); return same; }

        const duration = Math.round(opts.duration || Math.min(10000, Math.max(BASE_MS[kind], 2600 + text.length * 45)));
        const el = document.createElement('div');
        el.className = 'cms-toast is-' + kind;
        el.setAttribute('role', kind === 'error' ? 'alert' : 'status');
        el.innerHTML = '<span class="cms-toast-icon">' + ICONS[kind] + '</span><div class="cms-toast-body"><strong></strong><span></span></div><button type="button" class="cms-toast-close" aria-label="Tutup notifikasi">' + CLOSE + '</button><i class="cms-toast-bar"></i>';
        el.querySelector('strong').textContent = opts.title || TITLES[kind];
        el.querySelector('.cms-toast-body > span').textContent = text;
        el._text = text; el._kind = kind; el._leaving = false;

        const bar = el.querySelector('.cms-toast-bar');
        let anim = null, timer = 0, paused = false;
        const dismiss = () => {
            if (el._leaving) return;
            el._leaving = true; anim?.cancel(); clearTimeout(timer);
            const siblings = Array.from(root.children).filter(item => item !== el && !item._leaving);
            const tops = siblings.map(item => item.getBoundingClientRect().top);
            const done = () => {
                el.remove();
                if (calm()) return;
                siblings.forEach((item, index) => {   // toast di bawahnya meluncur mengisi tempat yang kosong
                    const shift = tops[index] - item.getBoundingClientRect().top;
                    if (shift && item.animate) item.animate([{transform: 'translateY(' + shift + 'px)'}, {transform: 'none'}], {duration: 240, easing: 'cubic-bezier(.2,.8,.2,1)'});
                });
            };
            if (el.animate && !calm()) el.animate([{opacity: 1, transform: 'none'}, {opacity: 0, transform: 'translateY(-14px) scale(.97)'}], {duration: 190, easing: 'ease-in', fill: 'forwards'}).onfinish = done;
            else done();
        };
        const start = () => {
            anim?.cancel(); clearTimeout(timer);
            if (bar.animate) {
                anim = bar.animate([{transform: 'scaleX(1)'}, {transform: 'scaleX(0)'}], {duration, easing: 'linear', fill: 'forwards'});
                anim.onfinish = dismiss;
                if (paused) anim.pause();
            } else if (!paused) timer = setTimeout(dismiss, duration);
        };
        el._restart = () => { start(); };

        // kursor di atas toast (atau fokus di dalamnya): hitung mundur berhenti supaya sempat dibaca
        const pause = () => { paused = true; anim?.pause(); };
        const resume = () => { paused = false; anim?.play(); };
        el.addEventListener('mouseenter', pause); el.addEventListener('mouseleave', resume);
        el.addEventListener('focusin', pause); el.addEventListener('focusout', resume);
        el.querySelector('.cms-toast-close').addEventListener('click', dismiss);

        el._dismiss = dismiss;
        root.appendChild(el);
        const live = Array.from(root.children).filter(item => !item._leaving);
        if (live.length > MAX_VISIBLE) live[0]._dismiss();
        if (el.animate && !calm()) el.animate([{opacity: 0, transform: 'translateY(-20px) scale(.95)'}, {opacity: 1, transform: 'none'}], {duration: 300, easing: 'cubic-bezier(.2,.9,.25,1.12)'});
        start();
        return el;
    }
    ['success', 'delete', 'error', 'warn', 'info'].forEach(kind => { cmsToast[kind] = (message, options) => cmsToast(message, kind, options); });
    cmsToast.classify = classify;
    cmsToast.dismissAll = () => { if (host) Array.from(host.children).forEach(item => item._dismiss?.()); };

    // Pantau elemen status: hasil akhir ditampilkan sebagai toast; pesan "sedang memproses…" dan teks siap-pakai dilewati.
    const IN_PROGRESS = /(…|\.\.\.)\s*$|^(menyimpan|memproses|memuat|membuat|mengunggah|menerjemahkan|menghasilkan|memindai|mengoptimasi|menjalankan|menunggu|sedang)\b|^(siap|centang field)\b/i;
    cmsToast.watch = (element, options) => {
        if (!element || element._cmsWatched) return;
        element._cmsWatched = true;
        const only = options && options.only;
        if (only) element.classList.add('cms-toast-source');
        new MutationObserver(() => {
            const text = element.textContent.replace(/^\s*[✓✔]\s*/, '').trim();
            if (!text || IN_PROGRESS.test(text)) return;
            const forced = element.classList.contains('error') ? 'error' : (element.classList.contains('success') ? 'success' : undefined);
            cmsToast(text, forced);
        }).observe(element, {childList: true, characterData: true, subtree: true});
    };

    const scan = () => {
        document.querySelectorAll('[data-cms-flash]').forEach(node => {
            const kind = node.getAttribute('data-cms-flash'), text = node.textContent;
            node.remove();
            cmsToast(text, kind === 'error' ? 'error' : undefined);
        });
        document.querySelectorAll('[data-cms-toast]').forEach(node => cmsToast.watch(node, {only: node.getAttribute('data-cms-toast') === 'only'}));
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', scan); else scan();
    window.cmsToast = cmsToast;
})();
