<?php


function icit_change_user_agent(){
	$agent = "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/8.0.552.237 Safari/534.10";
	return $agent;
}


if ( ! function_exists( 'icit_fetch_google_weather' ) ) {
	function icit_fetch_google_weather( $city = 'liverpool', $country = 'UK', $extended = true ) {
		global $iso3166;

		$url = sprintf( 'http://www.google.com/ig/api?weather=%s,%s&hl=en', urlencode(strtolower( $city )), in_array( $country, array_keys( $iso3166 ) ) ? strtolower( $country ) : 'gb' );

		#Change the user agent string (Fixes problem with results of some country/city locations not being returned)
		add_filter('http_headers_useragent', 'icit_change_user_agent');

		// Collect the HTML file
		$responce = wp_remote_request($url );
		// Check the headers
		if ( is_wp_error( $responce ) )
			return $responce;

		if ( wp_remote_retrieve_response_code( $responce ) != 200 )
			return new WP_Error( 'html_fetch', sprintf( __( 'HTTP response code %s', ICIT_WEATHER_DOM ), wp_remote_retrieve_response_code( $responce ) ) );


		// Create the XML object
		$xml = wp_remote_retrieve_body($responce);

		try {
			$data = @new SimpleXMLElement( $xml, LIBXML_NOCDATA );
		} catch ( Exception $e ) {
			return new WP_Error( 'xml_parse', $e->getMessage( ) );
		}

		// This will trigger if we're looking for a city that doesn't exist
		if( is_a( $data->weather, 'SimpleXMLElement' ) && in_array( 'problem_cause', array_keys( (array) $data->weather->children( ) ) ) )
			return new WP_Error( 'bad_location', __( 'Most likely could not find the place you were looking for or Google have broken their weather API.', ICIT_WEATHER_DOM ) );

		// This will be our repository for the results.
		$output = array( );

		// Extract the forecast information
		$forecast_info = $data->xpath( 'weather/forecast_information' );
		if ( empty( $forecast_info ) || $forecast_info === false )
			return new WP_Error( 'xml_parse', __( 'Unexpected feed format.', ICIT_WEATHER_DOM ) );

		if ( is_array( $forecast_info ) )
			$forecast_info = $forecast_info[ 0 ];

		foreach( $forecast_info->children( ) as $value ) {
			$output[ 'forecast_info' ][ $value->getName( ) ] = ( string ) $value->attributes( );
		}

		// Extract the current conditions from the feed.
		$current_cond = $data->xpath( 'weather/current_conditions' );
		if ( empty( $current_cond ) || $current_cond === false )
			return new WP_Error( 'xml_parse', __( 'Unexpected feed format.', ICIT_WEATHER_DOM ) );

		if ( is_array( $current_cond ) )
			$current_cond = $current_cond[ 0 ];

		foreach( $current_cond->children( ) as $value ) {
			$output[ 'current' ][ $value->getName( ) ] = ( string ) $value->attributes( );
		}

		// If we've asked for the extended forcast then process that too.
		if ( $extended ) {
			$forecast_cond = $data->xpath( 'weather/forecast_conditions' );
			if ( empty( $forecast_cond ) || $forecast_cond === false )
				return new WP_Error( 'xml_parse', __( 'Unexpected feed format.', ICIT_WEATHER_DOM ) );

			foreach( $forecast_cond as $day_data ) {
				$temp = array( );

				foreach( $day_data as $day ) {
					if ( strtolower( $day->getName( ) ) == 'day_of_week' ) {
						$key = ( string ) $day->attributes( );
					} else {
						$temp[ $day->getName( ) ] = ( string ) $day->attributes( );
					}
				}
				$output[ 'forecast' ][ $key ] = $temp;
			}
		}

		return $output;
	}
}

?>
