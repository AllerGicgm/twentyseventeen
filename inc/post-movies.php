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
	echo '<label>Subtitle</label><br/><input name="2017_subtitle" type=text value="'.$sub.'"><br/>';

	$mb_data = get_post_meta( $post->ID, '_price' );
	$price = '';
	if (!empty($mb_data)) {
		$price = $mb_data[0];
	}
	echo '<label>Price</label><br/><input name="price" type=text value="'.$price.'">';
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

		$price = $_POST['price'];
		if (!empty($data)) {
			update_post_meta($pid, '_price', $price);
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

if (class_exists('WC_Product_Data_Store_CPT')) {
	class twentyseventeen_Product_Data_Store_CPT extends WC_Product_Data_Store_CPT {

		/**
		 * Method to read a product from the database.
		 * @param WC_Product
		 */

		public function read( &$product ) {

			$product->set_defaults();

			if ( ! $product->get_id() || ! ( $post_object = get_post( $product->get_id() ) ) || ! in_array( $post_object->post_type, array( 'movie', 'product' ) ) ) { // change birds with your post type
				throw new Exception( __( 'Invalid product.', 'woocommerce' ) );
			}

			$id = $product->get_id();

			$product->set_props( array(
				'name'              => $post_object->post_title,
				'slug'              => $post_object->post_name,
				'date_created'      => 0 < $post_object->post_date_gmt ? wc_string_to_timestamp( $post_object->post_date_gmt ) : null,
				'date_modified'     => 0 < $post_object->post_modified_gmt ? wc_string_to_timestamp( $post_object->post_modified_gmt ) : null,
				'status'            => $post_object->post_status,
				'description'       => $post_object->post_content,
				'short_description' => $post_object->post_excerpt,
				'parent_id'         => $post_object->post_parent,
				'menu_order'        => $post_object->menu_order,
				'reviews_allowed'   => 'open' === $post_object->comment_status,
			) );

			$this->read_attributes( $product );
			$this->read_downloads( $product );
			$this->read_visibility( $product );
			$this->read_product_data( $product );
			$this->read_extra_data( $product );
			$product->set_object_read( true );
		}

		/**
		 * Get the product type based on product ID.
		 *
		 * @since 3.0.0
		 * @param int $product_id
		 * @return bool|string
		 */
		public function get_product_type( $product_id ) {
			$post_type = get_post_type( $product_id );
			if ( 'product_variation' === $post_type ) {
				return 'variation';
			} elseif ( in_array( $post_type, array( 'movie', 'product' ) ) ) { // change birds with your post type
				$terms = get_the_terms( $product_id, 'product_type' );
				return ! empty( $terms ) ? sanitize_title( current( $terms )->name ) : 'simple';
			} else {
				return false;
			}
		}
	}
}

add_filter( 'woocommerce_data_stores', 'woocommerce_data_stores' );

function woocommerce_data_stores ( $stores ) {
	$stores['product'] = 'twentyseventeen_Product_Data_Store_CPT';
	return $stores;
}

function wc_product_class( $class, $product_type, $post_type ) {
	if( 'movie' == $post_type )
		$class = 'WC_Product_Simple';
	return $class;
}

add_filter( 'woocommerce_product_class', 'wc_product_class', 10, 3);

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
