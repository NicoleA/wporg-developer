<?php namespace DevHub; ?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<h1><a href="<?php the_permalink() ?>"><?php echo get_signature(); ?></a></h1>
	<section class="description">
		<?php the_excerpt(); ?>
	</section>

	<?php if ( is_single() ) : ?>

	<section class="long-description">
		<?php the_content(); ?>
	</section>

	<?php
	$since = get_since();
	if ( ! empty( $since ) ) : ?>
		<section class="since">
			<p><strong><?php _e( 'Since:</strong> WordPress' ); ?> <a href="<?php echo get_since_link( $since ); ?>"><?php echo esc_html( $since ); ?></a></p>
		</section>
	<?php endif; ?>

	<?php
	$source_file = get_source_file();
	if ( ! empty( $source_file ) ) :
		?>
		<section class="source">
			<p>
				<strong><?php _e( 'Source file:', 'wporg' ); ?></strong>
				<a href="<?php echo get_source_file_archive_link( $source_file ); ?>"><?php echo esc_html( $source_file ); ?></a>
			</p>
			<p>
				<a href="<?php echo get_source_file_link(); ?>"><?php _e( 'View on Trac', 'wporg' ); ?></a>
			</p>
		</section>
	<?php endif; ?>

		<?php /*
		<?php if ( is_archive() ) : ?>
		<section class="meta">Used by TODO | Uses TODO | TODO Examples</section>
		<?php endif; ?>
		<hr/>
		<section class="explanation">
			<h2><?php _e( 'Explanation', 'wporg' ); ?></h2>
		</section>
		*/ ?>

		<?php if ( $params = get_params() ) : ?>

			<hr/>
			<section class="parameters">
				<h2><?php _e( 'Parameters', 'wporg' ); ?></h2>
				<dl>
					<?php foreach ( $params as $param ) : ?>
						<?php if ( ! empty( $param['variable'] ) ) : ?>
						<dt><?php echo esc_html( $param['variable'] ); ?></dt>
						<?php endif; ?>
						<dd>
							<p class="desc">
								<?php if ( ! empty( $param['types'] ) ) : ?>
									<span class="type">(<?php echo wp_kses_post( $param['types'] ); ?>)</span>
								<?php endif; ?>

								<?php if ( ! empty( $param['content'] ) ) : ?>
									<span class="description"><?php echo wp_kses_post( $param['content'] ); ?></span>
								<?php endif; ?>
							</p>

							<?php if ( ! empty( $param['default'] ) ) : ?>
								<p class="default"><?php _e( 'Default value:', 'wporg' );?> <?php echo esc_html( $param['default'] ); ?></p>
							<?php endif; ?>
						</dd>
					<?php endforeach; ?>
				</dl>
			</section>
		<?php endif; ?>

		<?php /*
		<hr/>
		<section class="learn-more">
			<h2><?php _e( 'Learn More', 'wporg' ); ?></h2>
		</section>
		*/ ?>

		<?php if ( comments_open() || '0' != get_comments_number() ) : ?>
		<hr/>
		<section class="examples">
			<h2><?php _e( 'Examples', 'wporg' ); ?></h2>
			<?php comments_template(); /* TODO: add '/examples.php' */ ?>
		</section>
		<?php endif; ?>

	<?php endif; ?>

</article>
