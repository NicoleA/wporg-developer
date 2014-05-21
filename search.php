<?php namespace DevHub;
/**
 * The template for displaying Search Results pages.
 *
 * @package wporg-developer
 */

get_header(); ?>

	<div id="content-area">

		<header class="page-header">
			<h1 class="page-title"><?php printf( __( 'Search Results for: %s', 'wporg' ), '<span>' . get_search_query() . '</span>' ); ?></h1>
		</header><!-- .page-header -->
		
		<div class="topic-guide section">
			<ul class="unordered-list horizontal-list no-bullets">
				<?php $current_filter = remove_query_arg( 'paged' ) ?>
				<li><a href="<?php echo add_query_arg( 'type', 'wp-parser-function', $current_filter ); ?>"><?php _e( 'Functions', 'wporg' ); ?></a></li>
				<li><a href="<?php echo add_query_arg( 'type', 'wp-parser-hook', $current_filter ); ?>"><?php _e( 'Hooks', 'wporg' ); ?></a></li>
				<li><a href="<?php echo add_query_arg( 'type', 'wp-parser-class', $current_filter ); ?>"><?php _e( 'Classes', 'wporg' ); ?></a></li>
				<li><a href="<?php echo add_query_arg( 'type', 'wp-parser-method', $current_filter ); ?>"><?php _e( 'Methods', 'wporg' ); ?></a></li>
			</ul>
		</div><!-- /topic-guide -->
		
		<main id="main" class="site-main" role="main">

		<?php if ( have_posts() ) : ?>

			<?php /* Start the Loop */ ?>
			<?php while ( have_posts() ) : the_post(); ?>

				<?php get_template_part( 'content', 'reference' ); ?>

			<?php endwhile; ?>

			<?php loop_pagination(); ?>

		<?php else : ?>

			<?php get_template_part( 'content', 'none' ); ?>

		<?php endif; ?>

		</main><!-- #main -->
		<?php //get_sidebar(); ?>
	</div><!-- #primary -->
<?php get_footer(); ?>
