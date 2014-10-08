=== ICIT Weather Widget ===

Contributors: interconnectit, sanchothefat, AndyWalmsley, spectacula, mim_mc
Tags: weather, widget
Requires at least: 3.8.1
Tested up to: 4.0
Stable tag: 2.1
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The ICIT Weather Widget provides a simple way to show a weather forecast on your website.

== Description ==

Weather information is pulled from the OpenWeatherMap API. This plugin adds a widget that can be dropped into any sidebar, can be customised to suit your theme and won't hit any usage limits.
 
The widget can be configured as follows:

* Country - choose the country to get weather data from. (Some countries do not have an average result for weather conditions)
* City - Enter the name of your area to recieve the weather results.
* Display Mode - you can choose to show either a compact view of the current weather or show the forecast for the current day and next three days.
* Background colour during day - use the colour picker to choose what colour background you want during the day time.
* Background colour during night - use the colour picker to choose what colour background you want during the night time.
* Show Temperature in Celsius - sets the temperature display to degrees celsius rather than farenheit.
* Show weather breakdown - choose whether to display the wind, humidity and written weather condition, as well as the temperature and weather icon.
* Show Wind Speed in mph - sets the wind speed display to mph rather than km/h.
* Cache Time - this is the interval in minutes before the plugin refreshes the forecast data.
* Output CSS - toggle whether the widget should output it's own CSS.
* Show Credit Link - this plugin is offered completely free of charge. If you're feeling kind please leave this in to send some delicious web traffic our way :) 

You can override what gets displayed for the location by filling in the widget's title text.

== Installation ==

1. You can install the plugin using the auto-install tool from the WordPress back-end.
2. To manually install, upload the folder `/icit-weather-widget/` to `/wp-content/plugins/` directory.
3. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= The Widget is Not Displaying =

This could be because there are no weather results returned from OpenWeatherMap for your region or area. You can try being less specific eg. by entering the name of your nearest City rather than Town but if you feel there is a mistake please let us know.

== Known problems ==

This widget should work in all themes, although some themes styling will mix with the widget leaving bits that look out of place.

IE7 does not support the icons we use for the weather display.
Works in IE8+, Chrome, Firefox, Opera, and Safari.

== Screenshots ==

1. Compact Mode
2. Extended Mode
3. Widget Settings

== Changelog ==

* 2.1 - icit-weather-widget is now translatable.
* 2.0 - Now uses OpenWeatherMap, new icons and customisable css.

== Upgrade Notice ==

* 2.1 - Translatable!
* 2.0 - It Works!