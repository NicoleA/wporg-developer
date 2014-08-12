<?php
/**
 * Code Reference admin extras
 *
 * @package wporg-developer
 */

/**
 * Class to handle admin extras for the Function-, Class-, Hook-,
 * and Method-editing screens.
 */
class WPORG_Admin_Extras {

	/**
	 * Post types array.
	 *
	 * Includes the Code Reference post types.
	 *
	 * @access public
	 * @var array
	 */
	public $post_types;

	/**
	 * Constructor.
	 *
	 * @access public
	 */
	public function __construct() {
		$this->post_types = array( 'wp-parser-function', 'wp-parser-class', 'wp-parser-hook', 'wp-parser-method' );

		// Data.
		add_action( 'add_meta_boxes',                   array( $this, 'add_meta_boxes'        ) );
		add_action( 'save_post',                        array( $this, 'save_post'             ) );

		// Script.
//		add_action( 'admin_print_scripts-post-new.php', array( $this, 'admin_enqueue_scripts' ) );
//		add_action( 'admin_print_scripts-post.php',     array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts',            array( $this, 'admin_enqueue_scripts' ) );
		// AJAX
		add_action( 'wp_ajax_wporg_attach_ticket',      array( $this, 'attach_ticket'          ) );
		add_action( 'wp_ajax_wporg_detach_ticket',      array( $this, 'detach_ticket'          ) );
	}

	/**
	 * Add meta boxes.
	 *
	 * @access public
	 */
	public function add_meta_boxes() {
		if ( in_array( $screen = get_current_screen()->id, $this->post_types ) && current_user_can( 'manage_options' ) ) {
			add_meta_box( 'wporg_parsed_content', __( 'Manage Parsed Content', 'wporg' ), array( $this, 'parsed_meta_box_cb' ), $screen, 'normal' );
		}
	}

	/**
	 * Parsed content meta box display callback.
	 *
	 * @access public
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function parsed_meta_box_cb( $post ) {
		$ticket      = get_post_meta( $post->ID, 'wporg_parsed_ticket', true );
		$ticket_info = get_post_meta( $post->ID, 'wporg_parsed_ticket_info', true );
		$content     = get_post_meta( $post->ID, 'wporg_parsed_content', true );

		if ( ! $ticket_info ) {
			$link = sprintf( '<a href="https://core.trac.wordpress.org/newticket">%s</a>', __( 'Core Trac', 'wporg' ) );
			/* translators: 1: Meta Trac link. */
			$ticket_info = '<em>' . sprintf( __( 'A valid, open ticket from %s is required to edit parsed content.', 'wporg' ), $link ) . '</em>';
		}
		wp_nonce_field( 'wporg-parsed-content', 'wporg-parsed-content-nonce' );
		?>
		<style type="text/css">
			#wporg_editor_outer {
				display: none;
			}
			#wporg_parsed_ticket {
				width: 100px;
			}
			#ticket_info_icon {
				font-size: 14px;
			}
			#ticket_status .spinner {
				position: relative;
				bottom: 4px;
				float: none;
			}
			#ticket_info_icon {
				color: #a00;
			}
			.attachment_controls {
				margin-bottom: 10px;
				display: block;
			}
			#wporg_ticket_detach {
				display: none;
			}
		</style>

		<table class="form-table">
			<tbody>
			<tr valign="top" id="ticket_controls">
				<th scope="row">
					<label for="wporg_parsed_ticket"><?php _e( 'Trac Ticket Number:' ); ?></label>
				</th>
				<td>
					<span class="attachment_controls">
						<input type="text" name="wporg_parsed_ticket" id="wporg_parsed_ticket" value="<?php echo esc_attr( $ticket ); ?>" />
						<a href="#attach-ticket" class="button secondary" id="wporg_ticket_attach" name="wporg_ticket_attach" aria-label="<?php esc_attr_e( 'Attach a Core Trac ticket' ); ?>" data-nonce="<?php echo wp_create_nonce( 'wporg-attach-ticket' ); ?>">
							<?php esc_attr_e( 'Attach Ticket', 'wporg' ); ?>
						</a>
						<a href="#detach-ticket" class="button secondary" id="wporg_ticket_detach" name="wporg_ticket_detach" aria-label="<?php esc_attr_e( 'Detach the Trac ticket' ); ?>" data-nonce="<?php echo wp_create_nonce( 'wporg-detach-ticket' ); ?>">
							<?php esc_attr_e( 'Detach Ticket', 'wporg' ); ?>
						</a>
					</span>
					<div id="ticket_status">
						<span class="spinner"></span><span class="ticket_info_icon"></span>
						<span id="wporg_parsed_ticket_info"><?php echo $ticket_info; ?></span>
					</div>
				</td>
			</tr>
			<tr valign="top" id="wporg_editor_outer" data-id="<?php the_id(); ?>">
				<th scope="row">
					<label for="wporg_parsed_content"><?php _e( 'Parsed Content:' ); ?></label>
				</th>
				<td>
					<?php
					wp_editor( $content, 'wporg_parsed_content_editor', array(
						'media_buttons' => false,
						'tinymce'       => false,
						'quicktags'     => true,
						'textarea_rows' => 10,
						'textarea_name' => 'wporg_parsed_content'
					) );
					?>
				</td>
			</tr>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Handle saving post content extras.
	 *
	 * @access public
	 *
	 * @param int $post_id Post ID.
	 */
	public function save_post( $post_id ) {
		// Only admins can edit parsed content.
		if ( current_user_can( 'manage_options' ) && wp_verify_nonce( $_POST['wporg-parsed-content-nonce'], 'wporg-parsed-content' ) ) {
			// Ticket number.
			if ( empty( $_POST['wporg_parsed_ticket'] ) ) {
				delete_post_meta( $post_id, 'wporg_parsed_ticket' );
			} else {
				update_post_meta( $post_id, 'wporg_parsed_ticket', sanitize_text_field( $_POST['wporg_parsed_ticket'] ) );
			}

			// Parsed content.
			if ( empty( $_POST['wporg_parsed_content'] ) ) {
				delete_post_meta( $post_id, 'wporg_parsed_content' );
			} else {
				update_post_meta( $post_id, 'wporg_parsed_content', wp_kses_post( $_POST['wporg_parsed_content'] ) );
			}
		}
	}

	/**
	 * Enqueue JS on the post-edit and post-new screens.
	 *
	 * @access public
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_script( 'wporg-admin-extras', get_template_directory_uri() . '/js/admin-extras.js', array( 'jquery', 'utils' ), '1.0', true );

		// Only enqueue 'wporg-admin-extras' on Code Reference post type screens.
//		if ( in_array( get_current_screen()->id, $this->post_types ) ) {
			wp_localize_script( 'wporg-admin-extras', 'wporg', array(
				'ajaxURL' => admin_url( 'admin-ajax.php' ),
				'searchText' => __( 'Searching ...', 'wporg' ),
			) );
//			wp_enqueue_script( 'admin-extras' );
//		}
	}

	/**
	 * AJAX handler for fetching a Core Trac ticket.
	 *
	 * @access public
	 */
	public function attach_ticket() {
		check_ajax_referer( 'wporg-attach-ticket', 'nonce' );

		$ticket = empty( $_REQUEST['ticket'] ) ? 0 : absint( $_REQUEST['ticket'] );
		$ticket = "https://core.trac.wordpress.org/ticket/{$ticket}";

		// Fetch the ticket.
		$resp        = wp_remote_get( $ticket );
		$status_code = wp_remote_retrieve_response_code( $resp );
		$body        = wp_remote_retrieve_body( $resp );

		// Anything other than 200 is invalid.
		if ( 200 === $status_code && null !== $body ) {
			$title = '';

			if ( class_exists( 'DOMDocument' ) ) {
				$doc = new DOMDocument();
				$doc->loadHTML( $body );

				$nodes = $doc->getElementsByTagName( 'title' );
				$title = $nodes->item(0)->nodeValue;

				// Strip off the site name.
				$title = str_ireplace( ' – WordPress Trac', '', $title );
			} else {
				die( -1 );
			}

			$message = array(
				'type'    => 'success',
				'message' => $title,
			);

			$post_id = empty( $_REQUEST['post_id'] ) ? 0 : absint( $_REQUEST['post_id'] );

			update_post_meta( $post_id, 'wporg_ticket_url', $ticket );
			update_post_meta( $post_id, 'wporg_ticket_title', $title );

		} else {
			$message = array(
				'type'    => 'invalid',
				'message' => __( 'Invalid ticket number.', 'wporg' ),
			);
		}

		// Slap on a new nonce for repeat offenders.
		$message['new_nonce'] = wp_create_nonce( 'wporg-attach-ticket' );

		die( json_encode( $message ) );
	}

	/**
	 * Detach a Trac ticket.
	 *
	 * @access public
	 */
	public function detach_ticket() {
		check_ajax_referer( 'wporg-detach-ticket', 'nonce' );

		$post_id = empty( $_REQUEST['post_id'] ) ? 0 : absint( $_REQUEST['post_id'] );

		if ( delete_post_meta( $post_id, 'wporg_ticket_url' ) ) {
			$message = array(
				'type' => 'success',
				'message' => __( 'Ticket detached.', 'wporg' )
			);
		} else {
			$message = array(
				'type' => 'failure',
				'message' => __( 'Ticket still attached.', 'wporg' )
			);
		}

		// Slap on a new nonce for repeat offenders.
		$message['new_nonce'] = wp_create_nonce( 'wporg-detach-ticket' );

		die( json_encode( $message ) );
	}

} // WPORG_Admin_Extras

$extras = new WPORG_Admin_Extras();
