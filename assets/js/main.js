// main.js — Mamun's Ortho Dental Management System
// Global UI helpers

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

    // ── Auto-dismiss flash alerts ───────────────────────────────────────────
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

});
