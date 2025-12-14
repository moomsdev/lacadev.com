<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Image Optimization & WebP Support
 * 
 * @package LacaDev
 */

/**
 * Register custom image sizes for responsive images
 */
add_action('after_setup_theme', function() {
    // Mobile sizes
    add_image_size('mobile', 480, 9999, false);
    add_image_size('mobile-2x', 960, 9999, false);
    
    // Tablet sizes
    add_image_size('tablet', 768, 9999, false);
    add_image_size('tablet-2x', 1536, 9999, false);
    
    // Desktop sizes
    add_image_size('desktop', 1200, 9999, false);
    add_image_size('desktop-2x', 2400, 9999, false);
    
    // Thumbnail variations
    add_image_size('thumb-small', 150, 150, true);
    add_image_size('thumb-medium', 300, 300, true);
    add_image_size('thumb-large', 600, 600, true);
});

/**
 * Enable WebP support in WordPress
 */
add_filter('mime_types', function($mimes) {
    $mimes['webp'] = 'image/webp';
    return $mimes;
});

/**
 * Add WebP to allowed upload file types
 */
add_filter('upload_mimes', function($mimes) {
    $mimes['webp'] = 'image/webp';
    return $mimes;
});

/**
 * Display WebP images correctly in media library
 */
add_filter('file_is_displayable_image', function($result, $path) {
    if ($result === false) {
        $info = @getimagesize($path);
        if (isset($info['mime']) && $info['mime'] === 'image/webp') {
            $result = true;
        }
    }
    return $result;
}, 10, 2);

/**
 * Auto-generate WebP version on image upload
 * Note: Requires GD or Imagick with WebP support
 */
add_filter('wp_generate_attachment_metadata', function($metadata, $attachment_id) {
    $file = get_attached_file($attachment_id);
    
    // Only process images
    if (!wp_attachment_is_image($attachment_id)) {
        return $metadata;
    }
    
    // Check if server supports WebP
    if (!function_exists('imagewebp')) {
        return $metadata;
    }
    
    // Generate WebP for main image
    lacadev_generate_webp_image($file);
    
    // Generate WebP for all sizes
    if (isset($metadata['sizes']) && is_array($metadata['sizes'])) {
        $upload_dir = wp_upload_dir();
        $base_dir = dirname($file);
        
        foreach ($metadata['sizes'] as $size => $size_data) {
            $size_file = $base_dir . '/' . $size_data['file'];
            if (file_exists($size_file)) {
                lacadev_generate_webp_image($size_file);
            }
        }
    }
    
    return $metadata;
}, 10, 2);

/**
 * Helper function to generate WebP image
 */
function lacadev_generate_webp_image($file) {
    $file_path = $file;
    $file_info = pathinfo($file_path);
    
    // Skip if already WebP
    if (isset($file_info['extension']) && strtolower($file_info['extension']) === 'webp') {
        return false;
    }
    
    // WebP output path
    $webp_path = $file_info['dirname'] . '/' . $file_info['filename'] . '.webp';
    
    // Skip if WebP already exists
    if (file_exists($webp_path)) {
        return true;
    }
    
    // Load image based on type
    $image = false;
    $extension = strtolower($file_info['extension']);
    
    switch ($extension) {
        case 'jpg':
        case 'jpeg':
            $image = @imagecreatefromjpeg($file_path);
            break;
        case 'png':
            $image = @imagecreatefrompng($file_path);
            // Preserve transparency
            imagealphablending($image, false);
            imagesavealpha($image, true);
            break;
        case 'gif':
            $image = @imagecreatefromgif($file_path);
            break;
    }
    
    if ($image === false) {
        return false;
    }
    
    // Generate WebP with 85% quality (good balance)
    $result = imagewebp($image, $webp_path, 85);
    
    // Free memory
    imagedestroy($image);
    
    return $result;
}

/**
 * Add WebP source to images with <picture> element
 */
add_filter('wp_get_attachment_image', function($html, $attachment_id, $size, $icon, $attr) {
    // Skip if not an image
    if (!wp_attachment_is_image($attachment_id)) {
        return $html;
    }
    
    $image_url = wp_get_attachment_image_url($attachment_id, $size);
    if (!$image_url) {
        return $html;
    }
    
    // Get WebP URL
    $webp_url = preg_replace('/\.(jpg|jpeg|png|gif)$/i', '.webp', $image_url);
    
    // Check if WebP file exists
    $webp_path = str_replace(wp_upload_dir()['baseurl'], wp_upload_dir()['basedir'], $webp_url);
    
    if (file_exists($webp_path)) {
        // Wrap with picture element
        $html = '<picture>'
            . '<source srcset="' . esc_url($webp_url) . '" type="image/webp">'
            . $html
            . '</picture>';
    }
    
    return $html;
}, 10, 5);

/**
 * Add responsive srcset to images
 */
add_filter('wp_get_attachment_image_attributes', function($attr, $attachment, $size) {
    if (!wp_attachment_is_image($attachment->ID)) {
        return $attr;
    }
    
    // Generate srcset for responsive images
    $image_meta = wp_get_attachment_metadata($attachment->ID);
    
    if (!isset($image_meta['sizes']) || empty($image_meta['sizes'])) {
        return $attr;
    }
    
    $srcset = [];
    $sizes_config = [
        'mobile' => '480w',
        'mobile-2x' => '960w',
        'tablet' => '768w',
        'tablet-2x' => '1536w',
        'desktop' => '1200w',
        'desktop-2x' => '2400w',
    ];
    
    foreach ($sizes_config as $size_name => $width) {
        $url = wp_get_attachment_image_url($attachment->ID, $size_name);
        if ($url) {
            $srcset[] = esc_url($url) . ' ' . $width;
        }
    }
    
    if (!empty($srcset)) {
        $attr['srcset'] = implode(', ', $srcset);
        $attr['sizes'] = '(max-width: 480px) 480px, (max-width: 768px) 768px, (max-width: 1200px) 1200px, 2400px';
    }
    
    return $attr;
}, 10, 3);

/**
 * Lazy load images by default
 */
add_filter('wp_get_attachment_image_attributes', function($attr, $attachment, $size) {
    // Add loading="lazy" for better performance
    if (!isset($attr['loading'])) {
        $attr['loading'] = 'lazy';
    }
    
    // Add decoding="async" for non-blocking
    if (!isset($attr['decoding'])) {
        $attr['decoding'] = 'async';
    }
    
    return $attr;
}, 10, 3);

/**
 * Optimize image quality on upload
 */
add_filter('jpeg_quality', function($quality) {
    return 85; // Good balance between quality and file size
});

add_filter('wp_editor_set_quality', function($quality) {
    return 85;
});
