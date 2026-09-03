document.addEventListener('DOMContentLoaded', () => {
    // --- Mobile nav toggle (unchanged) ---
    const toggle = document.querySelector('.nav-toggle');
    const nav = document.querySelector('.main-nav');
    if (toggle && nav) {
        toggle.addEventListener('click', () => {
            const open = nav.classList.toggle('open');
            toggle.setAttribute('aria-expanded', open);
        });
    }

    // --- Generic AJAX form handling for every [data-demo-form] ---
    document.querySelectorAll('[data-demo-form]').forEach((form) => {
        const endpoint = form.dataset.endpoint;
        const note = form.querySelector('.form-note');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Clear previous errors/messages.
            form.querySelectorAll('.field-error').forEach((el) => { el.textContent = ''; });
            if (note) {
                note.textContent = '';
                note.classList.remove('form-note-error', 'form-note-success');
            }

            // No backend wired up for this form yet — keep the old demo behavior.
            if (!endpoint) {
                if (note) note.textContent = 'Thanks! This demo form is ready to connect to your PHP/database backend.';
                return;
            }

            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: new FormData(form),
                });

                let data;
                try {
                    data = await response.json();
                } catch (parseErr) {
                    throw new Error('Unexpected server response.');
                }

                if (data.success) {
                    if (note) {
                        note.textContent = data.message || 'Thank you!';
                        note.classList.add('form-note-success');
                    }
                    form.reset();
                } else {
                    if (data.errors) {
                        Object.keys(data.errors).forEach((field) => {
                            const el = form.querySelector(`[data-error-for="${field}"]`);
                            if (el) el.textContent = data.errors[field];
                        });
                    }
                    if (note) {
                        note.textContent = data.message || 'Please check the form and try again.';
                        note.classList.add('form-note-error');
                    }
                }
            } catch (err) {
                if (note) {
                    note.textContent = 'Network error — please try again in a moment.';
                    note.classList.add('form-note-error');
                }
            } finally {
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    });
});
