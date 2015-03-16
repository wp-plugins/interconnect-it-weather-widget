<?php

function icit_change_user_agent( ) {
	$agent = "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/8.0.552.237 Safari/534.10";
	return $agent;
}

if ( ! function_exists( 'icit_fetch_open_weather' ) ) {
	function icit_fetch_open_weather( $city = 'liverpool', $country = 'uk', $extended = false ) {
		global $iso3166;
		
		// Get current weather info
		if ( preg_match( '/^\d{7}$/', $city ) ) {
			$url = sprintf( 'http://api.openweathermap.org/data/2.5/weather?id=%s&units=metric&APPID=80e6adde4b84756459e533351cb8487a', $city );
		} else {
			$url = sprintf( 'http://api.openweathermap.org/data/2.5/weather?q=%1$s,%2$s&units=metric&APPID=80e6adde4b84756459e533351cb8487a', $city, $country );
		}
		
		// Create JSON array Set timeout to 10s for when OpenWeatherMap is being slow
		$content = wp_remote_get( $url, array( 'timeout' => 10 ) );
		$json = json_decode( wp_remote_retrieve_body( $content ), true );
		
		// Change the user agent string (Fixes problem with results of some country/city locations not being returned)
		add_filter('http_headers_useragent', 'icit_change_user_agent');
		
		// This will be our repository for the results.
		$output = array( );
		
		// Break if OpenWeatherMap returns an error
		if ( isset( $json[ 'cod' ] ) && ( substr( $json[ 'cod' ], 0, 2 ) != '20' ) || !isset( $json[ 'name' ] ) ) {
		
			$output [ 'error' ] = $json[ 'message' ];
			return $output;
		
		} else {
			
			$output[ 'current' ][ 'city' ] = ( string ) $json[ 'name' ];
			$output[ 'current' ][ 'country' ] = ( string ) $json[ 'sys' ][ 'country' ];
			$output[ 'current' ][ 'temperature' ] = ( string ) $json[ 'main' ][ 'temp' ];
			$output[ 'current' ][ 'humidity' ] = ( string ) $json[ 'main' ][ 'humidity' ];
			$output[ 'current' ][ 'speed' ] = ( string ) $json[ 'wind' ][ 'speed' ];
			$output[ 'current' ][ 'direction' ] = ( string ) $json[ 'wind' ][ 'deg' ];
			$output[ 'current' ][ 'number' ] = ( string ) $json[ 'weather' ][ 0 ][ 'id' ];
			$output[ 'current' ][ 'rise' ] = ( string ) $json[ 'sys' ][ 'sunrise' ];
			$output[ 'current' ][ 'set' ] = ( string ) $json[ 'sys' ][ 'sunset' ];
			
		}
		
		// If we've asked for the extended forecast then process that too.
		if ( $extended ) {

			// Get next three day forecast
			if ( preg_match( '/^\d{7}$/', $city ) ) {
				$url = sprintf( 'http://api.openweathermap.org/data/2.5/forecast/daily?id=%s&units=metric&cnt=4&APPID=80e6adde4b84756459e533351cb8487a', $city );
			} else {
				$url = sprintf( 'http://api.openweathermap.org/data/2.5/forecast/daily?q=%1$s,%2$s&units=metric&cnt=4&APPID=80e6adde4b84756459e533351cb8487a', $city, $country );
			}
			
			// Create JSON array
			$content = wp_remote_get( $url, array( 'timeout' => 10 ) );
			$json = json_decode( wp_remote_retrieve_body( $content ), true );

			if ( isset( $json[ 'cod' ] ) && ( substr( $json[ 'cod' ], 0, 2 ) != '20' ) || !isset( $json[ 'list' ] ) ) {
			
				$output [ 'error' ] = $json[ 'message' ];
				return $output;
			
			} else {
			
				// Extract the forecast info from the feed and declare variables for attributes.
				$output[ 'forecast' ] = array( );
				foreach ( $json[ 'list' ] as $i => $forecast ) {
					
					if ( $i == 0 )
						continue;
					
					$forecast_output = array();
					
					$forecast_output[ 'time' ] = ( string ) $forecast[ 'dt' ];
					$forecast_output[ 'number' ] = ( string ) $forecast[ 'weather' ][ 0 ][ 'id' ];
					$forecast_output[ 'temperature' ] = ( string ) $forecast[ 'temp' ][ 'day' ];
					
					$output[ 'forecast' ][ ] = $forecast_output;
					
				}
				
			}
			
		}

		return $output;
	}
}

?>