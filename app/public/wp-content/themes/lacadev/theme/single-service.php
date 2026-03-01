<?php
	/**
	 * App Layout: layouts/app.php
	 *
	 * This is the template that is used for displaying all posts by default.
	 *
	 * @link    https://codex.wordpress.org/Template_Hierarchy
	 *
	 * @package WPEmergeTheme
	 */

	theBreadcrumb();
?>

<article class="single-service-template">
	<!-- Hero Section -->
	<section class="service-hero">
		<div class="container">
			<h1 class="service-title"><?php the_title(); ?></h1>
			<div class="service-meta">
				<span class="meta-item"><?php _e('Dịch vụ chuyên nghiệp', 'laca'); ?></span>
				<span class="meta-separator">•</span>
				<span class="meta-item"><?php echo get_the_date(); ?></span>
			</div>
		</div>
	</section>

	<div class="container">
		<!-- Featured Image -->
		<?php if (has_post_thumbnail()) : ?>
			<div class="service-featured-image">
				<?php echo getResponsivePostThumbnail(get_the_ID(), 'full'); ?>
			</div>
		<?php endif; ?>

		<!-- Content Body -->
		<div class="service-body">
			<?php theContent(); ?>
		</div>
	</div>
</article>
			