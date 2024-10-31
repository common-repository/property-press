<?php
if(!is_file('Zend/Http/Client.php')) require_once WP_PLUGIN_DIR.'/property-press/library/Zend/Http/Client.php';
if(!is_file('Zend/Json/Decoder.php')) require_once WP_PLUGIN_DIR.'/property-press/library/Zend/Json/Decoder.php';

/**
 * Geocoder
 *
 * @author Paul Szczesny
 */
class PropertypressGeocoder {
	
    protected $apiKey;
    
    /**
     * 
     */
    private function getGeocodeUri() {
        return 'http://maps.google.com/maps/api/geocode/json';
    }
    
    /**
     * 
     * @param $apiKey = Google Maps API key (see http://maps.google.com)
     */
    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
    }
    
    /**
     * Geocode the address return coordinates
     * @param String $address
     */
    public function geocode($address) {
        $client = new Zend_Http_Client();
        $client->setUri($this->getGeocodeUri());
        $client->setParameterGet('address',urlencode($address))
                   ->setParameterGet('sensor','false');

        $result = $client->request('GET');

        $response = Zend_Json_Decoder::decode($result->getBody(),Zend_Json::TYPE_OBJECT);
        return $response->results[0]->geometry->location;
    }

} ?>