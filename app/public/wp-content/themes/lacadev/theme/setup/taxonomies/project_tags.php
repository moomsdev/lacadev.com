<?php
/**
 * Register custom taxonomies.
 *
 * @link https://developer.wordpress.org/reference/functions/register_taxonomy/
 *
 * @hook    init
 * @package WPEmergeTheme
 */

use Carbon_Fields\Container;
use Carbon_Fields\Field\Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Custom hierarchical taxonomy (like categories).
// phpcs:disable
add_action('init', function () {
	register_taxonomy(
		'project_tags',
		array( 'project' ),
		array(
			'hierarchical'  => false,
			'label'         => __( 'Tags', 'laca' ),
			'singular_name' => __( 'Project Tags', 'laca' ),
			'rewrite'       => true,
			'query_var'     => true
		)
	);
});
// phpcs:enable