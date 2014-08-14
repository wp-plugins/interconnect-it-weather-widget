<?php

function icit_change_user_agent(){
	$agent = "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/8.0.552.237 Safari/534.10";
	return $agent;
}


if ( ! function_exists( 'icit_fetch_open_weather' ) ) {
	function icit_fetch_open_weather( $city = 'liverpool', $country = 'uk', $extended = true ) {
		global $iso3166;

		// Get current weather info
		$url = sprintf( 'http://api.openweathermap.org/data/2.5/weather?q=' . $city . ',' . $country . '&mode=xml&units=metric&APPID=80e6adde4b84756459e533351cb8487a'. apply_filters('icit_weather_widget_locale', get_locale()), urlencode(strtolower( $city )), in_array( $country, array_keys( $iso3166 ) ) ? strtolower( $country ) : 'gb' );

		// Change the user agent string (Fixes problem with results of some country/city locations not being returned)
		add_filter('http_headers_useragent', 'icit_change_user_agent');

		// Collect the HTML file
		$response = wp_remote_request( $url );
		// Check the headers
		if ( is_wp_error( $response ) )
			return $response;

		if ( wp_remote_retrieve_response_code( $response ) != 200 )
			return new WP_Error( 'html_fetch', sprintf( __( 'HTTP response code %s', ICIT_WEATHER_DOM ), wp_remote_retrieve_response_code( $response ) ) );


		// Create the XML object
		$xml = wp_remote_retrieve_body( $response );

		try {
			$data = new SimpleXMLElement( $xml, LIBXML_NOCDATA );
		} catch ( Exception $e ) {
			return new WP_Error( 'xml_parse', $e->getMessage( ) );
		}

		// This will trigger if we're looking for a city that doesn't exist
		if( is_a( $data, 'SimpleXMLElement' ) && in_array( 'problem_cause', array_keys( (array) $data->children( ) ) ) )
			return new WP_Error( 'bad_location', __( 'Most likely could not find the place you were looking for or OpenWeatherMap have broken their weather API.', ICIT_WEATHER_DOM ) );


		// This will be our repository for the results.
		$output = array( );

		// Extract the current conditions from the feed and declare variables for attributes.
		$current_temp = $data->xpath( 'temperature' );
		$current_hum = $data->xpath( 'humidity' );
		$current_windSpeed = $data->xpath( 'wind/speed' );
		$current_windDirection = $data->xpath( 'wind/direction' );
		$current_weather = $data->xpath( 'weather' );
		$current_sun = $data->xpath( 'city/sun' );
		
		foreach( $current_temp as $value ) {
			$output[ 'current' ][ $value->getName( ) ] = ( string ) $value->attributes( );
		}
		foreach( $current_hum as $value ) {
			$output[ 'current' ][ $value->getName( ) ] = ( string ) $value->attributes( );
		}
		foreach( $current_windSpeed as $value ) {
			$output[ 'current' ][ $value->getName( ) ] = ( string ) $value->attributes( );
		}
		foreach( $current_windDirection as $value ) {
			$output[ 'current' ][ $value->getName( ) ] = ( string ) $value->attributes( )->code;
		}
		foreach( $current_weather as $value ) {
			$output[ 'current' ][ 'number' ] = ( string ) $value->attributes( )->number;
		}
		foreach( $current_sun as $value ) {
			$output[ 'current' ][ 'rise' ] = ( string ) $value->attributes( )->rise;
			$output[ 'current' ][ 'set' ] = ( string ) $value->attributes( )->set;
		}

		// If we've asked for the extended forecast then process that too.
		if ( $extended ) {

			// Get next three day forecast
			$url = sprintf( 'http://api.openweathermap.org/data/2.5/forecast/daily?q=' . $city . ',' . $country . '&mode=xml&units=metric&cnt=4&APPID=80e6adde4b84756459e533351cb8487a'. apply_filters('icit_weather_widget_locale', get_locale()), urlencode(strtolower( $city )), in_array( $country, array_keys( $iso3166 ) ) ? strtolower( $country ) : 'gb' );
			
			// Collect the HTML file
			$response = wp_remote_request($url );
			// Check the headers
			if ( is_wp_error( $response ) )
				return $response;

			if ( wp_remote_retrieve_response_code( $response ) != 200 )
				return new WP_Error( 'html_fetch', sprintf( __( 'HTTP response code %s', ICIT_WEATHER_DOM ), wp_remote_retrieve_response_code( $response ) ) );


			// Create the XML object
			$xml = wp_remote_retrieve_body($response);

			try {
				$data = new SimpleXMLElement( $xml, LIBXML_NOCDATA );
			} catch ( Exception $e ) {
				return new WP_Error( 'xml_parse', $e->getMessage( ) );
			}

			// Extract the forecast info from the feed and declare variables for attributes.
			$output[ 'forecast' ] = array( );
			foreach ( $data->xpath( 'forecast/time' ) as $i => $forecast ) {
				if ( $i == 0 )
					continue;
				
				$forecast_output = array( );
				
				$forecast_output[ $forecast->getName( ) ] = ( string ) $forecast->attributes( )->day;
				foreach( $forecast->xpath( 'symbol' ) as $day_data ) {
					$forecast_output[ 'number' ] = ( string ) $day_data->attributes( )->number;
				}
				foreach( $forecast->xpath( 'temperature' ) as $day_data ) {
					$forecast_output[ $day_data->getName( ) ] = ( string ) $day_data->attributes( )->day;
				}
				$output[ 'forecast' ][ ] = $forecast_output;
			}
		}

		return $output;
	}
}

?>