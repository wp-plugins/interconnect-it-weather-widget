=== Plugin Name ===
Contributors: interconnectit, spectacula, sanchothefat
Donate link: http://interconnectit.com/1474/wordpress-weather-widget/
Tags: weather, widget
Requires at least: 3.0
Tested up to: 3.0.2
Stable tag: 1.0.1

An easy to use, elegant weather widget to work in most sidebar and widget locations.

== Description ==

We found many of the weather widgets and badges out there lacking. Either they were not aimed at regular users (having to track down your own locid for non-US addresses for example) or they had rate limits on the API. And almost all were just plain ugly. We had to fix that.

At Interconnect IT we believe in speed, reliability and usability.  To that end we decided to create our own releasable weather widget.  Using the wonderful images from [Radoslav Dimov's jDigiClock jQuery plugin] (http://www.radoslavdimov.com/jquery-plugins/jquery-plugin-digiclock/), we were able to build an attractive, GPL compatible weather widget.  We all like that.

The widget uses caching, so by default only makes 24 API calls to the Google Weather API, making sure that no matter how busy your site, you aren't going to find your widget blocked for making too many requests.

The widget is prepared for translation, if you'd like to drop us a line about your translation you can contact us through our [website] (http://interconnectit.com/about/contact/).

== Installation ==

## The install ##
1. You can install the plugin using the auto-install tool from the WordPress back-end.
2. To manually install, upload the folder `/interconnect-it-weather-widget/` to `/wp-content/plugins/` directory.
3. Activate the plugin through the 'Plugins' menu in WordPress
4. You should now see the widget show up under 'widgets' menu. Drop that widget into a sidebar.
5. With the widget in the sidebar you should see the config for this widget.

## The config ##
1. Once the plugin is activated, you will see a new widget appear titled ICIT Weather.
2. Drop the widget into an appropriate widget space - although the widget is variable width, some spaces can be too small so you may need to check.
3. Now you can go through the widget options.
4. Title lets you override the display of the city name.  In most cases you will leave this blank.
5. Choose your country.
6. Enter your town, city, postcode or zipcode.  This will be used to lookup the location on Google.
7. Choose the widget display format depending on how you want the widget to look on your site.
8. You can choose to have a forecast up to four days in advance.
9. The check timing is important - if you set it too high then you may miss updates.  Set it too low and you'll make more API calls.  We recommend 60 minutes.
10. You can choose between Celsius and Farenheit for display.
11. You can block inline CSS output - this can be useful for designers wishing to make the design fit their theme exactly.
12. There's a credit link option - you don't have to use it, but it's nice if you do :-)

== Frequently Asked Questions ==

= Where do you get the weather forecast from? =

The Google Weather API.

= The forecast isn't accurate =

If this is the case then we can't help much as we simply use Google's supplied data.  We may offer a choice of data suppliers in the future.

= The Widget is Not Displaying =

This could be because there are no weather results returned from Google for your region or area. You can try being less specific eg. by entering the name of your nearest City rather than Town but if you feel there is a mistake please let us know.

== Screenshots ==

1. The extended mode display as you'd normally see on a site.
2. The widget configuration panel.

== Changelog ==

= 1.0.2 =
* The version we should have released to the public first, but David's an idiot who can't keep a track on version numbers...

= 1.0.1 =
* Added a range of options, made plugin generic for use on most sites, first public release version.

= 1.0.0 =
* Development version


== Upgrade Notice ==

= 1.0.1 =
This version adds more flexibility and reliability to the plugin.

