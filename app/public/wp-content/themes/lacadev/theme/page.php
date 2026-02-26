<?php
/**
 * App Layout: layouts/app.php
 *
 * This is the template that is used for displaying all pages by default.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WPEmergeTheme
 */
?>
<?php
if (!is_front_page() && is_page()):
    echo get_template_part('template-parts/breadcrumb');
endif;

if (is_front_page()):
    the_content();
else:
    ?>
		<div class="wrapper-content">
			<h1 class="single-title screen-reader-text"><?php the_title(); ?></h1>
			<?php
			the_content();
			?>
		</div>
    <?php
endif;
?>