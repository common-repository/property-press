<?php
/**
 * Helper class for Walkscore
 * @author Paul
 *
 */
class Walkscore {
	
	private $api;
	
	public function __construct($api = '51c63ba2dffe614fdfecfb78dd1c2aa9') {
		$this->api = $api;
	}
	
	/**
	 * Get Raw Walkscore Data
	 * @param String $lat The latitude
	 * @param String $lon The longitude
	 * @return Array $obj
	 */
	public function getWalkscoreData($lat, $lon) { 
  		$url = "http://api.walkscore.com/score?format=json&lat=$lat&lon=$lon&wsapikey=$this->api";
  		$str = @file_get_contents($url);
  		$obj = json_decode($str); 
  		return ($obj->result->status == 1) ? $obj->result : false;
 	}
	
 	public function getWalkscoreMap($address, $width=500, $height=300, $layout='horizontal') {	
 		$map = "<script type='text/javascript'>
			var ws_wsid = '$this->api';
			var ws_address = '$address';
			var ws_width = '$width';
			var ws_height = '$height';
			var ws_layout = '$layout';
			</script>
			<style type='text/css'>
			#ws-walkscore-tile {position:relative;text-align:left}
			#ws-walkscore-tile *{float:none;}
			#ws-footer a,#ws-footer a:link{font:11px Verdana,Arial,Helvetica,sans-serif;margin-right:6px;white-space:nowrap;padding:0;color:#000;font-weight:bold;text-decoration:none}
			#ws-footer a:hover{color:#777;text-decoration:none}
			#ws-footer a:active{color:#b14900}
			</style>
			<div id='ws-walkscore-tile'>
				&nbsp;
			</div>
			<script type='text/javascript' src='http://www.walkscore.com/tile/show-walkscore-tile.php'></script>";
 		return $map;
 	}
	
}