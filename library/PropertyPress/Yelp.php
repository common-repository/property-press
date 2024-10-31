<?php 
/**
 * Helper class for connecting with YELP.
 * @author Paul Szczesny
 *
 */
class Yelper {
	
	private $api;
	
	public function __construct($api='seeQMOJZogshw1mSALwOvw') {
		$this->api = $api;
	}
	
	/**
	 * 
	 * @param String $lat The latitude
	 * @param String $lng The longitude
	 * @param String $term (optional) What to search for. See http://www.yelp.com/developers/documentation/category_list
	 * @param Integer $limit (optional) The number of results
	 * @param Integer $radius (optional) The radius from the point to search for
	 * @param String $type (optional) The type of search
	 */
	public function getResults($lat, $lng, $term='yelp', $limit=20, $radius=10, $type='business_review_search') {
		$yelpstring = file_get_contents("http://api.yelp.com/business_review_search?term=$term&lat=".$lat."&long=".$lng."&radius=$radius&limit=$limit&ywsid=$this->api", true); 
		$obj = json_decode($yelpstring);// Convert JSON from yelp return string
		return ($obj->message->code == 0) ? $obj : false;
	}
	
	/**
	 * 
	 * @param String $lat The latitude
	 * @param String $lng The longitude
	 * @param String $term (optional) What to search for. See http://www.yelp.com/developers/documentation/category_list
	 * @param Integer $limit (optional) The number of results
	 * @param Integer $radius (optional) The radius from the point to search for
	 * @param String $type (optional) The type of search
	 */
	public function printResults($lat, $lng, $term='yelp', $limit=20, $radius=10, $type='business_review_search') {
		$yelpstring = file_get_contents("http://api.yelp.com/business_review_search?term=$term&lat=".$lat."&long=".$lng."&radius=$radius&limit=$limit&ywsid=$this->api", true); 
		$obj = json_decode($yelpstring);// Convert JSON from yelp return string
		if ($obj->message->code == 0) {
			foreach($obj->businesses as $business) {
	    		echo "<img border=0 src='".$business->photo_url."'><br/>";
	    		echo $business->name."<br/>";
	    		echo $business->address1."<br/>";
	    		if( $business->address2 ) echo $business->address2."<br/>";
	    		echo $business->city ."<br/>";
	    		echo $business->state ."<br/>";
	    		echo $business->zip ."<br/>";
	    		echo $business->latitude ."<br/>";
	    		echo $business->longitude ."<br/>";
	    		echo "<hr>";
			}
		}
	}
}


?>