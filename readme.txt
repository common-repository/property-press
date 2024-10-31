=== PropertyPress ===
Contributors: webprodigy
Donate link: http://www.pulsedevelopment.com/development/propertypress/
Demo link: http://pp.pulsedevelopment.com
Tags: real estate, listings, property, realtor, homes, idx, realtor, property press, mls
Requires at least: 3.0
Tested up to: 3.01
Stable tag: /tag/1.1/

Turn your Wordpress installation into a feature-rich Real Estate website. 

== Description ==

Turn your Wordpress installation into a feature-rich Real Estate website. This plugin creates a custom post type for property listings with added fields for
price, address, city, bedrooms, bathrooms, floor space, and much more. It also offers automatic geocoding on save, based upon the address. Using the coordinates, 
you can also use simple theming functions for including a Google Map or Walkscore Map within your templates.

= Shortcodes =

The easiest way to add features to your site is using the shortcode functions. The basic usage is as follows:

[property]

The above code will add an unordered list of property features to your post content. You can specify which property features are displayed in the settings.

You can also modify the shortcode using attributes (defaults shown):

* action="info" (info, map, or walkscore)
* width="600" - For Google Map and Walkscore
* height="300" - For Map and Walkscore
* zoom="13" - For Map
* layout="horizontal" (horizontal or vertical) - For Walkscore
* id="25" - Use this if you want to display property details on another page or post. Otherwise it will default to the current property.

Ex: [property action="walkscore" width="350" height="700" layout="vertical"]

The above code will display a Walkscore Map that is 350px wide, 700px tall, with a vertical layout.

NOTE: Property Press Requires PHP5

If you have any questions or suggestions, send me an email at: info@pulsedevelopment.com

== Installation ==

1. Upload `propertypress` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Set options by going into the admistration panel -> Property Press -> Settings
4. Enter your API keys for the services you wish to use. NOTE: Walkscore requires a Google Map API for geocoding purposes. But you can also do this manually if you wish.
5. Enter the default div ID you want to use for Maps in your listings.
6. Use the 'PropertyPressProperty' object within your loop to access additional fields, maps, and walkscore. See theming for details.

== Frequently Asked Questions ==

= Do I need an API Key? =

Yes. Google Maps and Walkscore both require you to sign up for an API key in order to access their services from your website. The Google Maps API is also used for 
automatic geocoding (latitute and longitude) which is used by Walkscore.

= I get a Fatal Error when I try to Install. What's the deal? =

Your server is likely using PHP4. Property Press requires PHP5 in order to function. Check with your hosting company to see if they can upgrade this for you. 

= I want a feature that you have not included, can you add it? =

I have a feature request/bug report form at www.pulsedevelopment.com for this purpose. You are welcome to suggest new features and upgrades, 
and I will do my best to include them in future versions. If you are in a rush, contact me for a development quote.

== Screenshots ==

1. Property Page Edit Screen
2. Property Listing Backend
3. Demo Home Page
4. Demo Google Map
5. Demo Details Page
6. Demo Walkscore

== Other Notes ==

A full list of template functions and customization features can be found at: http://www.pulsedevelopment.com

== Change Log ==

* v1.1 (November 24, 2010) - Added shortcode functionality to allow the use of custom fields, Google Maps, and Walkscore without needing knowledge of Themes. Also added a new theme function for simplifying the display of property info.
* v1.0 (November 10, 2010) - First version of the plugin. Custom Property Type, aditional fields, google maps integration, walkscore features, automatic geocoding.

== Theming ==

On a property page, create an object to access the extra fields and features WITHIN THE LOOP. Enter an ID 
if you wish to use the object OUTSIDE THE LOOP. For instance:

$property = new PropertyPressProperty();
echo $property->price;
 
 or
 
 $property = new PropertyPressProperty(7);
echo $property->price;

= Available Variables =

* $property->price
* $property->address
* $property->city
* $property->state
* $property->country
* $property->zip
* $property->mls
* $property->is_featured
* $property->is_sold
* $property->title
* $property->type
* $property->livingArea
* $property->bedrooms
* $property->bathrooms
* $property->taxes
* $property->maintenance
* $property->yearBuilt
* $property->storeys
* $property->landSize
* $property->basement
* $property->garage
* $property->latitude
* $property->longitude
* $property->amenities

= Feature Functions =

getInfo($width, $height, $layout, $print)

Description: Creates an unordered list of the custom property fields. You can control which fields are displayed and their order, in the settings panel.
Usage: <?php $property->getInfo($print=true); ?>
Parameters:

* $print - (boolean) (optional) Whether or not to print the results or return as a variable. Defaults to true;

getWalkscoreMap($width, $height, $layout, $print)

Description: Create a walkscore map based on the current property's coordinates.
Usage: <?php $property->getWalkscoreMap($width=600, $height=286, $layout='horizontal', $print=true); ?>
Parameters:

* $width - (integer) (optional) The width of the map. Defaults to 600px.
* $height - (integer) (optional) The height of the map. Defaults to 286px.
* $layout - (string) (optional) Either 'horizontal' or 'vertical'. Defaults to 'horizontal'.
* $print - (boolean) (optional) Whether or not to print the results or return as a variable. Defaults to true;


getGoogleMap($width=595, $height=350, $zoom=13, $print=true)

Description: Create a google map based on the current property's coordinates.
Usage: <?php $property->getWalkscoreMap($width=600, $height=286, $layout='horizontal', $print=true); ?>
Parameters:

* $width - (integer) (optional) The width of the map. Defaults to 595px.
* $height - (integer) (optional) The height of the map. Defaults to 350px.
* $zoom - (integer) (optional) The zoom level of the map, between 0 and 22. I recommend the default 13 (+/- 3).
* $print - (boolean) (optional) Whether or not to print the results or return as a variable. Defaults to true;
