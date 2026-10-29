/* =========================================================================
   RESQNET — motion & interaksi bersama
   Dipakai di landing page, halaman masuk/daftar, dan semua halaman aplikasi.
   Tidak memerlukan library tambahan dan tidak perlu "npm run build".
   ========================================================================= */
(function () {
    'use strict';

    var RM = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var FINE = window.matchMedia('(pointer: fine)').matches;
    var $ = function (s, r) { return (r || document).querySelector(s); };
    var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
    var RQ = window.RQ = window.RQ || {};
    RQ.reducedMotion = RM;

    /* ---------- Notifikasi singkat ---------- */
    var toastEl, toastTimer;
    RQ.toast = function (msg) {
        if (!toastEl) {
            toastEl = document.createElement('div');
            toastEl.className = 'rq-toast';
            toastEl.setAttribute('role', 'status');
            document.body.appendChild(toastEl);
        }
        toastEl.textContent = msg;
        toastEl.classList.add('is-on');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { toastEl.classList.remove('is-on'); }, 2600);
    };

    /* ---------- Efek ketik ---------- */
    RQ.sleep = function (ms) { return new Promise(function (r) { setTimeout(r, ms); }); };
    RQ.type = function (el, text, speed, keepCaret) {
        speed = speed || 40;
        var token = el._typeToken = (el._typeToken || 0) + 1;
        el.textContent = '';
        if (el._caret) el._caret.remove();
        var caret = el._caret = document.createElement('i');
        caret.className = 'rq-caret';
        el.after(caret);
        if (RM) {
            el.textContent = text;
            if (!keepCaret) caret.remove();
            return Promise.resolve();
        }
        var i = 0;
        return new Promise(function (resolve) {
            (function step() {
                if (el._typeToken !== token) return resolve();
                if (i >= text.length) {
                    if (!keepCaret) caret.remove();
                    return resolve();
                }
                var s = document.createElement('span');
                s.className = 'rq-ch';
                s.textContent = text[i];
                el.appendChild(s);
                var extra = ',.'.indexOf(text[i]) > -1 ? 170 : 0;
                i++;
                setTimeout(step, speed * (0.6 + Math.random() * 0.8) + extra);
            })();
        });
    };

    /* ---------- Bentuk grafis latar ---------- */
    var K = '#121417';
    var COLORS = { k: K, w: '#ffffff', o: '#ff5a1f', y: '#ffc400', r: '#e5242b' };
    var SHAPES = {
        radar: function (c) {
            return '<svg viewBox="0 0 100 100" fill="none" stroke="' + c + '"><circle cx="50" cy="50" r="48" stroke-width="1.6"/><circle cx="50" cy="50" r="33" stroke-width="1" opacity=".7"/><circle cx="50" cy="50" r="18" stroke-width="1" opacity=".7"/><path d="M50 0v100M0 50h100" stroke-width=".8" stroke-dasharray="2 3" opacity=".6"/><path d="M50 50L50 2A48 48 0 0 1 91.6 26Z" fill="' + c + '" fill-opacity=".16" stroke="none"/><path d="M50 50L50 2" stroke-width="1.6"/></svg>';
        },
        cross: function (c) {
            return '<svg viewBox="0 0 100 100" fill="none" stroke="' + c + '" stroke-width="3"><path d="M50 0v36M50 64v36M0 50h36M64 50h36"/><circle cx="50" cy="50" r="10"/></svg>';
        },
        warn: function (c) {
            return '<svg viewBox="0 0 100 100"><polygon points="50,6 96,90 4,90" fill="' + c + '" stroke="' + K + '" stroke-width="6" stroke-linejoin="round"/><rect x="45.5" y="36" width="9" height="30" fill="' + K + '"/><rect x="45.5" y="72" width="9" height="9" fill="' + K + '"/></svg>';
        },
        seis: function (c) {
            return '<svg viewBox="0 0 160 50" fill="none"><polyline points="0,25 160,25" stroke="' + c + '" stroke-width="1" opacity=".35"/><polyline class="rq-seis" points="0,25 30,25 34,21 38,29 42,25 56,25 60,10 65,42 70,3 75,46 80,14 84,33 88,25 104,25 108,20 112,30 116,25 160,25" stroke="' + c + '" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/></svg>';
        }
    };
    /* list: [jenis, warna, kiri, atas, ukuran(px), kedalaman parallax] */
    RQ.shapes = function (host, list) {
        if (!host) return;
        var bg = document.createElement('div');
        bg.className = 'rq-shapes';
        bg.setAttribute('aria-hidden', 'true');
        bg.innerHTML = list.map(function (s) {
            var cls = 'rq-shp' + (s[0] === 'seis' ? ' is-wide' : '') + (s[0] === 'radar' ? ' is-spin' : '');
            return '<div class="' + cls + '" style="left:' + s[2] + ';top:' + s[3] + ';--s:' + s[4] + 'px;--d:' + (s[5] || 14) + '">' + SHAPES[s[0]](COLORS[s[1]] || s[1]) + '</div>';
        }).join('');
        host.prepend(bg);
    };

    /* ---------- Pita berjalan ---------- */
    RQ.marquee = function (text, mod) {
        var one = '';
        for (var i = 0; i < 4; i++) one += '<span>' + text + '</span>';
        return '<div class="rq-mq ' + (mod || '') + '" aria-hidden="true"><div class="rq-mq__track">' + one + one + '</div></div>';
    };

    /* ---------- Odometer angka ---------- */
    function buildOdo(el) {
        if (el.dataset.odReady) return;
        var formatted = Number(el.dataset.od || 0).toLocaleString('id-ID');
        el.dataset.odReady = '1';
        el.setAttribute('aria-label', formatted);
        var digits = '01234567890123456789'.split('').map(function (d) { return '<i>' + d + '</i>'; }).join('');
        el.innerHTML = formatted.split('').map(function (ch) {
            return /\d/.test(ch)
                ? '<span class="rq-od" data-d="' + ch + '" aria-hidden="true"><span class="rq-od__c">' + digits + '</span></span>'
                : '<span class="rq-odx" aria-hidden="true">' + ch + '</span>';
        }).join('');
    }
    function rollOdo(el) {
        buildOdo(el);
        $$('.rq-od', el).forEach(function (d, i) {
            var c = d.firstChild;
            c.style.transition = 'none';
            c.style.transform = 'translateY(0)';
            c.offsetHeight;
            var target = -(10 + Number(d.dataset.d)) * 1.2;
            c.style.transition = RM ? 'none' : 'transform ' + (1.2 + i * 0.2) + 's cubic-bezier(.2,.8,.2,1) ' + (i * 0.07) + 's';
            c.style.transform = 'translateY(' + target + 'em)';
        });
    }

    /* ---------- Animasi saat elemen terlihat ---------- */
    function onVisible(el, fn, once) {
        if (!('IntersectionObserver' in window)) return fn(el);
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (e.isIntersecting) {
                    fn(e.target);
                    if (once !== false) io.unobserve(e.target);
                }
            });
        }, { threshold: 0.15 });
        io.observe(el);
    }
    RQ.onVisible = onVisible;

    function initVisible() {
        $$('[data-od]').forEach(function (el) { onVisible(el, rollOdo); });
        $$('.rq-ring').forEach(function (el) { onVisible(el, function (r) { r.classList.add('go'); }); });
        $$('.rq-rv').forEach(function (el) { onVisible(el, function (r) { r.classList.add('in'); }); });
        $$('[data-count]').forEach(function (el) {
            onVisible(el, function (t) {
                var to = Number(t.dataset.count || 0), t0 = performance.now(), suf = t.dataset.suffix || '';
                (function f(now) {
                    var p = RM ? 1 : Math.min(1, (now - t0) / 1300);
                    t.textContent = Math.round(to * (1 - Math.pow(1 - p, 4))).toLocaleString('id-ID') + suf;
                    if (p < 1) requestAnimationFrame(f);
                })(t0);
            });
        });
    }

    /* ---------- Tirai transisi antarhalaman ---------- */
    var WIPE_KEY = 'rqWipe';
    var wipe;
    function getWipe() {
        if (wipe) return wipe;
        wipe = document.createElement('div');
        wipe.className = 'rq-wipe';
        wipe.setAttribute('aria-hidden', 'true');
        wipe.innerHTML = '<i></i><i></i><i><b></b></i>';
        document.body.appendChild(wipe);
        return wipe;
    }
    function animateBars(from, to, delayBase, done) {
        var bars = Array.prototype.slice.call(getWipe().children);
        if (from === '0%') bars.reverse(); // saat membuka: lapisan atas terangkat dulu
        var count = 0;
        bars.forEach(function (b, i) {
            b.getAnimations && b.getAnimations().forEach(function (a) { a.cancel(); });
            var anim = b.animate(
                [{ transform: 'translateY(' + from + ')' }, { transform: 'translateY(' + to + ')' }],
                { duration: 560, delay: delayBase + i * 80, easing: 'cubic-bezier(.76,0,.24,1)', fill: 'both' }
            );
            anim.onfinish = function () { if (++count === bars.length && done) done(); };
        });
    }
    function wipeLabelFrom(link) {
        var label = link.getAttribute('data-wipe-label') || link.textContent || '';
        return label.replace(/\s+/g, ' ').trim().slice(0, 60) || 'RESQNET';
    }
    function shouldWipe(e, a) {
        if (RM || !a || e.defaultPrevented || e.button !== 0) return false;
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return false;
        if (a.target && a.target !== '_self') return false;
        if (a.hasAttribute('download') || a.hasAttribute('data-no-wipe')) return false;
        if (a.getAttribute('data-bs-toggle')) return false;
        var href = a.getAttribute('href');
        if (!href || href.charAt(0) === '#' || href.indexOf('javascript:') === 0) return false;
        var url;
        try { url = new URL(a.href, location.href); } catch (err) { return false; }
        if (url.origin !== location.origin) return false;
        if (url.pathname === location.pathname && url.search === location.search && url.hash) return false;
        if (/\.(pdf|png|jpe?g|zip|csv|xlsx?)$/i.test(url.pathname)) return false;
        return true;
    }
    function initWipe() {
        if (!document.body.hasAttribute('data-wipe')) return;
        document.addEventListener('click', function (e) {
            var a = e.target.closest && e.target.closest('a');
            if (!shouldWipe(e, a)) return;
            e.preventDefault();
            var w = getWipe();
            w.querySelector('b').textContent = wipeLabelFrom(a);
            w.classList.add('is-on');
            try { sessionStorage.setItem(WIPE_KEY, wipeLabelFrom(a)); } catch (err) {}
            var go = function () { location.href = a.href; };
            animateBars('101%', '0%', 0, go);
            setTimeout(go, 1100); // cadangan bila animasi tidak berjalan
        });
        // Halaman tujuan: tirai terangkat
        var label = null;
        try { label = sessionStorage.getItem(WIPE_KEY); sessionStorage.removeItem(WIPE_KEY); } catch (err) {}
        if (label && !RM) {
            var w = getWipe();
            w.querySelector('b').textContent = label;
            w.classList.add('is-on');
            animateBars('0%', '-101%', 140, function () { w.classList.remove('is-on'); });
        }
        // Tombol "kembali" browser (bfcache): pastikan tirai tidak tertinggal
        window.addEventListener('pageshow', function (ev) {
            if (ev.persisted && wipe) {
                wipe.classList.remove('is-on');
                Array.prototype.forEach.call(wipe.children, function (b) {
                    b.getAnimations && b.getAnimations().forEach(function (an) { an.cancel(); });
                });
            }
        });
    }

    /* ---------- Kursor & tombol magnetik (hanya mouse) ---------- */
    function initPointer() {
        if (!FINE || RM) return;
        var c = document.createElement('div');
        c.id = 'rq-cursor';
        c.setAttribute('aria-hidden', 'true');
        document.body.appendChild(c);
        var x = -100, y = -100, cx = x, cy = y, mg = null;
        var HOVER = 'a,button,[role="button"],.rq-story,.rq-table tbody tr,label.form-check-label,summary';
        var MAGNET = '.rq-btn,.rq-app .btn';
        window.addEventListener('pointermove', function (e) {
            x = e.clientX; y = e.clientY;
            c.classList.add('on');
            var t = e.target && e.target.closest ? e.target : document.body;
            var text = t.closest('input,textarea,select,.leaflet-container,[contenteditable]');
            c.classList.toggle('tx', !!text);
            c.classList.toggle('hv', !text && !!t.closest(HOVER));
            var b = t.closest(MAGNET);
            if (mg && mg !== b) { mg.style.translate = ''; mg = null; }
            if (b && !b.disabled) {
                mg = b;
                var r = b.getBoundingClientRect();
                b.style.translate = ((x - r.left - r.width / 2) * 0.16).toFixed(1) + 'px ' + ((y - r.top - r.height / 2) * 0.28).toFixed(1) + 'px';
            }
        }, { passive: true });
        document.documentElement.addEventListener('mouseleave', function () { c.classList.remove('on'); });
        window.addEventListener('pointerdown', function () { c.classList.add('dn'); });
        window.addEventListener('pointerup', function () { c.classList.remove('dn'); });
        (function loop() {
            cx += (x - cx) * 0.24; cy += (y - cy) * 0.24;
            c.style.transform = 'translate3d(' + cx.toFixed(1) + 'px,' + cy.toFixed(1) + 'px,0)';
            requestAnimationFrame(loop);
        })();
    }

    /* ---------- Parallax bentuk, progres scroll, pita reaktif ---------- */
    function initScrollFx() {
        var bar = $('#rq-progress i');
        var last = new WeakMap(), vel = 0, sk = 0;
        document.addEventListener('scroll', function (e) {
            var t = e.target === document ? document.scrollingElement : e.target;
            if (!t) return;
            var y = t.scrollTop, p = last.get(t);
            last.set(t, y);
            if (p !== undefined) vel += y - p;
        }, { capture: true, passive: true });
        if (FINE && !RM) {
            window.addEventListener('pointermove', function (e) {
                var px = (e.clientX / innerWidth - 0.5) * 2, py = (e.clientY / innerHeight - 0.5) * 2;
                $$('.rq-shapes').forEach(function (h) { h.style.setProperty('--px', px.toFixed(3)); h.style.setProperty('--py', py.toFixed(3)); });
            }, { passive: true });
        }
        (function tick() {
            var h = document.documentElement;
            if (bar) bar.style.transform = 'scaleX(' + (h.scrollTop / Math.max(1, h.scrollHeight - innerHeight)).toFixed(4) + ')';
            if (!RM) {
                var v = vel; vel = 0;
                sk += (Math.max(-9, Math.min(9, v * 0.22)) - sk) * 0.12;
                if (Math.abs(sk) > 0.02 || v) {
                    $$('.rq-mq').forEach(function (m) {
                        m.style.setProperty('--sk', sk.toFixed(2) + 'deg');
                        var track = m.firstElementChild;
                        var a = track && track.getAnimations ? track.getAnimations()[0] : null;
                        if (a) a.playbackRate = 1 + Math.min(5, Math.abs(sk) * 0.7);
                    });
                }
            }
            requestAnimationFrame(tick);
        })();
    }

  /* ---------- Menu samping desktop & HP ---------- */
function initSidebar() {
    var side = $('.rq-side');
    var scrim = $('.rq-scrim');
    var btn = $('.rq-top__menu');

    if (!side || !btn) return;

    var isMobile = function () {
        return window.matchMedia('(max-width: 960px)').matches;
    };

    var toggle = function (open) {
        if (isMobile()) {
            side.classList.toggle('is-open', open);
            if (scrim) scrim.classList.toggle('is-on', open);
        } else {
            side.classList.toggle('is-collapsed', !open);
            if (scrim) scrim.classList.remove('is-on');
        }

        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        btn.setAttribute('aria-label', open ? 'Tutup menu' : 'Buka menu');
    };

    btn.addEventListener('click', function () {
        var open = isMobile()
            ? !side.classList.contains('is-open')
            : !side.classList.contains('is-collapsed');

        toggle(open);
    });

    if (scrim) {
        scrim.addEventListener('click', function () {
            toggle(false);
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') toggle(false);
    });

    window.addEventListener('resize', function () {
        side.classList.remove('is-open', 'is-collapsed');
        if (scrim) scrim.classList.remove('is-on');
        btn.setAttribute('aria-expanded', 'false');
        btn.setAttribute('aria-label', 'Buka menu');
    });
}

    /* ---------- Jam WIB ---------- */
    function initClock() {
        var els = $$('[data-clock]');
        if (!els.length) return;
        var tick = function () {
            var s;
            try {
                s = new Date().toLocaleTimeString('id-ID', { timeZone: 'Asia/Jakarta', hour12: false }).replace(/\./g, ':') + ' WIB';
            } catch (err) { s = new Date().toTimeString().slice(0, 8); }
            els.forEach(function (el) { el.textContent = s; });
        };
        tick();
        setInterval(tick, 1000);
    }

    /* ---------- Tutup pesan flash ---------- */
    function initFlash() {
        $$('[data-dismiss-flash]').forEach(function (b) {
            b.addEventListener('click', function () { b.closest('.rq-flash').remove(); });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initVisible();
        initWipe();
        initPointer();
        initScrollFx();
        initSidebar();
        initClock();
        initFlash();
    });
})();
