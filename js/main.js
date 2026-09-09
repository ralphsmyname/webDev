document.addEventListener('DOMContentLoaded', () => {

    // --- Mobile nav toggle ---
    const toggle = document.querySelector('.nav-toggle');
    const nav = document.querySelector('.main-nav');

    if (toggle && nav) {
        toggle.addEventListener('click', () => {
            const open = nav.classList.toggle('open');
            toggle.setAttribute('aria-expanded', open);
        });
    }

    // --- Generic AJAX form handling ---
    document.querySelectorAll('[data-demo-form]').forEach((form) => {

        const endpoint = form.dataset.endpoint;
        const note = form.querySelector('.form-note');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Clear previous errors
            form.querySelectorAll('.field-error').forEach((el) => {
                el.textContent = '';
            });

            if (note) {
                note.textContent = '';
                note.classList.remove(
                    'form-note-error',
                    'form-note-success'
                );
            }

            // No backend
            if (!endpoint) {
                if (note) {
                    note.textContent =
                        'Thanks! This demo form is ready to connect to your PHP/database backend.';
                }
                return;
            }

            const submitBtn = form.querySelector(
                'button[type="submit"]'
            );

            if (submitBtn) {
                submitBtn.disabled = true;
            }

            try {

                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: new FormData(form)
                });

                // Get the server response as text first
                const responseText = await response.text();

                console.log('Server response:', responseText);

                let data;

                try {
                    data = JSON.parse(responseText);
                } catch (parseErr) {

                    console.error(
                        'Invalid JSON response:',
                        responseText
                    );

                    if (note) {
                        note.textContent =
                            'The server returned an invalid response.';
                        note.classList.add('form-note-error');
                    }

                    return;
                }

                // --- SUCCESS ---
                if (data.success) {

                    if (note) {
                        note.textContent =
                            data.message ||
                            'Order placed successfully!';

                        note.classList.remove(
                            'form-note-error'
                        );

                        note.classList.add(
                            'form-note-success'
                        );
                    }

                    // Clear form
                    form.reset();

                }

                // --- VALIDATION / SERVER ERROR ---
                else {

                    if (data.errors) {

                        Object.keys(data.errors).forEach(
                            (field) => {

                                const el =
                                    form.querySelector(
                                        `[data-error-for="${field}"]`
                                    );

                                if (el) {
                                    el.textContent =
                                        data.errors[field];
                                }
                            }
                        );
                    }

                    if (note) {

                        note.textContent =
                            data.message ||
                            'Please check the form and try again.';

                        note.classList.remove(
                            'form-note-success'
                        );

                        note.classList.add(
                            'form-note-error'
                        );
                    }
                }

            } catch (err) {

                console.error(
                    'Order request error:',
                    err
                );

                if (note) {

                    note.textContent =
                        'Unable to process the order. Please try again.';

                    note.classList.remove(
                        'form-note-success'
                    );

                    note.classList.add(
                        'form-note-error'
                    );
                }

            } finally {

                if (submitBtn) {
                    submitBtn.disabled = false;
                }

            }

        });

    });

});