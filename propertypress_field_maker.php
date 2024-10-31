<?php 

class PropertyPressFieldMaker {
	
	private $listingData;
	
	public function __construct() {
		global $post;
		$this->listingData = unserialize(get_post_meta($post->ID, '_propertypress_listing', true));
	}
	
 	public function propertypress_field_html ( $args ) {
    	switch ( $args[2] ) {
        	case 'textarea':
            	return $this->propertypress_text_area( $args );
        	case 'checkbox':
            	return $this->propertypress_checkbox($args);
            case 'dropdown':
            	return $this->propertypress_dropdown($args);
        	case 'radio':
            	return $this->propertypress_radio($args);
        	case 'text':
        	default:
            	return $this->propertypress_text_field( $args );
    	}
	}
 
	private function propertypress_text_field ( $args ) {
    	$args[2] = $this->listingData[$args[0]];
    	$args[1] = __($args[1], 'propertypress' );
    	$label_format =
          '<label for="%1$s">%2$s</label><br />'
        . '<input style="width: 70%%;" type="text" name="%1$s" value="%3$s" /><br /><br />';
    	return vsprintf( $label_format, $args );
	}
 
	private function propertypress_text_area ( $args ) {
    	$args[2] = $this->listingData[$args[0]];
    	$args[1] = __($args[1], 'propertypress' );
    	$label_format =
          '<label for="%1$s">%2$s</label>: '
        . '<textarea style="width: 95%%;" name="%1$s">%3$s</textarea><br /><br />';
    	return vsprintf( $label_format, $args );
	}

	private function propertypress_dropdown($args) {
	   	$args[2] = $this->listingData[$args[0]];
	   	$args[1] = __($args[1], 'propertypress' );
	   	$label_format =
	         '<label for="%1$s">%2$s</label> <br />'
	         . '<select name="%1$s" style="width: 70%%;">';
	   	foreach ($args[3] as $option) {
	   		if ($args[2] == $option) $selected = ' selected = "selected"'; else $selected = '';
	   		$label_format .= '<option'.$selected.'>'.$option.'</option>';
	   	}
	   	$label_format .= '</select><br /><br />';
	   	return vsprintf( $label_format, $args );
	}

	private function propertypress_checkbox($args) {
	   $args[2] = $this->listingData[$args[0]];
	   $args[1] = __($args[1], 'propertypress' );
	   if ($args[2] == '1') $checked = ' checked = "checked"'; else $checked = '';
	   $label_format =
	         '<div style="width: 100px; text-align: right;"><label for="%1$s">%2$s</label>: '
	       . '<input style="width: 20px;" type="checkbox" name="%1$s" value="%4$s"'.$checked.' /></div><br /><br />';
	   return vsprintf( $label_format, $args );
	
	}

	private function propertypress_radio($args) {
	   	$args[2] = get_post_meta($post->ID, $args[0], true);
	   	$args[1] = __($args[1], 'propertypress' );
	   	$label_format =
	         '<label for="%1$s">%2$s</label>: '
	       . '<input style="width: 95%%;" type="text" name="%1$s" value="%3$s" /><br /><br />';
	   	return vsprintf( $label_format, $args );
	
	}

	public function echo_propertypress_nonce () {
    	// Use nonce for verification ... ONLY USE ONCE!
    	echo sprintf(
        '<input type="hidden" name="%1$s" id="%1$s" value="%2$s" />',
        'propertypress_nonce_name',
        wp_create_nonce( plugin_basename(__FILE__) )
    	);
	}
}
?>
