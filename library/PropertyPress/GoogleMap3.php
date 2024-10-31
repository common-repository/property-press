<?php
class GoogleMap3 {
	
	private $api_address = 'http://maps.google.com/maps?file=api&v=2&key=abcdefg&sensor=true';
	private $api;
	private $MapPoints;
	private $script;
	private $widht;
	private $height;
	private $lat;
	private $lng;
	private $map_id;
	
	public function __construct($lat, $lng, $width=500, $height=350, $auto=true) {
		$this->lat = $lat;
		$this->lng = $lng;
		$this->width = $width;
		$this->height = $height;
		$this->api = get_option('propertypress_googlemaps_api');
		$this->ajax_address = 'http://www.google.com/jsapi?key='.$this->api;
		$this->api_address = 'http://maps.google.com/maps?file=api&v=3&key='.$this->api.'&sensor=true';
		$this->MapPoints = array();
		$this->script = '<script type="text/javascript" src="'.$this->api_address.'">'
		.'<script type="text/javascript" src="'.$this->ajax_address.'"></script>' . "\n";
		$this->addPoint($this->lat, $this->lng);
		$this->map_id = get_option('propertypress_googlemaps_id');
		if (strlen($this->map_id) < 1) $this->map_id = 'map_canvas';
		if($auto) echo $this->makeHeadScript($this->map_id);
	}
	
	public function makeHeadScript($id) {
		$this->script .= '<script type="text/javascript">' . "\n"
			.'//<![CDATA[' . "\n"
			.'google.load("maps", "2");' . "\n"
			.'function initialize() {' ."\n"
    		.'var map = new google.maps.Map2(document.getElementById("'.$id.'"));' . "\n"
    		.'map.setCenter(new google.maps.LatLng('.$this->lat.', '.$this->lng.'), 13);' . "\n"
    		.'map.addControl(new GSmallMapControl());' . "\n"
    		.'map.addControl(new GMapTypeControl());' . "\n"
	 		.'var bounds = map.getBounds();
  			var southWest = bounds.getSouthWest();
  			var northEast = bounds.getNorthEast();
  			var lngSpan = northEast.lng() - southWest.lng();
  			var latSpan = northEast.lat() - southWest.lat();';
    		foreach($this->MapPoints as $point) $this->script .= $point;
    		$this->script .= '} google.setOnLoadCallback(initialize);' . "\n"
  			.'//]]>' . "\n"
			.'</script>';
		return $this->script;
	}
	
	public function addPoint($lat, $lng) {
		$num = count($this->MapPoints);
		$this->MapPoints[] = 'var point'.$num.' = new GLatLng('.$lat.','.$lng.');' . "\n"
			.'map.addOverlay(new GMarker(point'.$num.'));' . "\n";
	}
}