<?php
/**
 * Code Reference redirects.
 *
 * @package wporg-developer
 */

/**
 * Class to handle redirects.
 */
class DevHub_Redirects {

	/**
	 * Initializer
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'do_init' ) );
	}

	/**
	 * Handles adding/removing hooks to perform redirects as needed.
	 */
	public static function do_init() {
		add_action( 'template_redirect', array( __CLASS__, 'redirect_single_search_match' ) );
		add_action( 'template_redirect', array( __CLASS__, 'redirect_handbook' ) );
		add_action( 'template_redirect', array( __CLASS__, 'redirect_resources' ) );
		add_action( 'template_redirect', array( __CLASS__, 'redirect_pluralized_handbooks' ), 1 );
		add_action( 'template_redirect', array( __CLASS__, 'redirect_pluralized_reference_post_types' ), 1 );
	}

	/**
	 * Redirects a search query with only one result directly to that result.
	 */
	public static function redirect_single_search_match() {
		if ( is_search() && 1 == $GLOBALS['wp_query']->found_posts ) {
			wp_redirect( get_permalink( get_post() ) );
			exit();
		}
	}

	/**
	 * Redirects a naked handbook request to home.
	 */
	public static function redirect_handbook() {
		if (
			// Naked /handbook/ request
			( 'handbook' == get_query_var( 'name' ) && ! get_query_var( 'post_type' ) ) ||
			// Temporary: Disable access to handbooks unless a member of the site
			( ! is_user_member_of_blog() && is_post_type_archive( array( 'plugin-handbook', 'theme-handbook' ) ) )
		) {
			wp_redirect( home_url() );
			exit();
		}
	}

	/**
	 * Redirects a naked /resources/ request to dashicons page.
	 *
	 * Temporary until a resource page other than dashicons is created.
	 */
	public static function redirect_resources() {
		if ( is_page( 'resources' ) ) {
			wp_redirect( get_permalink( get_page_by_title( 'dashicons' ) ) );
			exit();
		}
	}

	/**
	 * Redirects requests for the pluralized slugs of the handbooks.
	 *
	 * Note: this is a convenience redirect of just the naked slugs and not a
	 * fix for any deployed links.
	 */
	public static function redirect_pluralized_handbooks() {
		$name = get_query_var( 'name' );

		// '/plugins' => '/plugin'
		if ( 'plugins' == $name ) {
			wp_redirect( get_post_type_archive_link( 'plugin-handbook' ), 301 );
			exit();
		}

		// '/themes' => '/theme'
		if ( 'themes' == $name ) {
			wp_redirect( get_post_type_archive_link( 'theme-handbook' ), 301 );
			exit();
		}
	}

	/**
	 * Redirects requests for the pluralized slugs of the code reference parsed
	 * post types.
	 *
	 * Note: this is a convenience redirect and not a fix for any officially
	 * deployed links.
	 */
	public static function redirect_pluralized_reference_post_types() {
		$path = trailingslashit( $_SERVER['REQUEST_URI'] );

		$post_types = array(
			'class'    => 'classes',
			'function' => 'functions',
			'hook'     => 'hooks',
			'method'   => 'methods',
		);

		// '/reference/$singular(/*)?' => '/reference/$plural(/*)?'
		foreach ( $post_types as $post_type_slug_singular => $post_type_slug_plural ) {
			if ( 0 === stripos( $path, "/reference/{$post_type_slug_singular}/" ) ) {
				$path = "/reference/{$post_type_slug_plural}/" . substr( $path, strlen( "/reference/{$post_type_slug_singular}/" ) );
				wp_redirect( $path, 301 );
				exit();
			}
		}
	}

} // DevHub_Redirects

DevHub_Redirects::init();
