<?php
class PropertyPressProperty {
	
	private $id;
	public $address; public $city; public $state; public $country; public $zip;
	public $mls;
	public $price;
	public $is_featured;
	public $is_sold;
	public $title;
	public $type;
	public $livingArea;
	public $bedrooms;
	public $bathrooms;
	public $taxes;
	public $maintenance;
	public $yearBuilt;
	public $storeys;
	public $landSize;
	public $basement;
	public $garage;
	public $latitude;
	public $longitude;
	public $amenities;
	private $info; //User Selected info for default layout
	
	
	/**
	 * Constructor for the property
	 * @param Integer $id (optional) The post ID of the property. If called within the loop this will default to the current property.
	 */
	public function __construct($id=false) {
		(!$id) ? $this->id = get_the_ID() : $this->id = $id;
		$custom =  unserialize(get_post_meta($this->id, '_propertypress_listing', true)); 
		//$custom = unserialize($custom['propertypress_listing'][0]);
		$this->address = $custom['_address'];
		$this->city = $custom['_city'];
		$this->state = $custom['_state'];
		$this->country &= $custom['_country'];
		$this->zip = $custom['_zip_code'];
		$this->mls = $custom['_mls'];
		$this->price = $custom['_listing_price'];
		$this->is_featured = $custom['_is_featured'];
		$this->is_sold = $custom['_is_sold'];
		$this->title = $custom['_title'];
		$this->type = $custom['_type'];
		$this->livingArea = $custom['_living_area'];
		$this->bedrooms = $custom['_bedrooms'];
		$this->bathrooms = $custom['_bathrooms'];
		$this->taxes = $custom['_taxes'];
		$this->maintenance = $custom['_maintenance'];
		$this->yearBuilt = $custom['_built_in'];
		$this->storeys = $custom['_storeys'];
		$this->basement = $custom['_basement'];
		$this->landSize = $custom['_land_size'];
		$this->garage = $custom['_garage_space'];
		$this->amenities = $custom['_amenities'];
		$this->latitude = $custom['_latitude'];
		$this->longitude = $custom['_longitude'];
		$this->info = get_option('propertypress_default_display', 'No info available');
	}

	/**
	 * Get all of the images attached to the current post
	 * @param String $size The size of the images to get. Can be 'full', 'large', 'medium', or 'thumbnail' (default: thumbnail)
	 */
	public function getImages($size = 'thumbnail') {
		$photos = get_children( array('post_parent' => $this->id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC', 'orderby' => 'menu_order ID') );
		$results = array();
		if ($photos) {
			foreach ($photos as $photo) $results[] = wp_get_attachment_image_src($photo->ID, $size);
		}
		return $results;
	}
	
	public function getInfo($print=true) {
		$fields = explode(',', str_replace(' ','',$this->info));
		$display = '<ul class="propertypress_info">';
		foreach ($fields as $f) {
			$display .= '<li class="'.$f.'"><span class="property_label">'.$f.'</span>: '.$this->$f.'</li>';
		}
		$display .= '</ul>';
		if($print) echo $display; else return $display;
	}
	
	/**
	 * 
	 * @param Integer $width Width of the Map
	 * @param Integer $height Height of the Map
	 * @param String $layout 
	 * @param Boolean $print
	 */
	public function getWalkscoreMap($width=600, $height=286, $layout='horizontal', $print=true) {
		require_once('library/PropertyPress/Walkscore.php');
		$walkscore = new Walkscore(get_option('propertypress_walkscore_api'));
		(strlen($this->country) > 0) ? $country = ', '.$this->country : $country = '';
		if ($print) echo $walkscore->getWalkscoreMap($this->address.', '.$this->city.', '.$this->state.$country, $width, $height, $layout);
		else return $walkscore->getWalkscoreMap($this->address.', '.$this->city.', '.$this->state.$country, $width, $height, $layout);
	}
	
	/**
	 * Retrieves a google map based upon the property address.
	 * @param $width
	 * @param $height
	 * @param $zoom
	 * @param $print
	 */
	public function getGoogleMap($width=595, $height=350, $zoom=13, $print=true) {
			$google_api_key = get_option('propertypress_googlemaps_api');
			$address = $this->address.', '.$this->city.', '.$this->state;
			if(strlen($this->country)) $address .= ', '.$this->country;
			$html =  '<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;sensor=false&amp;key='.$google_api_key.'" type="text/javascript"></script>
				<div id="map_canvas" style="width: '.$width.'px; height: '.$height.'px"></div>
					<script type="text/javascript">
					function showAddress(address) {
						var map = new GMap2(document.getElementById("map_canvas"));
						var geocoder = new GClientGeocoder();
						geocoder.getLatLng(
							address,
							function(point) {
								if (!point) {
									alert(address + " not found");
								} else {
									map.setCenter(point, '.$zoom.');
									var marker = new GMarker(point);
									map.addOverlay(marker);
									map.setUIToDefault();
								}
							}
						);
					}
					showAddress("'.$address.'");
					</script>';
			if($print) echo $html; else return $html;
	}
}

//Shortcode for adding property information in wysiwyg
function propertypress_func($atts, $content = null) {
	extract(shortcode_atts(array(
		'id' => false,
		'action' => 'info',
		'width' => 600,
		'height' => 300,
		'zoom' => 13,
		'layout' => 'horizontal',
	), $atts));
	$pp = new PropertyPressProperty($id);
	if($action == 'info') {return $pp->getInfo(false);}
	elseif($action == 'map') {return $pp->getGoogleMap($width, $height, $zoom, false);}
	elseif($action == 'walkscore') {return $pp->getWalkscoreMap($width, $height, $layout, false);}
}
add_shortcode('property', 'propertypress_func');
?>