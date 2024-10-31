<?php
/*
Plugin Name: Property Press
Plugin URI: http://www.pulsedevelopment.com/development/propertypress/
Description: Turn your wordpress installation into a Real Estate Listing Website
Version: 1.1
Author: Pulse Development
Author URI: http://www.pulsedevelopment.com
*/

/*  Copyright 2010  Pulse Development (email : info@pulsedevelopment.com)
    This file is part of Property Press.

    PropertyPress is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    any later version.

    Download Protect is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Download Protect.  If not, see <http://www.gnu.org/licenses/>.

*/

require_once('propertypress_field_maker.php');
require_once('propertypress_template_functions.php');
require_once('library/PropertyPress/GoogleMap3.php');
require_once('library/PropertyPress/Geocoder.php');

define( 'PROPERTYPRESS', WP_CONTENT_DIR . '/plugins/' . dirname(plugin_basename(__FILE__) ).'/');
define( 'IS_WP29', version_compare( $wp_version, '2.9', '>=' ) );

//This works only in WP2.9 or higher
if ( IS_WP29 == FALSE ){
	add_action( 'admin_notices', create_function( '', 'echo \'<div id="message" class="error fade"><p><strong>' . __( 'Sorry, Property Press works only under WordPress 2.9 or higher', "propertypress" ) . '</strong></p></div>\';' ) );
	return;
}

register_activation_hook(__FILE__, "propertypress_install");
register_deactivation_hook(__FILE__, "propertypress_uninstall");

function propertypress_install() {
	if(!get_option('propertypress_default_display')) add_option("propertypress_default_display", 'price,mls,address,city,state,country,zip,bedrooms,bathrooms,livingArea,yearBuilt');
	if(!get_option('propertypress_googlemaps_api')) add_option("propertypress_googlemaps_api", '');
	if(!get_option('propertypress_walkscore_api')) add_option("propertypress_walkscore_api", '');
	if(!get_option('propertypress_yelp_api')) add_option("propertypress_yelp_api", '');
}
function propertypress_uninstall() {
	if(get_option('propertypress_default_display')) delete_option("propertypress_default_display");
	if(get_option('propertypress_googlemaps_api')) delete_option("propertypress_googlemaps_api");
	if(get_option('propertypress_walkscore_api')) delete_option("propertypress_walkscore_api");
	if(get_option('propertypress_yelp_api')) delete_option("propertypress_yelp_api");
}

class PropertyPress {
	
	public $propertypress_boxes = array (
	    'General Property Info' => array (
			array( '_is_sold', 'Is Sold?', 'checkbox', '1' ),
			array( '_is_featured', 'Is Featured?', 'checkbox', '1' ),
	        array( '_listing_price', 'Listing Price ($):' ),
	        array( '_mls', 'MLS # (if any):' ),
	        array( '_address', 'Address:' ),
	        array( '_city', 'City:' ),
	        array( '_state', 'State/Province:' ),
	        array( '_zip_code', 'Zip/Postal Code:' ),
	        array( '_country', 'Country:' ),
	    ),
	    'Property Details' => array (
	    	array( '_title', 'Listing Title (Freehold, etc.):' ),
	    	array( '_type', 'Listing Type:', 'dropdown', array(
	    		'House', 'Apartment', 'Condo', 'Townhouse', 'Duplex', 'Penthouse') ),
	        array( '_living_area', 'Living Area (SqFt.):' ),
	        array( '_bedrooms', 'Bedrooms:' ),
	        array( '_bathrooms', 'Bathrooms:' ),
	        array( '_built_in', 'Built In:' ),
	        array( '_taxes', 'Taxes ($/year):'),
	        array( '_maintenance', 'Maintenance ($/month):'),
	        array( '_land_size', 'Land Size:' ),
	        array( '_storeys', 'Storeys:' ),
	        array( '_basement', 'Basement (full, 1/2, finished, unfinished):' ),
	        array( '_garage_space', 'Garage Spaces:' ),
	        array( '_latitude', 'Latitude:' ),
	        array( '_longitude', 'Longitude:' ),
	        array( '_amenities', 'Amenities:', 'textarea' ),
	    ));
	public $map;
	public $geocoder;
	
	/**
	 * Constructor for PropertyPress
	 */
	public function __construct() {
		// Register custom post types
		register_post_type('property', array(
			'label' => __('Property Press'),
			'singular_label' => __('Property'),
			'public' => true,
			'menu_icon' => WP_PLUGIN_URL. '/property-press/resources/images/icon_propertypress.png',
			'show_ui' => true, // UI in admin panel
			'_builtin' => false, // It's a custom post type, not built in!
			'_edit_link' => 'post.php?post=%d',
			'capability_type' => 'post',
			'hierarchical' => false,
			'rewrite' => array("slug" => "property"), // Permalinks format
			'query_var' => 'property',
			'supports' => array('title','excerpt', 'editor', 'custom-fields', 'thumbnail', 'sticky-posts')
		));
		$this->geocoder = new PropertypressGeocoder(get_option('propertypress_googlemaps_api'));
		register_taxonomy( 'features', 'property', array( 'hierarchical' => true, 'label' => __('Features') ) );
		// Insert post hook
		add_action("admin_menu", array(&$this, "propertypress_add_custom_box"), 10, 2);
		// Save the custom fields
		add_action('save_post', array(&$this, 'propertypress_save_postdata'), 1, 2 );
		add_filter("manage_edit-property_columns", array(&$this, "propertypress_edit_columns"));
		add_action("manage_posts_custom_column", array(&$this, "propertypress_custom_columns"));
		add_action('wp_head', array(&$this, 'createTemplate'));
	}
	
	/**
	 * Adds a custom section to the "advanced" Post and Page edit screens
	 */ 
	public function propertypress_add_custom_box() {
	    if ( function_exists( 'add_meta_box' ) ) {
	        foreach ( array_keys( $this->propertypress_boxes ) as $box_name )
	          	add_meta_box( $box_name, __( $box_name, 'propertypress' ), array(&$this, 'propertypress_post_custom_box'), 'property', 'normal', 'high' );
	    }
	}
	
	/**
	 * Creates the Meta fields for the Property Editor
	 * @param $obj
	 * @param $box
	 */
	public function propertypress_post_custom_box ( $obj, $box ) {
		$fieldMaker = new PropertyPressFieldMaker();
	    static $propertypress_nonce_flag = false;
	    // Run once
	    if ( ! $propertypress_nonce_flag ) {
	        $fieldMaker->echo_propertypress_nonce();
	        $propertypress_nonce_flag = true;
	    }
	    // Genrate box contents
	    foreach ( $this->propertypress_boxes[$box['id']] as $propertypress_box ) echo $fieldMaker->propertypress_field_html( $propertypress_box );
	}
	 	 
	/**
	 * When the post is saved, saves our custom data 
	 * @param Integer $post_id
	 * @param Object $post
	 */
	public function propertypress_save_postdata($post_id, $post) {	 
	    // The data is already in $propertypress_boxes, but we need to flatten it out.
	    foreach ( $this->propertypress_boxes as $propertypress_box ) {
	        foreach ( $propertypress_box as $propertypress_fields ) {
	            $my_data[$propertypress_fields[0]] =  $_POST[$propertypress_fields[0]];
	        }
	    }
	    // Add values of $my_data as custom fields
	    // Let's cycle through the $my_data array!
	    foreach ($my_data as $key => $value) {
	        if ( 'revision' == $post->post_type  ) return;  // don't store custom data twice
	        $my_data[$key] = implode(',', (array)$value);  // if $value is an array, make it a CSV (unlikely)
	    }
	    // Geocode this Address if no coordinates exist
	    if(($my_data['_latitude'] == '' || $my_data['_longitude'] == '') && 
	    ( $my_data['_address'] != '' && $my_data['_city'] != '' && $my_data['_state'] != '' ) ) {
	    	$coordinates = $this->geocoder->geocode($my_data['_address'].', ' . $my_data['_city'] . ', ' . $my_data['state']);
	    	if($coordinates) { 
	    			$my_data['_latitude'] = $coordinates->lat; 
	    			$my_data['_longitude'] = $coordinates->lng; 
	    	}
	    }
	    if (get_post_meta($post->ID, '_propertypress_listing', FALSE) ) update_post_meta($post->ID, '_propertypress_listing', serialize($my_data));
	    else add_post_meta($post->ID, '_propertypress_listing', serialize($my_data));
	}
	
	/**
	 * Customizes the Property List View Fields
	 * @param unknown_type $columns
	 */
	public function propertypress_edit_columns($columns){
		$columns = array(
			"cb" => "<input type=\"checkbox\" />",
			"title" => "Page Title",
			"_title" => "Property Type",
			"_mls" => "MLS #",
			"_address" => "Address",
			"_listing_price" => "Price",
		);
		return $columns;
	}
	
	/**
	 * Retrieves the variables for Custom Columns in the Property list
	 * @param unknown_type $column
	 */
	public function propertypress_custom_columns($column) {
		global $post;
		$columns = get_post_meta($post->ID, '_propertypress_listing', true);
		$custom = unserialize($columns);
		switch ($column) {
			case "_title":
				echo $custom["_type"];
				break;
			case "_mls":
				echo $custom["_mls"];
				break;
			case "_address":
				echo $custom["_address"];
				break;
			case "_listing_price":
				echo '$'.number_format($custom["_listing_price"]);
				break;
		}
	}
	
	/**
	 * Create the Template object and assign it as a global variable
	 */
	public function createTemplate() {
		global $wp; global $wp_query;
		if ($wp->query_vars["post_type"] == "property") {
			global $property; $property = new PropertyPressProperty();
		}
	}
	
}

// Initiate the plugin
add_action("init", "PropertyPressInit");
function PropertyPressInit() { $property = new PropertyPress(); }

// Hook for adding admin menus
add_action('admin_menu', 'propertypress_add_menu');

// Adding the admin Menu
function propertypress_add_menu() {
    add_submenu_page('edit.php?post_type=property', 'PropertyPress Settings', 'Settings', 'manage_options', 'propertypress-options', 'propertypress_add_adminpage');
}

// action function for adding the administrative page
function propertypress_add_adminpage() { ?>
<div class="wrap">
<h2>PropertyPress Settings</h2>
<p style="clear: both;">Fill out the fields</p>
<form method="post" action="options.php">
<?php wp_nonce_field('update-options'); ?>

<h2>Theme Display</h2>
<p><b>Enter a comma separated list of the fields you wish to display and the order you want them displayed.</b> (used for the shortcode and theme functions).</p>
	<p><b>Choose from</b>: address, city, state, country, zip, mls, price, is_sold, title, type, livingArea, 
	bedrooms, bathrooms, taxes, maintenance, yearBuilt, storeys, landSize, basement, garage, latitute, longitude, amenities)</p>
<table class="form-table">
<tr valign="bottom">
	<td align="right">Default Display Fields</td>
	<td><input id="propertypress_default_display" name="propertypress_default_display" type="text" value="<?php echo get_option('propertypress_default_display'); ?>" size="80" /></td>
</tr>
</table>

<h2>API's and Features</h2>
<table class="form-table">
<tr valign="bottom">
	<td align="right">Google Maps API Key</td>
	<td><input id="propertypress_googlemaps_api" name="propertypress_googlemaps_api" type="text" value="<?php echo get_option('propertypress_googlemaps_api'); ?>" size="60" /></td>
</tr>
<tr valign="bottom">
	<td align="right">Walkscore API Key</td>
	<td><input id="propertypress_walkscore_api" name="propertypress_walkscore_api" type="text" value="<?php echo get_option('propertypress_walkscore_api'); ?>" size="60" /></td>
</tr>
<tr valign="bottom">
	<td align="right">Yelp API Key (for future versions)</td>
	<td><input id="propertypress_yelp_api" name="propertypress_yelp_api" type="text" value="<?php echo get_option('propertypress_yelp_api'); ?>" size="60" /></td>
</tr>
</table>

<input type="hidden" name="action" value="update" />
<input type="hidden" name="page_options" value="propertypress_default_display,propertypress_googlemaps_api,propertypress_googlemaps_id,propertypress_walkscore_api,propertypress_yelp_api" />
<p class="submit">
<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>
</form>
<div style="float: left;"><h4 style="float: left;margin: 0px; padding: 7px;">Like this plugin? </h4><form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="float: left;"> 
<input type="hidden" name="cmd" value="_s-xclick"> 
<input type="hidden" name="hosted_button_id" value="93YYQRZNQEV7Q"> 
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!"> 
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1"> 
</form> <span style="font-size: 18px; display: block; float: left; margin-top: 8px;"> or <a href="mailto:info@pulsedevelopment.com">Comment</a></span></div>

</div>
<?php } ?>