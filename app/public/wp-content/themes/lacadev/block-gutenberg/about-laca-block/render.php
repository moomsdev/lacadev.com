<?php
$content = !empty($attributes['content']) ? $attributes['content'] : '';
$bg_image_url = !empty($attributes['bgImageUrl']) ? $attributes['bgImageUrl'] : '';
$class_name = 'block-about-laca';
if (!empty($attributes['className'])) {
    $class_name .= ' ' . $attributes['className'];
}

// Lấy ID ảnh nếu có để dùng srcset
$bg_image_id = !empty($attributes['bgImageId']) ? $attributes['bgImageId'] : 0;
?>

<section class="<?php echo esc_attr($class_name); ?>" id="about-laca-hero">
    <div class="img-container">
        <?php if ($bg_image_url) : ?>
            <div class="parallax-bg" style="background-image: url('<?php echo esc_url($bg_image_url); ?>');"></div>
        <?php endif; ?>
        
        <div class="container container--narrow">
            <div class="content-wrapper">
                <div class="about-content">
                    <?php echo nl2br(wp_kses_post($content)); ?>
                </div>
            </div>
        </div>
    </div>
</section>
