<?php
/**
 * Unified Page Header / Hero section for all pages and archives
 * 
 * @package LacaDev
 */

$title = '';
$excerpt = '';

if (is_archive()) {
    if (is_post_type_archive()) {
        $title = post_type_archive_title('', false);
    } else {
        $title = get_the_archive_title();
    }
    $excerpt = get_the_archive_description();
} else {
    $title = get_the_title();
    if (has_excerpt()) {
        $excerpt = get_the_excerpt();
    }
}

// Special case for Service Archive if we want custom text
if (is_post_type_archive('service') && empty($excerpt)) {
    $excerpt = __('Khám phá các giải pháp công nghệ chuyên sâu giúp bạn bứt phá hành trình số.', 'laca');
}
$title_class = 'page-title';
if (is_post_type_archive('service')) {
    $title_class .= ' block-title-scroll';
}
?>

<div class="page-hero">
    <div class="container">
        <div class="hero-content" data-aos="fade-up">
            <h1 class="<?php echo esc_attr($title_class); ?>"><?php echo esc_html($title); ?></h1>
            <?php if ($excerpt) : ?>
                <div class="page-excerpt">
                    <?php echo wp_kses_post($excerpt); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
