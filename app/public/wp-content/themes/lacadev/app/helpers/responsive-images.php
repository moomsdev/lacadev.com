<?php
/**
 * Responsive Image Helper Functions
 * 
 * Wrapper functions for wp_get_attachment_image() to provide
 * automatic responsive images with srcset and sizes attributes.
 */

// =============================================================================
// RESPONSIVE IMAGE FUNCTIONS (NEW - RECOMMENDED)
// =============================================================================

/**
 * Echo responsive post thumbnail
 * 
 * @param string $size Image size name (mobile, tablet, full)
 * @param array $attr Additional attributes
 */
function theResponsivePostThumbnail($size = 'mobile', $attr = []) {
    $image_id = get_post_thumbnail_id();
    if ($image_id) {
        echo wp_get_attachment_image($image_id, $size, false, $attr);
    }
}

/**
 * Get responsive post thumbnail HTML
 * 
 * @param int|null $post_id Post ID
 * @param string $size Image size name
 * @param array $attr Additional attributes
 * @return string HTML img tag with srcset
 */
function getResponsivePostThumbnail($post_id = null, $size = 'mobile', $attr = []) {
    $post_id = $post_id ?: get_the_ID();
    $image_id = get_post_thumbnail_id($post_id);
    
    if (!$image_id) {
        // Return default image if set
        $default_id = getOption('default_image');
        if ($default_id) {
            return wp_get_attachment_image($default_id, $size, false, $attr);
        }
        return '';
    }
    
    return wp_get_attachment_image($image_id, $size, false, $attr);
}

/**
 * Echo responsive image from post meta
 * 
 * @param string $meta_key Carbon Fields meta key
 * @param string $size Image size name
 * @param array $attr Additional attributes
 */
function theResponsivePostMeta($meta_key, $size = 'mobile', $attr = []) {
    $image_id = carbon_get_post_meta(get_the_ID(), $meta_key);
    if ($image_id) {
        echo wp_get_attachment_image($image_id, $size, false, $attr);
    }
}

/**
 * Get responsive image from post meta
 * 
 * @param string $meta_key Carbon Fields meta key
 * @param int|null $post_id Post ID
 * @param string $size Image size name
 * @param array $attr Additional attributes
 * @return string HTML img tag with srcset
 */
function getResponsivePostMeta($meta_key, $post_id = null, $size = 'mobile', $attr = []) {
    $post_id = $post_id ?: get_the_ID();
    $image_id = carbon_get_post_meta($post_id, $meta_key);
    
    if (!$image_id) {
        return '';
    }
    
    return wp_get_attachment_image($image_id, $size, false, $attr);
}

/**
 * Echo responsive image from theme option
 * 
 * @param string $option_key Carbon Fields option key
 * @param string $size Image size name
 * @param array $attr Additional attributes
 */
function theResponsiveOption($option_key, $size = 'mobile', $attr = []) {
    $image_id = carbon_get_theme_option($option_key);
    if ($image_id) {
        echo wp_get_attachment_image($image_id, $size, false, $attr);
    }
}

/**
 * Get responsive image from theme option
 * 
 * @param string $option_key Carbon Fields option key
 * @param string $size Image size name
 * @param array $attr Additional attributes
 * @return string HTML img tag with srcset
 */
function getResponsiveOption($option_key, $size = 'mobile', $attr = []) {
    $image_id = carbon_get_theme_option($option_key);
    
    if (!$image_id) {
        return '';
    }
    
    return wp_get_attachment_image($image_id, $size, false, $attr);
}

/**
 * Echo responsive image by attachment ID
 * 
 * @param int $attachment_id Attachment ID
 * @param string $size Image size name
 * @param array $attr Additional attributes
 */
function theResponsiveImage($attachment_id, $size = 'mobile', $attr = []) {
    if ($attachment_id) {
        echo wp_get_attachment_image($attachment_id, $size, false, $attr);
    }
}

/**
 * Get responsive image by attachment ID
 * 
 * @param int $attachment_id Attachment ID
 * @param string $size Image size name
 * @param array $attr Additional attributes
 * @return string HTML img tag with srcset
 */
function getResponsiveImage($attachment_id, $size = 'mobile', $attr = []) {
    if (!$attachment_id) {
        return '';
    }
    
    return wp_get_attachment_image($attachment_id, $size, false, $attr);
}

// =============================================================================
// USAGE EXAMPLES
// =============================================================================

/*
// Example 1: Post thumbnail
<?php theResponsivePostThumbnail('mobile', ['class' => 'post-thumb', 'loading' => 'lazy']); ?>

// Example 2: Post meta image
<?php theResponsivePostMeta('gallery_image', 'tablet'); ?>

// Example 3: Theme option image
<?php theResponsiveOption('site_logo', 'full'); ?>

// Example 4: Direct attachment ID
<?php theResponsiveImage($image_id, 'mobile', ['alt' => 'Custom alt text']); ?>

// Available sizes:
// - 'mobile' (480px)
// - 'mobile-2x' (960px)
// - 'tablet' (768px)
// - 'tablet-2x' (1536px)
// - 'full' (original)
*/
