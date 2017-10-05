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
			'custom-fields'
		),
		'menu_position' => 24,
	);
	register_post_type('movie', $args);
}

add_action('init', 'twentyseventeen_custom_type_register');

class My_Product_Data_Store_CPT extends WC_Product_Data_Store_CPT {

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

add_filter( 'woocommerce_data_stores', 'woocommerce_data_stores' );

function woocommerce_data_stores ( $stores ) {
	$stores['product'] = 'My_Product_Data_Store_CPT';
	return $stores;
}

function wc_product_class( $class, $product_type, $post_type ) {
	if( 'movie' == $post_type )
		$class = 'WC_Product_Simple';
	return $class;
}

add_filter( 'woocommerce_product_class', 'wc_product_class', 10, 3);
add_filter( 'woocommerce_after_add_to_cart_button', 'wc_after_add_to_cart_button', 10, 3);

function wc_after_add_to_cart_button() {
	global $product;
	echo sprintf( '<a rel="nofollow" href="%s&qb=1" data-quantity="1" data-product_id="%s" data-product_sku="%s">%s</a>',
		esc_url( $product->add_to_cart_url() ),
		esc_attr( $product->get_id() ),
		esc_attr( $product->get_sku() ),
		esc_html( $product->add_to_cart_text() ));
}

add_filter( 'woocommerce_add_to_cart_redirect', 'wc_qb_redirect',99);

function wc_qb_redirect($url) {
	if(isset($_REQUEST['qb']) && $_REQUEST['qb'] == '1'){
		return WC()->cart->get_checkout_url();
	}
	return $url;
}


/*
	Adding custom comment field
*/
add_filter('comment_form_default_fields', 'custom_fields');

function custom_fields($fields) {
	$commenter = wp_get_current_commenter();
	$req = get_option( 'require_name_email' );
	$aria_req = ( $req ? " aria-required='true'" : '' );

	$skype = get_comment_meta( get_comment_ID(), 'skype', true );

	$fields[ 'skype' ] = '<p class="comment-form-skype">'.
		'<label for="skype">' . __( 'Skype' ) . '</label>'.
		'<input id="skype" name="skype" type="text" value="'.$skype.'" size="30"  tabindex="5" /></p>';

	return $fields;
}

add_action( 'comment_post', 'save_comment_meta_data' );
function save_comment_meta_data( $comment_id ) {
	if ( ( isset( $_POST['skype'] ) ) && ( $_POST['skype'] != '') ) {
		$skype = wp_filter_nohtml_kses($_POST['skype']);
		add_comment_meta( $comment_id, 'skype', $skype );
	}
}

add_filter( 'comment_text', 'modify_comment');

function modify_comment( $text ){
	if( $skype = get_comment_meta( get_comment_ID(), 'skype', true ) ) {
		$skype = '<p class="skype">'. $skype .'</p>';
		$text = $text . $skype;
		return $text;
	} else {
		return $text;
	}
}

/*
	End: Adding custom comment field
*/