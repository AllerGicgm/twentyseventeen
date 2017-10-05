<?php
/**
 * Custom template tags for this theme
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 */

/**
 * Flush out the transients used in twentyseventeen_categorized_blog.
 */
function twentyseventeen_custom_type_register() {

	$args = array(
		'label' => 'Movies',
		'public' => true,
		'show_ui' => true,
		'capability_type' => 'post',
		'hierarchical' => true,
		'has_archive' => true,
		'rewrite' => array('slug' => 'movie', 'with_front' => false),
		'supports' => array(
			'title',
			'editor',
			'thumbnail',
		),
		'menu_position' => 24,
		'taxonomies' => array('movie-category')
	);
	register_post_type('movie', $args);

	register_taxonomy('movie-category', 'movie',
		array(
			'hierarchical' => true,
			'show_admin_column' => true,
			'label' => __('Movie Categories', 'twentyseventeen'),
			'query_var' => true,
			'rewrite' => array('slug' => 'movie-category', 'twentyseventeen')
			)
		);
}

add_action('init', 'twentyseventeen_custom_type_register');

/*
Adding metabox with subtitle text for our custom pagetype
*/
add_action( 'add_meta_boxes_movie', 'movie_add_mb' );

function movie_add_mb() {
	add_meta_box( 'twentyseventeen-metabox-id', 'Twentyseventeen Metaboxes', 'twentyseventeen_mb_callback', 'movie', 'normal', 'high' );
}

function twentyseventeen_mb_callback($post){
	wp_nonce_field( '2017_mb_nonce', 'mb_nonce' );
	$mb_data = get_post_meta( $post->ID, '2017_mb' );
	$sub = '';
	if (!empty($mb_data)) {
		$sub = $mb_data[0];
	}
	echo '<label>Subtitle</label><br/><input name="2017_subtitle" type=text value="'.$sub.'">';
}

/*
Saving subtitle text for a page during save process
*/
add_action( 'save_post', 'twentyseventeen_metabox_save', 11, 2 );
function twentyseventeen_metabox_save($pid, $post) {
	if ( in_array($post->post_type, array('movie')) ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )	return;
		if ( !isset( $_POST['mb_nonce']) || !wp_verify_nonce($_POST['mb_nonce'], '2017_mb_nonce') )	return;
		if ( !current_user_can( 'edit_post', $post->ID ) ) return;

		$data = $_POST['2017_subtitle'];

		if (!empty($data)) {
			update_post_meta($pid, '2017_mb', $data);
		}
	}
}

/*
Adding quick buy link
*/
add_filter( 'woocommerce_after_add_to_cart_button', 'wc_after_add_to_cart_button', 10, 3);

function wc_after_add_to_cart_button() {
	global $product;
	echo sprintf( '<a rel="nofollow" href="%s&qb=1" data-quantity="1" data-product_id="%s" data-product_sku="%s">%s</a>',
		esc_url( $product->add_to_cart_url() ),
		esc_attr( $product->get_id() ),
		esc_attr( $product->get_sku() ),
		__( 'Quick buy', 'twentyseventeen' ));
}

add_filter( 'woocommerce_add_to_cart_redirect', 'wc_qb_redirect',99);

function wc_qb_redirect($url) {
	if(isset($_REQUEST['qb']) && $_REQUEST['qb'] == '1'){
		return WC()->cart->get_checkout_url();
	}
	return $url;
}

/*
Adding custom registration field
*/
add_action( 'register_form', 'twentyseventeen_register_form' );
function twentyseventeen_register_form() {
	$skype = ( ! empty( $_POST['skype'] ) ) ? trim( $_POST['skype'] ) : '';
?>
	<p><label for="skype"><?php _e( 'Skype', 'twentyseventeen' ) ?><br />
	<input type="text" name="skype" id="skype" class="input" value="<?php echo esc_attr( wp_unslash( $skype ) ); ?>" size="25" /></label>
	</p>
<?php
}

add_filter( 'registration_errors', 'twentyseventeen_registration_errors', 10, 3 );
function twentyseventeen_registration_errors( $errors, $sanitized_user_login, $user_email ) {
	if ( empty( $_POST['skype'] ) || ! empty( $_POST['skype'] ) && trim( $_POST['skype'] ) == '' ) {
		$errors->add( 'skype_error', __( '<strong>ERROR</strong>: You must include your skype account.', 'twentyseventeen' ) );
	}
	return $errors;
}

add_action( 'user_register', 'twentyseventeen_user_register' );
function twentyseventeen_user_register( $user_id ) {
	if ( ! empty( $_POST['skype'] ) ) {
		update_user_meta( $user_id, 'skype', trim( $_POST['skype'] ) );
	}
}

add_filter( 'registration_redirect', 'twentyseventeen_redirect_home' );
function twentyseventeen_redirect_home( $registration_redirect ) {
	return home_url( '/favourite/' );
}

/*
	End: custom registration
*/