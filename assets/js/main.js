// main.js — Mamun's Ortho Dental Management System
// Global UI helpers

// ── Network Status Monitor ─────────────────────────────────────────────────
const NetworkMonitor = {
    isOnline: navigator.onLine,
    wasOnline: navigator.onLine,
    checkInterval: null,
    lastCheck: 0,
    checkDelay: 5000,

    init() {
        this.setupEventListeners();
        this.startPeriodicCheck();
    },

    setupEventListeners() {
        window.addEventListener('online', () => this.handleOnline());
        window.addEventListener('offline', () => this.handleOffline());
    },

    startPeriodicCheck() {
        this.checkInterval = setInterval(() => this.checkConnectionQuality(), this.checkDelay);
    },

    async checkConnectionQuality() {
        const now = Date.now();
        if (now - this.lastCheck < this.checkDelay) return;
        this.lastCheck = now;

        try {
            const start = performance.now();
            await fetch('api/ping.php', { method: 'HEAD', cache: 'no-cache', signal: AbortSignal.timeout(3000) });
            const latency = performance.now() - start;

            if (!this.isOnline) {
                this.isOnline = true;
                this.handleOnline();
            }

            if (latency > 2000) {
                this.showWeakConnection(latency);
            }
        } catch {
            if (this.isOnline) {
                this.isOnline = false;
                this.handleOffline();
            }
        }
    },

    handleOnline() {
        if (!this.wasOnline) {
            AdminToast.show('Back online', 'success');
            this.wasOnline = true;
        }
        this.isOnline = true;
    },

    handleOffline() {
        AdminToast.show('No internet connection', 'error', 0);
        this.wasOnline = false;
        this.isOnline = false;
    },

    showWeakConnection(latency) {
        const msg = `Weak connection (${Math.round(latency)}ms)`;
        AdminToast.show(msg, 'warning');
    },

    destroy() {
        if (this.checkInterval) {
            clearInterval(this.checkInterval);
            this.checkInterval = null;
        }
    }
};

// ── Premium Toast Notification System ──────────────────────────────────────
const AdminToast = {
    container: null,

    init() {
        if (this.container) return;
        this.container = document.createElement('div');
        this.container.id = 'toast-container';
        // Styled using premium fixed layout in the bottom-right viewport
        this.container.className = 'fixed bottom-6 right-6 z-[99999] flex flex-col gap-3 max-w-sm w-full px-4 sm:px-0 pointer-events-none';
        document.body.appendChild(this.container);
    },

    show(message, type = 'success', duration = 4000) {
        this.init();

        const toast = document.createElement('div');
        // Styled with Tailwind CSS classes for premium cards
        toast.className = 'toast-card pointer-events-auto flex items-center gap-3.5 px-4.5 py-3.5 bg-white border border-gray-100 rounded-2xl shadow-[0_10px_30px_rgba(0,69,145,0.12)] text-sm font-semibold transition-all duration-300 toast-animate-in';
        
        let iconHtml = '';
        let textColor = 'text-[#004591]';

        if (type === 'success') {
            iconHtml = '<div class="w-8 h-8 rounded-xl bg-green-50 text-green-500 flex items-center justify-center flex-shrink-0"><i class="fas fa-check text-xs"></i></div>';
            textColor = 'text-green-800';
        } else if (type === 'error') {
            iconHtml = '<div class="w-8 h-8 rounded-xl bg-red-50 text-red-500 flex items-center justify-center flex-shrink-0"><i class="fas fa-exclamation-triangle text-xs"></i></div>';
            textColor = 'text-red-800';
            duration = Math.max(duration, 6000); // Errors persist longer
        } else if (type === 'warning') {
            iconHtml = '<div class="w-8 h-8 rounded-xl bg-yellow-50 text-yellow-500 flex items-center justify-center flex-shrink-0"><i class="fas fa-exclamation-circle text-xs"></i></div>';
            textColor = 'text-yellow-800';
            duration = Math.max(duration, 8000);
        } else if (type === 'loading') {
            iconHtml = '<div class="w-8 h-8 rounded-xl bg-blue-50 text-[#004591] flex items-center justify-center flex-shrink-0"><i class="fas fa-circle-notch fa-spin text-xs"></i></div>';
            textColor = 'text-[#004591]';
            duration = 0; // Persistent until redirect / manual dismissal
        }

        toast.innerHTML = `
            ${iconHtml}
            <span class="flex-1 ${textColor} font-medium pr-2 text-[13px] leading-tight">${message}</span>
            ${type !== 'loading' ? '<button class="toast-close ml-auto text-gray-300 hover:text-gray-500 transition-colors"><i class="fas fa-times text-[10px]"></i></button>' : ''}
        `;

        // Apply style rules directly to guarantee premium Outfit styling
        toast.style.fontFamily = "'Outfit', sans-serif";

        this.container.appendChild(toast);

        // Bind close event if not loading
        const closeBtn = toast.querySelector('.toast-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.dismiss(toast));
        }

        // Auto dismiss timer
        if (duration > 0) {
            setTimeout(() => this.dismiss(toast), duration);
        }

        return toast;
    },

    dismiss(toast) {
        if (!toast) return;
        toast.classList.remove('toast-animate-in');
        toast.classList.add('toast-animate-out');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }
};

document.addEventListener('DOMContentLoaded', () => {

    // ── Live Patient Search (AJAX) ──────────────────────────────────────────
    const globalSearch = document.getElementById('globalSearch');
    const searchResults = document.getElementById('searchResults');

    if (globalSearch && searchResults) {
        let searchTimeout;

        globalSearch.addEventListener('input', () => {
            const q = globalSearch.value.trim();
            clearTimeout(searchTimeout);

            if (q.length < 2) {
                searchResults.classList.add('hidden');
                searchResults.innerHTML = '';
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch(`api/search_patient.php?q=${encodeURIComponent(q)}`)
                    .then(r => r.json())
                    .then(data => {
                        searchResults.innerHTML = '';
                        const results = (data && data.status === 'success' && Array.isArray(data.data)) ? data.data : [];
                        if (!results.length) {
                            searchResults.innerHTML = `
                                <div class="px-4 py-5 text-center text-sm text-gray-400">
                                    <i class="fas fa-search-minus text-lg mb-2 block"></i>No patients found
                                </div>`;
                        } else {
                            results.forEach(p => {
                                const item = document.createElement('a');
                                item.href = `patient_record.php?pid=${p.patient_id}`;
                                item.target = '_blank';
                                item.className = 'flex items-center gap-3 px-4 py-3 hover:bg-[#F4F7FC] transition-colors border-b border-gray-50 last:border-b-0';
                                item.innerHTML = `
                                    <div class="w-8 h-8 rounded-full bg-[#004591] flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                        ${p.name.charAt(0).toUpperCase()}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-[#004591] text-sm">${p.name}</p>
                                        <p class="text-xs text-[#7c7c7c]">${p.patient_id} · ${p.phone}</p>
                                    </div>
                                    <i class="fas fa-external-link-alt text-[#ea741b] text-xs ml-auto"></i>`;
                                searchResults.appendChild(item);
                            });
                        }
                        searchResults.classList.remove('hidden');
                    })
                    .catch(() => searchResults.classList.add('hidden'));
            }, 280);
        });

        // Close results on outside click
        document.addEventListener('click', e => {
            if (!globalSearch.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.classList.add('hidden');
            }
        });
    }

    // ── Process Query Parameter Flash Messages ──────────────────────────────
    const urlParams = new URLSearchParams(window.location.search);
    const successMsg = urlParams.get('success');
    const errorMsg = urlParams.get('error');

    if (successMsg) {
        AdminToast.show(successMsg, 'success');
    } else if (errorMsg) {
        AdminToast.show(errorMsg, 'error');
    }

    // Clean parameters from address bar to prevent toast re-triggers on refresh
    if (successMsg || errorMsg) {
        urlParams.delete('success');
        urlParams.delete('error');
        const newSearch = urlParams.toString();
        const newUrl = window.location.pathname + (newSearch ? '?' + newSearch : '') + window.location.hash;
        window.history.replaceState({ path: newUrl }, '', newUrl);

        // Hide inline alerts to maintain a clean layout
        ['successAlert', 'errorAlert'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.remove();
        });

        // Remove ID-less PHP alerts (like in users.php) by text matching
        document.querySelectorAll('main div').forEach(el => {
            const txt = el.textContent || '';
            const isMatch = (successMsg && txt.includes(successMsg)) || (errorMsg && txt.includes(errorMsg));
            if (isMatch && (el.classList.contains('bg-green-50') || el.classList.contains('bg-red-50'))) {
                el.remove();
            }
        });
    }

    // ── Global Form Submissions Loading Interceptor ─────────────────────────
    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (form.method && form.method.toLowerCase() === 'post') {
            // Check in next tick in case local JS overrides and calls preventDefault() (e.g. AJAX forms)
            setTimeout(() => {
                if (e.defaultPrevented) return;

                // Detect if the form has selected file inputs for upload-specific message
                const fileInputs = form.querySelectorAll('input[type="file"]');
                let hasFiles = false;
                fileInputs.forEach(input => {
                    if (input.files && input.files.length > 0) {
                        hasFiles = true;
                    }
                });

                const msg = hasFiles ? 'Uploading files, please wait...' : 'Processing request...';
                AdminToast.show(msg, 'loading');

                // Disable submit buttons to block double-submissions
                const submits = form.querySelectorAll('button[type="submit"], input[type="submit"]');
                submits.forEach(btn => {
                    btn.disabled = true;
                    btn.classList.add('opacity-50', 'cursor-not-allowed');
                });
            }, 0);
        }
    });

    // ── Fallback Auto-dismiss for any static flash alerts ──────────────────
    setTimeout(() => {
        ['successAlert', 'errorAlert'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.style.transition = 'opacity 0.5s ease';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 500);
            }
        });
    }, 5000);

    // ── Network Status Monitor ──────────────────────────────────────────────
    NetworkMonitor.init();

});
