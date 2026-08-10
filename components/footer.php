<!-- Developer Credit Bar -->
<div class="bg-[#080B14] border-t border-[#1e293b] px-6 py-3 flex items-center justify-between flex-shrink-0 mt-auto">
    <p class="text-[10px] text-gray-500 font-medium">&copy; <?= date('Y') ?> Mamun's Ortho Dental. Crafted with care.</p>
    <p class="text-[10px] text-gray-500">Designed &amp; Developed by <a href="https://umaerislam.com" target="_blank" rel="dofollow" class="text-[#ea741b] hover:text-[#f5973e] font-bold transition-colors">Umaer Islam</a></p>
</div>

<!-- Footer: closes wrappers opened in topbar.php / header.php -->
</div> <!-- /main content area wrapper (scrolls internally) -->
</div> <!-- /right side wrapper (stacks topbar and main content) -->
</div> <!-- /flex h-screen from header.php -->

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.8.1/flowbite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Core UI JS -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // User menu toggle
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userMenu    = document.getElementById('userMenu');
    if (userMenuBtn && userMenu) {
        userMenuBtn.addEventListener('click', (e) => { e.stopPropagation(); userMenu.classList.toggle('hidden'); });
        document.addEventListener('click', (e) => {
            if (!userMenuBtn.contains(e.target)) userMenu.classList.add('hidden');
        });
    }

    // Init all custom dropdowns
    document.querySelectorAll('.mod-dropdown').forEach(initModDropdown);
    // Init all custom calendars
    document.querySelectorAll('.mod-calendar').forEach(initModCalendar);
    // Init all custom time pickers
    document.querySelectorAll('.mod-time').forEach(initModTime);
    // Auto-init dropdowns, calendars & time pickers added later (modals, dynamic content)
    new MutationObserver(() => {
      document.querySelectorAll('.mod-dropdown:not([data-mod-init])').forEach(initModDropdown);
      document.querySelectorAll('.mod-calendar:not([data-cal-init])').forEach(initModCalendar);
      document.querySelectorAll('.mod-time:not([data-time-init])').forEach(initModTime);
    }).observe(document.body, { childList: true, subtree: true });
});

/* ═══════════════════════════════════════════════════════
   CUSTOM DROPDOWN — Reusable
   ═══════════════════════════════════════════════════════ */
function initModDropdown(root) {
    if (root._modInit) return;
    root._modInit = true;
    root.setAttribute('data-mod-init', '1');
    const trigger  = root.querySelector('.mod-dropdown-trigger');
    const selected = root.querySelector('.mod-dropdown-selected');
    const panel    = root.querySelector('.mod-dropdown-panel');
    const input    = root.querySelector('input[type="hidden"]');
    const options  = root.querySelectorAll('.mod-dropdown-option');

    function open() {
      root.classList.add('is-open');
      const active = root.querySelector('.mod-dropdown-option.is-selected');
      if (active) active.scrollIntoView({ block: 'nearest' });
    }
    function close() { root.classList.remove('is-open'); }

    trigger.addEventListener('click', (e) => {
      e.stopPropagation();
      root.classList.contains('is-open') ? close() : open();
    });

    options.forEach(opt => {
      opt.addEventListener('click', (e) => {
        e.stopPropagation();
        const val = opt.dataset.value;
        const txt = opt.querySelector('span:last-child').textContent;
        input.value = val;
        selected.textContent = txt;
        root.classList.toggle('has-value', val !== '');
        options.forEach(o => o.classList.remove('is-selected'));
        opt.classList.add('is-selected');
        input.dispatchEvent(new Event('change', { bubbles: true }));
        close();
      });
    });

    root.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
}

/* Single global handler: close all open dropdowns on outside click */
document.addEventListener('click', (e) => {
    document.querySelectorAll('.mod-dropdown.is-open').forEach(d => {
      if (!d.contains(e.target)) d.classList.remove('is-open');
    });
});

/* Helper: set dropdown value from JS (replaces select.value = x) */
function setModDropdown(idOrEl, value) {
    let root;
    if (typeof idOrEl === 'string') {
      root = document.getElementById(idOrEl);
    } else {
      root = idOrEl;
    }
    if (!root || !root.classList.contains('mod-dropdown')) return;
    const input   = root.querySelector('input[type="hidden"]');
    const display = root.querySelector('.mod-dropdown-selected');
    const options = root.querySelectorAll('.mod-dropdown-option');
    let found = false;
    options.forEach(o => {
      o.classList.remove('is-selected');
      if (o.dataset.value === value) {
        o.classList.add('is-selected');
        display.textContent = o.querySelector('span:last-child').textContent;
        found = true;
      }
    });
    if (!found) {
      display.textContent = root.dataset.placeholder || 'Select...';
      root.classList.remove('has-value');
    } else {
      root.classList.add('has-value');
    }
    input.value = value;
    input.dispatchEvent(new Event('change', { bubbles: true }));
}

/* ═══════════════════════════════════════════════════════
   CUSTOM DATE PICKER — Reusable
   ═══════════════════════════════════════════════════════ */
const CAL_MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
const CAL_DAYS = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

function initModCalendar(root) {
    if (root._calInit) return;
    root._calInit = true;
    const input   = root.querySelector('input[type="hidden"]');
    const text    = root.querySelector('.mod-calendar-text');
    const panel   = root.querySelector('.mod-calendar-panel');
    const clearBtn= root.querySelector('.mod-calendar-clear');
    const trigger = root.querySelector('.mod-calendar-trigger');
    let viewDate  = input.value ? new Date(input.value + 'T00:00:00') : new Date();
    let selected  = input.value || '';

    function fmtDate(d) {
      const y = d.getFullYear(), m = String(d.getMonth()+1).padStart(2,'0'), dd = String(d.getDate()).padStart(2,'0');
      return y+'-'+m+'-'+dd;
    }
    function fmtDisplay(d) {
      return d.getDate() + ' ' + CAL_MONTHS[d.getMonth()].slice(0,3) + ' ' + d.getFullYear();
    }
    function today() { const n=new Date(); n.setHours(0,0,0,0); return n; }

    function render(direction) {
      const daysEl = panel.querySelector('.cal-days');
      const myEl   = panel.querySelector('.cal-month-year');
      const t = today();
      const y = viewDate.getFullYear(), m = viewDate.getMonth();
      myEl.innerHTML = '<span>'+CAL_MONTHS[m]+'</span><span>'+y+'</span>';
      const first = new Date(y, m, 1).getDay();
      const daysInMonth = new Date(y, m+1, 0).getDate();
      const daysInPrev = new Date(y, m, 0).getDate();
      let html = '';
      /* previous month trailing days */
      for (let i = first-1; i >= 0; i--) {
        html += '<button type="button" class="cal-day cal-day--other-month" data-date="'+fmtDate(new Date(y,m-1,daysInPrev-i))+'">'+(daysInPrev-i)+'</button>';
      }
      /* current month days */
      for (let d = 1; d <= daysInMonth; d++) {
        const dt = new Date(y, m, d);
        const ds = fmtDate(dt);
        let cls = 'cal-day';
        if (ds === fmtDate(t)) cls += ' cal-day--today';
        if (ds === selected) cls += ' cal-day--selected';
        html += '<button type="button" class="'+cls+'" data-date="'+ds+'">'+d+'</button>';
      }
      /* next month leading days */
      const totalCells = first + daysInMonth;
      const remaining = totalCells % 7 === 0 ? 0 : 7 - (totalCells % 7);
      for (let i = 1; i <= remaining; i++) {
        html += '<button type="button" class="cal-day cal-day--other-month" data-date="'+fmtDate(new Date(y,m+1,i))+'">'+i+'</button>';
      }
      daysEl.innerHTML = html;
      /* slide animation */
      if (direction) {
        daysEl.classList.remove('cal-days--slide-left','cal-days--slide-right');
        void daysEl.offsetWidth; /* force reflow */
        daysEl.classList.add(direction === 'left' ? 'cal-days--slide-left' : 'cal-days--slide-right');
      }
      /* bind day clicks */
      daysEl.querySelectorAll('.cal-day').forEach(btn => {
        btn.addEventListener('click', () => {
          const ds = btn.dataset.date;
          selected = ds;
          input.value = ds;
          text.textContent = fmtDisplay(new Date(ds+'T00:00:00'));
          root.classList.add('has-value');
          input.dispatchEvent(new Event('change', { bubbles: true }));
          close();
        });
      });
    }

    function open() {
      if (input.value) viewDate = new Date(input.value + 'T00:00:00');
      render();
      root.classList.add('is-open');
    }
    function close() { root.classList.remove('is-open'); }

    trigger.addEventListener('click', (e) => { e.stopPropagation(); root.classList.contains('is-open') ? close() : open(); });
    clearBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      selected = ''; input.value = '';
      text.textContent = root.dataset.placeholder || 'Select date';
      root.classList.remove('has-value');
      input.dispatchEvent(new Event('change', { bubbles: true }));
    });

    /* month nav */
    panel.querySelector('.cal-prev').addEventListener('click', (e) => { e.stopPropagation(); viewDate.setMonth(viewDate.getMonth()-1); render('right'); });
    panel.querySelector('.cal-next').addEventListener('click', (e) => { e.stopPropagation(); viewDate.setMonth(viewDate.getMonth()+1); render('left'); });

    /* today button */
    panel.querySelector('.cal-today-btn').addEventListener('click', (e) => {
      e.stopPropagation();
      const t = today();
      viewDate = new Date(t);
      selected = fmtDate(t);
      input.value = selected;
      text.textContent = fmtDisplay(t);
      root.classList.add('has-value');
      render();
      input.dispatchEvent(new Event('change', { bubbles: true }));
      close();
    });

    /* keyboard nav */
    root.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') close();
      if (e.key === 'ArrowLeft') { viewDate.setMonth(viewDate.getMonth()-1); render('right'); }
      if (e.key === 'ArrowRight') { viewDate.setMonth(viewDate.getMonth()+1); render('left'); }
    });

    panel.addEventListener('click', (e) => e.stopPropagation());

    /* init display */
    if (selected) {
      text.textContent = fmtDisplay(new Date(selected+'T00:00:00'));
      root.classList.add('has-value');
    }
}

/* Global: close all open calendars on outside click */
document.addEventListener('click', () => {
    document.querySelectorAll('.mod-calendar.is-open').forEach(c => c.classList.remove('is-open'));
});

/* Helper: set calendar value from JS */
function setModCalendar(idOrEl, value) {
    const root = typeof idOrEl === 'string' ? document.getElementById(idOrEl) : idOrEl;
    if (!root || !root.classList.contains('mod-calendar')) return;
    const input = root.querySelector('input[type="hidden"]');
    const text  = root.querySelector('.mod-calendar-text');
    input.value = value;
    if (value) {
      const d = new Date(value+'T00:00:00');
      text.textContent = d.getDate()+' '+CAL_MONTHS[d.getMonth()].slice(0,3)+' '+d.getFullYear();
      root.classList.add('has-value');
    } else {
      text.textContent = root.dataset.placeholder || 'Select date';
      root.classList.remove('has-value');
    }
    input.dispatchEvent(new Event('change', { bubbles: true }));
}

/* ═══════════════════════════════════════════════════════
   CUSTOM TIME PICKER — Reusable
   ═══════════════════════════════════════════════════════ */
function initModTime(root) {
    if (root._timeInit) return;
    root._timeInit = true;
    root.setAttribute('data-time-init', '1');
    const input   = root.querySelector('input[type="hidden"]');
    const text    = root.querySelector('.mod-time-text');
    const panel   = root.querySelector('.mod-time-panel');
    const trigger = root.querySelector('.mod-time-trigger');
    let h = 12, m = 0, ap = 'AM';

    if (input.value) {
      const parts = input.value.split(':');
      h = parseInt(parts[0]) || 12;
      m = parseInt(parts[1]) || 0;
      ap = h >= 12 ? 'PM' : 'AM';
      if (h > 12) h -= 12;
      if (h === 0) h = 12;
    }

    function render() {
      panel.querySelectorAll('.tp-hour-scroll .tp-btn').forEach(b => b.classList.toggle('is-selected', parseInt(b.dataset.v) === h));
      panel.querySelectorAll('.tp-min-scroll .tp-btn').forEach(b => b.classList.toggle('is-selected', parseInt(b.dataset.v) === m));
      panel.querySelectorAll('.tp-ampm-btn').forEach(b => b.classList.toggle('is-selected', b.dataset.v === ap));
      panel.querySelector('.tp-hour-scroll .is-selected')?.scrollIntoView({ block: 'nearest' });
      panel.querySelector('.tp-min-scroll .is-selected')?.scrollIntoView({ block: 'nearest' });
    }
    function display() {
      text.textContent = String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0') + ' ' + ap;
    }
    function close() { root.classList.remove('is-open'); }
    function open() { render(); root.classList.add('is-open'); }
    function sync() {
      const hh24 = ap === 'PM' ? (h === 12 ? 12 : h + 12) : (h === 12 ? 0 : h);
      input.value = String(hh24).padStart(2,'0') + ':' + String(m).padStart(2,'0');
      display(); root.classList.add('has-value');
      input.dispatchEvent(new Event('change', { bubbles: true }));
    }

    trigger.addEventListener('click', (e) => { e.stopPropagation(); root.classList.contains('is-open') ? close() : open(); });
    panel.querySelectorAll('.tp-hour-scroll .tp-btn').forEach(btn => {
      btn.addEventListener('click', (e) => { e.stopPropagation(); h = parseInt(btn.dataset.v); render(); sync(); });
    });
    panel.querySelectorAll('.tp-min-scroll .tp-btn').forEach(btn => {
      btn.addEventListener('click', (e) => { e.stopPropagation(); m = parseInt(btn.dataset.v); render(); sync(); });
    });
    panel.querySelectorAll('.tp-ampm-btn').forEach(btn => {
      btn.addEventListener('click', (e) => { e.stopPropagation(); ap = btn.dataset.v; render(); sync(); });
    });
    panel.querySelector('.tp-now')?.addEventListener('click', (e) => {
      e.stopPropagation();
      const now = new Date();
      h = now.getHours(); ap = h >= 12 ? 'PM' : 'AM'; if (h > 12) h -= 12; if (h === 0) h = 12;
      m = now.getMinutes();
      render(); sync(); close();
    });
    panel.addEventListener('click', (e) => e.stopPropagation());
    root.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
    if (input.value) { display(); root.classList.add('has-value'); }
}

document.addEventListener('click', () => {
    document.querySelectorAll('.mod-time.is-open').forEach(c => c.classList.remove('is-open'));
});

function setModTime(idOrEl, value) {
    const root = typeof idOrEl === 'string' ? document.getElementById(idOrEl) : idOrEl;
    if (!root || !root.classList.contains('mod-time')) return;
    const input = root.querySelector('input[type="hidden"]');
    const text  = root.querySelector('.mod-time-text');
    input.value = value;
    if (value) {
      const parts = value.split(':');
      let hv = parseInt(parts[0]) || 0, mv = parseInt(parts[1]) || 0;
      const apv = hv >= 12 ? 'PM' : 'AM';
      if (hv > 12) hv -= 12; if (hv === 0) hv = 12;
      text.textContent = String(hv).padStart(2,'0') + ':' + String(mv).padStart(2,'0') + ' ' + apv;
      root.classList.add('has-value');
    } else {
      text.textContent = root.dataset.placeholder || 'Select time';
      root.classList.remove('has-value');
    }
    input.dispatchEvent(new Event('change', { bubbles: true }));
}
</script>

<script src="assets/js/main.js"></script>
<script src="assets/js/charts.js"></script>
</body>
</html>
