/**
 * Contact Page Logic
 */

export const initContactPage = () => {
    const form = document.getElementById('laca-contact-form');
    if (!form) return;

    const siteKey = form.getAttribute('data-sitekey');
    const theme = document.documentElement.getAttribute('data-theme');
    const isDark = theme === 'dark';

    // 1. Initial Status Check
    fetch(themeData.ajaxurl + '?action=laca_check_submission_status')
        .then(response => response.json())
        .then(res => {
            if (res.success && res.data.submitted) {
                Swal.fire({
                    title: 'Chào bạn quay lại!',
                    text: res.data.message,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Tôi muốn gửi tiếp',
                    cancelButtonText: 'Đã rõ',
                    background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1a1a1a' : '#fff',
                    color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#fff' : '#000',
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.setAttribute('data-resubmit', 'true');
                    }
                });
            }
        });

    // 2. Form Submission Handling
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;

        // Get reCAPTCHA token
        grecaptcha.ready(function() {
            grecaptcha.execute(siteKey, {action: 'contact'}).then(function(token) {
                document.getElementById('recaptcha-response').value = token;
                
                // Prepare Data
                const formData = new FormData(form);
                if (form.getAttribute('data-resubmit') === 'true') {
                    formData.append('resubmit_confirmed', 'true');
                }

                // Send AJAX
                fetch(themeData.ajaxurl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(res => {
                    submitBtn.classList.remove('loading');
                    submitBtn.disabled = false;

                    if (res.success) {
                        Swal.fire({
                            title: 'Thành công!',
                            text: res.data.message,
                            icon: 'success',
                            background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1a1a1a' : '#fff',
                            color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#fff' : '#000',
                        });
                        form.reset();
                    } else {
                        // Handle recently submitted error specifically
                        if (res.data && res.data.code === 'recently_submitted') {
                            Swal.fire({
                                title: 'Thông báo',
                                text: res.data.message,
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Tôi vẫn muốn gửi tiếp',
                                cancelButtonText: 'Để sau vậy',
                                background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1a1a1a' : '#fff',
                                color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#fff' : '#000',
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    form.setAttribute('data-resubmit', 'true');
                                    Swal.fire({
                                        text: 'Bạn đã có thể gửi lại lời nhắn.',
                                        icon: 'success',
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                }
                            });
                        } else {
                            Swal.fire({
                                title: 'Thất bại!',
                                text: res.data.message || 'Đã có lỗi xảy ra.',
                                icon: 'error',
                                background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1a1a1a' : '#fff',
                                color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#fff' : '#000',
                            });
                        }
                    }
                })
                .catch(err => {
                    submitBtn.classList.remove('loading');
                    submitBtn.disabled = false;
                    Swal.fire('Lỗi!', 'Không thể kết nối đến máy chủ.', 'error');
                });
            });
        });
    });
};
