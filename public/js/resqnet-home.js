/* =========================================================================
   RESQNET — animasi khusus landing page (home.blade.php)
   Data angka berasal dari window.RQ_HOME (HomeController).
   ========================================================================= */
(function () {
    'use strict';

    var RQ = window.RQ, RM = RQ.reducedMotion;
    var S = window.RQ_HOME || {};
    var $ = function (s) { return document.querySelector(s); };
    var $$ = function (s) { return Array.prototype.slice.call(document.querySelectorAll(s)); };
    var nf = function (n) { return Number(n || 0).toLocaleString('id-ID'); };

    /* ---------- 1. Preloader: tampil sekali per sesi browser ---------- */
    var pre = $('#rq-pre'), preMs = 0;
    var seen = false;
    try { seen = sessionStorage.getItem('rqIntroSeen') === '1'; sessionStorage.setItem('rqIntroSeen', '1'); } catch (e) {}
    if (pre) {
        if (RM || seen) {
            pre.remove();
        } else {
            preMs = 1900;
            var n = pre.querySelector('.rq-pre__n'), bar = pre.querySelector('.rq-pre__bar i'), t0 = performance.now();
            (function f(t) {
                var p = Math.min(1, (t - t0) / 1250), e = 1 - Math.pow(1 - p, 3);
                n.textContent = Math.round(e * 100) + '%';
                bar.style.transform = 'scaleX(' + e + ')';
                if (p < 1) requestAnimationFrame(f);
                else { pre.classList.add('out'); setTimeout(function () { pre.remove(); }, 1300); }
            })(t0);
        }
    }

    /* ---------- 2. Pita hazard & bentuk grafis ---------- */
    $$('[data-mq]').forEach(function (el) {
        var text = el.getAttribute('data-mq').split('|').join(' ▲ ');
        el.outerHTML = RQ.marquee(text, el.getAttribute('data-mod'));
    });
    RQ.shapes($('.rq-cin'), [['radar', 'k', '3%', '16%', 120, 20], ['seis', 'o', '2%', '50%', 230, 10], ['warn', 'y', '91%', '64%', 64, 24], ['cross', 'k', '90%', '18%', 44, 16]]);
    RQ.shapes($('#cara'), [['radar', 'k', '4%', '9%', 120, 18], ['warn', 'y', '90%', '12%', 60, 22], ['seis', 'o', '62%', '92%', 240, 10], ['cross', 'k', '6%', '80%', 40, 14]]);
    RQ.shapes($('#sorotan'), [['radar', 'w', '88%', '8%', 130, 18], ['seis', 'o', '4%', '88%', 240, 10], ['cross', 'w', '46%', '10%', 40, 14]]);
    RQ.shapes($('#lapor'), [['warn', 'y', '88%', '9%', 64, 22], ['cross', 'k', '6%', '12%', 44, 14], ['seis', 'o', '4%', '90%', 220, 10]]);
    RQ.shapes($('#menu'), [['radar', 'w', '90%', '6%', 120, 18], ['seis', 'o', '70%', '95%', 220, 10]]);

    /* ---------- 3. Panel hero berisi angka asli ---------- */
    var tb = function (t) { return '<div class="rq-win__tb"><i></i><i></i><i></i><span>' + t + '</span></div>'; };
    var row = function (label, value, badge) {
        return '<div class="rq-win__r"><span>' + label + '</span>' + (badge ? '<span class="rq-badge ' + badge + '">' + value + '</span>' : '<b>' + value + '</b>') + '</div>';
    };
    var dots = [[20, 30], [48, 55], [70, 25], [62, 72], [30, 70], [82, 52], [40, 38], [56, 20]];
    var dotCount = Math.min(dots.length, Math.max(0, S.verified_reports || 0));
    var cats = (S.categories || []).map(function (c) { return row(c.name, nf(c.total), 'rq-badge--ok'); }).join('')
        || row('Belum ada laporan terverifikasi', '0');

    var panels = [
        ['left:-3%;top:9%;width:30%', '.2s', tb('Peta laporan') + '<div class="rq-win__map">' + dots.slice(0, dotCount).map(function (p) { return '<i style="left:' + p[0] + '%;top:' + p[1] + '%"></i>'; }).join('') + '</div>'],
        ['right:-5%;top:-3%;width:32%', '.5s', tb('Status distribusi') + row('Dialokasikan', nf(S.allocated), 'rq-badge--mut') + row('Dikirim', nf(S.shipped), 'rq-badge--wait') + row('Diterima', nf(S.received), 'rq-badge--ok')],
        ['left:-5%;bottom:-3%;width:33%', '.8s', tb('Laporan per kategori') + cats],
        ['right:-1%;bottom:-5%;width:25%', '1.1s', '<div class="rq-win__st"><span>RESQNET</span><b>' + nf(S.verified_reports) + '</b><span>laporan terverifikasi</span></div>']
    ];
    $('#rqPanels').innerHTML = panels.map(function (p) {
        return '<div class="rq-pw" style="' + p[0] + '"><div class="rq-win rq-pn" style="--dl:' + p[1] + '">' + p[2] + '</div></div>';
    }).join('');

    // Grafik kecil di mockup: dinormalisasi dari angka asli
    var series = [S.verified_reports, S.aid_requests, S.pending_requests, S.allocated, S.shipped, S.received, S.active_warehouses].map(Number);
    var max = Math.max.apply(null, series.concat([1]));
    $('#rqMockChart').innerHTML = series.map(function (v, i) {
        return '<i style="--h:' + Math.max(8, Math.round(v / max * 100)) + '%;--n:' + i + '"></i>';
    }).join('');

    /* ---------- 4. Sekuens hero sinematik ---------- */
    var cin = $('.rq-cin'), skip = false, fin = false;
    var G = function (id) { return document.getElementById(id); };
    var wait = function (ms) { return skip ? Promise.reject('skip') : RQ.sleep(ms); };
    var sw = function (id, a, b) { var e = G(id); e.classList.remove(a); e.classList.add(b); };

    function finalize() {
        if (fin) return;
        fin = true;
        ['scSearch', 'scLogo', 'scKinetic', 'scCta'].forEach(function (id) { var e = G(id); e.classList.remove('on'); e.classList.add('out'); });
        cin.classList.add('is-lit', 'is-end');
        G('rqPanels').classList.add('is-pv');
        G('rqMock').classList.add('show');
        document.body.classList.add('is-live');
        RQ.sleep(RM ? 0 : 900)
            .then(function () { return RQ.type(G('rqFin1'), 'Lapor sekarang.', 48); })
            .then(function () { return RQ.type(G('rqFin2'), 'Bantuan menyusul.', 58, true); });
    }

    function hero() {
        return wait(preMs || 500)
            .then(function () { sw('scSearch', 'out', 'on'); return RQ.type(G('rqTyped'), 'Banjir di RT 05 Selebar, warga butuh evakuasi', 44, true); })
            .then(function () { return wait(700); })
            .then(function () { G('rqSend').classList.add('hit'); return wait(550); })
            .then(function () { sw('scSearch', 'on', 'out'); return wait(800); })
            .then(function () { cin.classList.add('is-lit'); sw('scLogo', 'out', 'on'); return RQ.type(G('rqLogoText'), 'RESQNET', 95); })
            .then(function () { return wait(1500); })
            .then(function () { sw('scLogo', 'on', 'out'); return wait(700); })
            .then(function () {
                G('rqPanels').classList.add('is-pv');
                sw('scKinetic', 'out', 'on');
                var words = $$('#scKinetic .rq-wd'), chain = Promise.resolve();
                words.forEach(function (w) { chain = chain.then(function () { w.classList.add('on'); return wait(380); }); });
                return chain;
            })
            .then(function () { return wait(1500); })
            .then(function () { sw('scKinetic', 'on', 'out'); return wait(700); })
            .then(function () { sw('scCta', 'out', 'on'); return wait(500); })
            .then(function () { G('rqCursorDemo').classList.add('go'); return wait(1500); })
            .then(function () { G('rqCtaDemo').classList.add('pr'); return wait(380); })
            .then(function () { G('rqCtaDemo').classList.remove('pr'); return wait(500); })
            .then(function () { sw('scCta', 'on', 'out'); return wait(700); })
            .then(function () { G('rqMock').classList.add('show'); return wait(1700); })
            .then(finalize);
    }

    G('rqSkip').addEventListener('click', function () { skip = true; finalize(); });
    // Pengguna langsung menggulir: tampilkan keadaan akhir agar navigasi muncul
    window.addEventListener('scroll', function () {
        if (!fin && window.scrollY > innerHeight * 0.3) { skip = true; finalize(); }
    }, { passive: true });
    if (RM || location.hash) { skip = true; finalize(); } else { hero().catch(function () {}); }

    /* ---------- 5. Judul diketik saat terlihat ---------- */
    RQ.onVisible($('#rqA1'), function () {
        RQ.type($('#rqA1'), 'Satu laporan.', 55)
            .then(function () { return RQ.type($('#rqA2'), 'Satu jaringan.', 55); })
            .then(function () { return RQ.type($('#rqA3'), 'Bantuan tepat sasaran.', 45, true); });
    });
    RQ.onVisible($('#rqW1'), function () {
        RQ.type($('#rqW1'), 'RESQNET,', 60).then(function () { return RQ.type($('#rqW2'), 'dalam sorotan.', 60, true); });
    });

    /* ---------- 6. Kartu sorotan (gaya story) ---------- */
    var slides = [
        [S.verified_reports, 'laporan bencana terverifikasi', 'Laporan warga yang sudah diperiksa petugas'],
        [S.aid_requests, 'permintaan bantuan masuk', nf(S.pending_requests) + ' di antaranya masih menunggu'],
        [S.available_stock, 'stok bantuan siap salur', 'Tersimpan di ' + nf(S.active_warehouses) + ' gudang aktif'],
        [S.distributions, 'distribusi bantuan tercatat', nf(S.received) + ' sudah diterima di lokasi tujuan']
    ];
    var logo = document.querySelector('.rq-brand img').getAttribute('src');
    var story = $('#rqStory');
    story.innerHTML = '<div class="rq-segs">' + slides.map(function () { return '<div class="rq-seg"><i></i></div>'; }).join('') + '</div>' +
        slides.map(function (s, i) {
            return '<div class="rq-slide rq-slide--' + i + '"><div class="rq-slide__sb"><img src="' + logo + '" alt=""><span class="rq-wm">RESQNET</span></div>' +
                '<div class="rq-slide__n">0</div><div class="rq-slide__cp"></div><div class="rq-slide__sm">' + s[2] + '</div></div>';
        }).join('');
    var sl = story.querySelectorAll('.rq-slide'), sg = story.querySelectorAll('.rq-seg'), cur = -1, timer;
    function show(i) {
        cur = i;
        clearTimeout(timer);
        story.classList.toggle('is-dark', i === 0);
        sl.forEach(function (s, k) { s.classList.toggle('on', k === i); });
        sg.forEach(function (s, k) {
            s.classList.remove('o');
            s.classList.toggle('d', k < i);
            if (k === i) { void s.offsetWidth; s.classList.add('o'); }
        });
        var nEl = sl[i].querySelector('.rq-slide__n'), to = Number(slides[i][0] || 0), t0 = performance.now();
        (function f(t) {
            var p = RM ? 1 : Math.min(1, (t - t0) / 1100);
            nEl.textContent = nf(Math.round(to * (1 - Math.pow(1 - p, 3))));
            if (p < 1 && cur === i) requestAnimationFrame(f);
        })(t0);
        RQ.type(sl[i].querySelector('.rq-slide__cp'), slides[i][1], 34);
        timer = setTimeout(function () { show((i + 1) % slides.length); }, 4400);
    }
    var next = function () { show((cur + 1) % slides.length); };
    story.addEventListener('click', next);
    story.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); next(); } });
    RQ.onVisible(story, function () { show(0); });

    /* ---------- 7. Wordmark footer ---------- */
    RQ.onVisible($('.rq-foot__big'), function (el) { el.classList.add('in'); });
})();
