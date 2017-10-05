<?php
	require_once( get_template_directory() . '/core/class-tgm-plugin-activation.php' );

	add_action( 'tgmpa_register', 'twentyseventeen_register_required_plugins' );

function twentyseventeen_register_required_plugins() {
	$plugins = array(
		array(
			'name'						=> esc_html__('WooCommerce','twentyseventeen'), // The plugin name
			'slug'						=> 'woocommerce', // The plugin slug (typically the folder name)
			'required' 				=> false, // If false, the plugin is only 'recommended' instead of required
		),
		array(
			'name'						=> esc_html__('WooCommerce Gateway Paypal Express Checkout','twentyseventeen'),
			'slug'						=> 'woocommerce-gateway-paypal-express-checkout',
			'required' 				=> false,
		),

	);

	/**
		* Array of configuration settings. Amend each line as needed.
		* If you want the default strings to be available under your own theme domain,
		* leave the strings uncommented.
		* Some of the strings are added into a sprintf, so see the comments at the
		* end of each line for what each argument will be.
		*/
	$config = array(
		'domain'				=> 'twentyseventeen',					// Text domain - likely want to be the same as your theme.
		'default_path' 			=> '',									// Default absolute path to pre-packaged plugins
		'menu'					=> 'install-required-plugins', 	// Menu slug
		'has_notices'			=> true,												// Show admin notices or not
		'is_automatic'			=> false,							// Automatically activate plugins after installation or not
		'message' 				=> '',							// Message to output right before the plugins table
		'strings'				=> array(
			'page_title'						=> esc_html__( 'Install Required Plugins', 'twentyseventeen' ),
			'menu_title'						=> esc_html__( 'Install Plugins', 'twentyseventeen' ),
			'installing'						=> esc_html__( 'Installing Plugin: %s', 'twentyseventeen' ), // %1$s = plugin name
			'oops'								=> esc_html__( 'Something went wrong with the plugin API.', 'twentyseventeen' ),
			'notice_can_install_required'		=> _n_noop( 'This theme requires the following plugin: %1$s.', 'This theme requires the following plugins: %1$s.', 'twentyseventeen' ), // %1$s = plugin name(s)
			'notice_can_install_recommended'	=> _n_noop( 'This theme recommends the following plugin: %1$s.', 'This theme recommends the following plugins: %1$s.', 'twentyseventeen' ), // %1$s = plugin name(s)
			'notice_cannot_install'				=> _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.', 'twentyseventeen' ), // %1$s = plugin name(s)
			'notice_can_activate_required'		=> _n_noop( 'The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.', 'twentyseventeen' ), // %1$s = plugin name(s)
			'notice_can_activate_recommended'	=> _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.', 'twentyseventeen' ), // %1$s = plugin name(s)
			'notice_cannot_activate' 			=> _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.', 'twentyseventeen' ), // %1$s = plugin name(s)
			'notice_ask_to_update' 				=> _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.', 'twentyseventeen' ), // %1$s = plugin name(s)
			'notice_cannot_update' 				=> _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.', 'twentyseventeen' ), // %1$s = plugin name(s)
			'install_link' 						=> _n_noop( 'Begin installing plugin', 'Begin installing plugins', 'twentyseventeen' ),
			'activate_link' 					=> _n_noop( 'Activate installed plugin', 'Activate installed plugins', 'twentyseventeen' ),
			'return'							=> esc_html__( 'Return to Required Plugins Installer', 'twentyseventeen' ),
			'plugin_activated'					=> esc_html__( 'Plugin activated successfully.', 'twentyseventeen' ),
			'complete' 							=> esc_html__( 'All plugins installed and activated successfully. %s', 'twentyseventeen' ), // %1$s = dashboard link
			'nag_type'							=> 'updated',// Determines admin notice type - can only be 'updated' or 'error'
		),
	);

	tgmpa( $plugins, $config );

}
?>
