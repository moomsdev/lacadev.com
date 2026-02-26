<?php
/**
 * Template Name: Contact Page
 *
 * App Layout: layouts/app.php
 *
 * This is the template that is used for displaying 404 errors.
 *
 * @package WPEmergeTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$site_key = carbon_get_theme_option('recaptcha_site_key');
?>

<main id="main-content" class="contact-page-template">
    <div class="contact-hero">
        <div class="container">
            <div class="hero-content" data-aos="fade-up">
                <h1 class="page-title"><?php the_title(); ?></h1>
                <?php if (has_excerpt()) : ?>
                    <div class="page-excerpt"><?php the_excerpt(); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <section class="contact-section">
        <div class="container">
            <div class="contact-grid">
                <!-- Contact Info -->
                <div class="contact-info" data-aos="fade-right">
                    <div class="info-card glass">
                        <h2 class="section-title"><?php _e('Ping tôi tại đây', 'laca'); ?></h2>
                        <p class="section-desc"><?php _e('Bạn có ý tưởng mới, một dự án hay ho hay đơn giản là muốn chia sẻ một hành trình? Đừng ngần ngại, trạm luôn mở cửa đón chờ!', 'laca'); ?></p>
                        
                        <div class="info-items">
                            <?php 
                            $phone = getOption('phone_number');
                            $email = getOption('email');
                            $address = getOption('address');
                            ?>
                            
                            <?php if ($email) : ?>
                                <div class="info-item">
                                    <div class="icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                    </div>
                                    <div class="text">
                                        <label><?php _e('Email', 'laca'); ?></label>
                                        <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($phone) : ?>
                                <div class="info-item">
                                    <div class="icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                    </div>
                                    <div class="text">
                                        <label><?php _e('Điện thoại', 'laca'); ?></label>
                                        <a href="tel:<?php echo esc_attr(str_replace(['.', ' '], '', $phone)); ?>"><?php echo esc_html($phone); ?></a>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="info-item">
                                <div class="icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                </div>
                                <div class="text">
                                    <label><?php _e('Địa điểm', 'laca'); ?></label>
                                    <span><?php echo esc_html($address ?: 'Hanoi, Vietnam'); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="social-circles">
                            <?php 
                            $socials = ['facebook', 'linkedin', 'instagram', 'tiktok', 'youtube'];
                            foreach ($socials as $soc) :
                                $url = getOption($soc);
                                if ($url) : ?>
                                    <a href="<?php echo esc_url($url); ?>" target="_blank" class="social-circle">
                                        <span class="screen-reader-text"><?php echo ucfirst($soc); ?></span>
                                        <i class="fab fa-<?php echo $soc; ?>"></i>
                                    </a>
                                <?php endif;
                            endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="contact-form-wrapper" data-aos="fade-left">
                    <form id="laca-contact-form" class="glass" data-sitekey="<?php echo esc_attr($site_key); ?>">
                        <?php wp_nonce_field('laca_contact_nonce', 'nonce'); ?>
                        <input type="hidden" name="action" value="laca_contact_submit">
                        <input type="hidden" name="recaptcha_response" id="recaptcha-response">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="form-name"><?php _e('Bạn tên là gì?', 'laca'); ?> <span>*</span></label>
                                <input type="text" id="form-name" name="name" placeholder="<?php _e('Họ và tên của bạn', 'laca'); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="form-email"><?php _e('Email liên hệ', 'laca'); ?> <span>*</span></label>
                                <input type="email" id="form-email" name="email" placeholder="<?php _e('Để tôi có thể gửi phản hồi', 'laca'); ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="form-subject"><?php _e('Tiêu đề', 'laca'); ?></label>
                            <input type="text" id="form-subject" name="subject" placeholder="<?php _e('Tóm tắt ngắn gọn mục đích liên hệ', 'laca'); ?>">
                        </div>

                        <div class="form-group">
                            <label for="form-message"><?php _e('Nội dung', 'laca'); ?> <span>*</span></label>
                            <textarea id="form-message" name="message" rows="5" placeholder="<?php _e('Nơi bạn viết nên những ý tưởng hoặc lời nhắn gửi...', 'laca'); ?>" required></textarea>
                        </div>

                        <div class="form-submit">
                            <button type="submit" class="btn btn-primary">
                                <span class="text"><?php _e('Gửi lời nhắn', 'laca'); ?></span>
                                <span class="loader"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>