/* ═══════════════════════════════════════════════════════════════════
   LOADER.JS — Mamun's Ortho Dental Loading Screen Lifecycle
   ═══════════════════════════════════════════════════════════════════ */

(function () {
    'use strict';

    var MIN_DISPLAY = 2600;
    var FADE_DELAY   = 400;

    var loader, fill, startTime;
    var currentProgress = 0;
    var realLoaded = false;

    document.addEventListener('DOMContentLoaded', init);

    function init() {
        loader = document.querySelector('.mod-loader');
        fill   = document.querySelector('.mod-loader__fill');

        if (!loader) return;

        /* Skip loader on repeat visits OR after form submissions (success/error in URL) */
        var hasFlash = new URLSearchParams(window.location.search).has('success') || new URLSearchParams(window.location.search).has('error');
        if (sessionStorage.getItem('mod_loader_seen') || hasFlash) {
            loader.parentNode.removeChild(loader);
            document.dispatchEvent(new CustomEvent('loader:done'));
            return;
        }

        startTime = Date.now();
        document.body.style.overflow = 'hidden';
        sessionStorage.setItem('mod_loader_seen', '1');

        simulateProgress();
        window.addEventListener('load', onRealLoad);
        setTimeout(forceDismiss, 8000);
    }

    /* ─── Progress Simulation ─── */
    function simulateProgress() {
        var milestones = [
            { at: 20, speed: 45 },
            { at: 48, speed: 65 },
            { at: 72, speed: 90 },
            { at: 90, speed: 130 }
        ];
        var idx = 0;

        function tick() {
            if (currentProgress >= 100) return;
            var target = idx < milestones.length ? milestones[idx].at : 100;
            var speed  = idx < milestones.length ? milestones[idx].speed : 100;

            if (currentProgress < target) {
                currentProgress = Math.min(currentProgress + 1, target);
                updateBar(currentProgress);
            }
            if (currentProgress >= target && idx < milestones.length) idx++;
            if (currentProgress < 100) setTimeout(tick, speed);
        }

        tick();
    }

    function updateBar(val) {
        if (fill) fill.style.width = val + '%';
    }

    /* ─── Real Load Handler ─── */
    function onRealLoad() {
        realLoaded = true;
        animateTo100(function () {
            setTimeout(dismiss, FADE_DELAY);
        });
    }

    function animateTo100(cb) {
        function step() {
            if (currentProgress >= 100) {
                currentProgress = 100;
                updateBar(100);
                if (cb) cb();
                return;
            }
            currentProgress = Math.min(currentProgress + 2, 100);
            updateBar(currentProgress);
            requestAnimationFrame(step);
        }
        step();
    }

    function forceDismiss() {
        if (realLoaded) return;
        realLoaded = true;
        animateTo100(function () {
            setTimeout(dismiss, 200);
        });
    }

    /* ─── Dismiss Loader (Slide Up) ─── */
    function dismiss() {
        if (!loader || loader.classList.contains('slide-up')) return;

        var elapsed = Date.now() - startTime;
        var wait    = Math.max(0, MIN_DISPLAY - elapsed);

        setTimeout(function () {
            loader.classList.add('slide-up');
            document.body.style.overflow = '';

            setTimeout(function () {
                if (loader && loader.parentNode) {
                    loader.parentNode.removeChild(loader);
                }
                document.dispatchEvent(new CustomEvent('loader:done'));
            }, 1200);
        }, wait);
    }

})();
