// js/main.js
// Vanilla JavaScript for School Management System

document.addEventListener('DOMContentLoaded', function () {

    // ── Sidebar Toggle (mobile) ──────────────────────────────────
    const sidebarToggle  = document.getElementById('sidebarToggle');
    const sidebar        = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('open');
            sidebarOverlay.classList.toggle('open');
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function () {
            sidebar.classList.remove('open');
            sidebarOverlay.classList.remove('open');
        });
    }

    // ── Auto-dismiss Flash Messages ──────────────────────────────
    const flashMsg = document.getElementById('flashMsg');
    if (flashMsg) {
        setTimeout(function () {
            flashMsg.style.transition = 'opacity 0.5s ease';
            flashMsg.style.opacity = '0';
            setTimeout(() => flashMsg.remove(), 500);
        }, 4000);
    }

    // ── Live Table Search ────────────────────────────────────────
    const searchInput = document.getElementById('tableSearch');
    const dataTable   = document.getElementById('dataTable');

    if (searchInput && dataTable) {
        searchInput.addEventListener('input', function () {
            const query  = this.value.toLowerCase().trim();
            const rows   = dataTable.querySelectorAll('tbody tr');
            let   visible = 0;

            rows.forEach(function (row) {
                const text = row.textContent.toLowerCase();
                if (text.includes(query)) {
                    row.style.display = '';
                    visible++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Show "no results" row if needed
            const noResultsRow = document.getElementById('noResultsRow');
            if (noResultsRow) {
                noResultsRow.style.display = visible === 0 ? '' : 'none';
            }
        });
    }

    // ── Delete Confirmation ──────────────────────────────────────
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            const msg = this.dataset.confirm || 'Are you sure you want to delete this record?';
            if (!confirm(msg)) {
                e.preventDefault();
                return false;
            }
        });
    });

    // ── Form Validation ──────────────────────────────────────────
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            let valid = true;

            // Clear previous errors
            form.querySelectorAll('.field-error').forEach(el => el.remove());
            form.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));

            const required = form.querySelectorAll('[required]');
            required.forEach(function (field) {
                if (!field.value.trim()) {
                    valid = false;
                    field.classList.add('input-error');
                    const err = document.createElement('span');
                    err.className = 'field-error';
                    err.textContent = field.dataset.errMsg || 'This field is required.';
                    field.parentNode.appendChild(err);
                }
            });

            // Phone number format check
            const phones = form.querySelectorAll('input[type="tel"]');
            phones.forEach(function (field) {
                if (field.value && !/^[0-9+\-\s]{7,15}$/.test(field.value.trim())) {
                    valid = false;
                    field.classList.add('input-error');
                    const err = document.createElement('span');
                    err.className = 'field-error';
                    err.textContent = 'Enter a valid phone number.';
                    field.parentNode.appendChild(err);
                }
            });

            // Email format check
            const emails = form.querySelectorAll('input[type="email"]');
            emails.forEach(function (field) {
                if (field.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value.trim())) {
                    valid = false;
                    field.classList.add('input-error');
                    const err = document.createElement('span');
                    err.className = 'field-error';
                    err.textContent = 'Enter a valid email address.';
                    field.parentNode.appendChild(err);
                }
            });

            // Marks validation (0-100)
            const marksFields = form.querySelectorAll('input[data-marks]');
            marksFields.forEach(function (field) {
                const val = parseFloat(field.value);
                const max = parseFloat(field.dataset.max || 100);
                if (field.value !== '' && (isNaN(val) || val < 0 || val > max)) {
                    valid = false;
                    field.classList.add('input-error');
                    const err = document.createElement('span');
                    err.className = 'field-error';
                    err.textContent = `Marks must be between 0 and ${max}.`;
                    field.parentNode.appendChild(err);
                }
            });

            if (!valid) {
                e.preventDefault();
                // Scroll to first error
                const firstError = form.querySelector('.input-error');
                if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    });

    // ── Attendance Toggle Buttons ────────────────────────────────
    document.querySelectorAll('.att-toggle').forEach(function (group) {
        const presentBtn = group.querySelector('.att-present');
        const absentBtn  = group.querySelector('.att-absent');
        const hiddenInput = group.previousElementSibling; // hidden input

        if (presentBtn) {
            presentBtn.addEventListener('click', function () {
                presentBtn.classList.add('present-active');
                absentBtn.classList.remove('absent-active');
                if (hiddenInput) hiddenInput.value = 'Present';
            });
        }

        if (absentBtn) {
            absentBtn.addEventListener('click', function () {
                absentBtn.classList.add('absent-active');
                presentBtn.classList.remove('present-active');
                if (hiddenInput) hiddenInput.value = 'Absent';
            });
        }
    });

    // ── Live Grade Preview (marks input) ────────────────────────
    document.querySelectorAll('input[data-marks]').forEach(function (input) {
        const gradeSpan = document.getElementById('grade_' + input.dataset.studentId);
        if (!gradeSpan) return;

        input.addEventListener('input', function () {
            const val = parseFloat(this.value);
            if (isNaN(val) || this.value === '') {
                gradeSpan.textContent = '-';
                gradeSpan.className = 'badge';
            } else {
                const grade = calcGrade(val);
                gradeSpan.textContent = grade;
                gradeSpan.className   = 'badge grade-badge ' + 'grade-' + grade.toLowerCase();
            }
        });
    });

    function calcGrade(marks) {
        if (marks >= 80) return 'A';
        if (marks >= 70) return 'B';
        if (marks >= 60) return 'C';
        if (marks >= 50) return 'D';
        return 'E';
    }

    // ── Live Clock in Header ─────────────────────────────────────
    const clockEl = document.getElementById('liveClock');
    if (clockEl) {
        function updateClock() {
            const now    = new Date();
            const opts   = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
            const dateStr = now.toLocaleDateString('en-KE', opts);
            const timeStr = now.toLocaleTimeString('en-KE', { hour: '2-digit', minute: '2-digit' });
            clockEl.textContent = dateStr + '  •  ' + timeStr;
        }
        updateClock();
        setInterval(updateClock, 1000);
    }

    // ── Mark All Present / Absent Buttons ───────────────────────
    const markAllPresent = document.getElementById('markAllPresent');
    const markAllAbsent  = document.getElementById('markAllAbsent');

    if (markAllPresent) {
        markAllPresent.addEventListener('click', function () {
            document.querySelectorAll('.att-present').forEach(btn => btn.click());
        });
    }

    if (markAllAbsent) {
        markAllAbsent.addEventListener('click', function () {
            document.querySelectorAll('.att-absent').forEach(btn => btn.click());
        });
    }

    // ── Active nav link highlight ────────────────────────────────
    const currentPath = window.location.pathname;
    document.querySelectorAll('.sidebar-nav a').forEach(function (link) {
        if (link.getAttribute('href') && currentPath.includes(link.getAttribute('href').replace('../', ''))) {
            link.classList.add('active');
        }
    });

});

// Field error style (injected dynamically)
const style = document.createElement('style');
style.textContent = `
    .input-error { border-color: #dc2626 !important; box-shadow: 0 0 0 3px rgba(220,38,38,0.1) !important; }
    .field-error { display: block; color: #dc2626; font-size: 0.75rem; margin-top: 4px; }
`;
document.head.appendChild(style);
