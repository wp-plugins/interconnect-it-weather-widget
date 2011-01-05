<?php
/*
 Plugin Name: ICIT Weather widget
 Plugin URI: http://interconnectit.com/1474/wordpress-weather-widget/
 Description: A versatile weather widget that uses caching and the Google Weather API to provide weather data for your visitors.
 Version: 1.0.1
 Author: Interconnect IT, James R Whitehead
 Author URI: http://interconnectit.com
*/

if ( ! class_exists( 'icit_weather_widget' ) && version_compare( phpversion( ), 5.0, 'ge' ) && version_compare( $wp_version, 3.0, 'ge' ) ) {

	// Define some fixed elements
	define ( 'ICIT_WEATHER_DOM', 'icit_weather' );
	define ( 'ICIT_WEATHER_PTH', dirname( __FILE__ ) );
	define ( 'ICIT_WEATHER_URL', plugins_url( '', __FILE__ ) );

	// Load translation files if they exist
	$locale = get_locale( );
	if ( file_exists( ICIT_WEATHER_PTH . '/lang/' . ICIT_WEATHER_DOM . '-' . $locale . '.mo' ) )
		load_textdomain( ICIT_WEATHER_DOM, ICIT_WEATHER_PTH . '/lang/' . ICIT_WEATHER_DOM . '-' . $locale . '.mo' );

	// Load in the helper functions
	include( ICIT_WEATHER_PTH . '/includes/helpers.php' );

	add_action( 'widgets_init', array( 'icit_weather_widget', '_init' ), 1 );
	add_action( 'wp_head', array( 'icit_weather_widget', 'css' ) );

	class icit_weather_widget extends WP_Widget {

		var $images = array(
						'sunny' => '1.png',
						'mostly_sunny' => '2.png',
						'partly_cloudy' => '4.png',
						'mostly_cloudy' => '6.png',
						'chance_of_storm' => '13.png',
						'rain' => '12.png',
						'chance_of_rain' => '14.png',
						'chance_of_snow' => '21.png',
						'cloudy' => '7.png',
						'mist' => '11.png',
						'storm' => '18.png',
						'thunderstorm' => '15.png',
						'chance_of_tstorm' => '17.png',
						'sleet' => '26.png',
						'snow' => '22.png',
						'icy' => '31.png',
						'dust' => '32.png',
						'fog' => '32.png',
						'smoke' => '32.png',
						'haze' => '5.png',
						'flurries' => '22.png'
					);

		var $defaults = array(
							  'title' => '',
							  'city' => 'Liverpool',
							  'frequency' => 60,
							  'celsius' => true,
							  'data' => array( ),
							  'updated' => 0,
							  'errors' => false,
							  'country' => 'UK',
							  'clear_errors' => false
							);

		/*
		 Basic constructor.
		*/
		function icit_weather_widget( ) {
			$widget_ops = array( 'classname' => __CLASS__, 'description' => __( 'Show the weather from a location you specify.', ICIT_WEATHER_DOM ) );
			$this->WP_Widget( __CLASS__, __( 'ICIT Weather', ICIT_WEATHER_DOM), $widget_ops);
		}


		function widget( $args, $instance  ) {
			extract( $args, EXTR_SKIP );

			$instance = wp_parse_args( $instance, $this->defaults );
			extract( $instance, EXTR_SKIP );

			// Update
			if ( empty( $data ) || intval( $updated ) + ( intval( $frequency ) * 60 ) < time( ) ) {
				// We need to run an update on the data
				$all_args = get_option( $this->option_name );

				$results = icit_fetch_google_weather( $city, $country, false );

				if ( ! is_wp_error( $results ) ) {
					$data = $all_args[ $this->number ][ 'data' ] = $results;
					$updated = $all_args[ $this->number ][ 'updated' ] = time( );

					if( ! update_option( $this->option_name, $all_args ) )
						add_option( $this->option_name, $all_args );
				} else {
					// If we're looking for somewhere that's not there then we need to drop the cached data
					if ( $results->get_error_code( ) == 'bad_location' )
						unset( $data );
					$this->add_error( $results );
				}
			}

			if ( ! empty( $data ) ) {

				// Break the file name into 2 parts name and ext. The array will have basename, name and ext
				preg_match( '/(.*)\.([a-zA-Z0-9]{3,4}$)/is', basename( $data[ 'current' ][ 'icon' ] ), $icon );

				// Check that we have a local image mapped to the name expected or try the filename or finally use na.png
				if ( ! in_array( $icon[ 1 ], array_keys( $this->images ) ) ) {
					if ( file_exists( ICIT_WEATHER_PTH . '/images/' . $icon[ 0 ] ) )
						$icon[ 'filename' ] = $icon[ 0 ];
					elseif ( file_exists( ICIT_WEATHER_PTH . '/images/' . $icon[ 1 ] . '.png' ) )
						$icon[ 'filename' ] = $icon[ 1 ] . '.png';
					else
						$icon[ 'filename' ] = 'na.png';
				} else
					$icon[ 'filename' ] = $this->images[ $icon[ 1 ] ];

				$image = ICIT_WEATHER_URL . '/images/' . $icon[ 'filename' ];

 				echo $before_widget; ?>

				<div class="weather-wrapper">
					<div class="weather-icon">
						<!--[if lt IE 7]><div style="width:160px;height:103px;filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php echo $image; ?>');"></div><div style="display:none"><![endif]-->
						<img src="<?php echo $image; ?>" alt="<?php echo esc_attr( $icon[ 1 ] );?>" />
						<!--[if lt IE 7]></div><![endif]-->
					</div>
					<div class="city"><?php echo empty( $title ) ? $data[ 'forecast_info' ][ 'city' ] : $title; ?></div>
					<div class="temperature"><?php echo $celsius ? $data[ 'current' ][ 'temp_c' ] . '&deg;C' : $data[ 'current' ][ 'temp_f' ] . '&deg;F' ; ?></div>
					<div class="condition"><?php echo $data[ 'current' ][ 'condition' ]; ?></div>
					<div class="humidity"><?php echo $data[ 'current' ][ 'humidity' ]; ?></div>
					<div class="wind-condition"><?php echo $data[ 'current' ][ 'wind_condition' ]; ?></div>
					<!-- <?php printf( __( 'Last updated at %1$s on %2$s', ICIT_WEATHER_DOM ), date( get_option( 'time_format' ), $updated ), date( get_option( 'date_format' ), $updated ) ) ; ?> -->
				</div> <?php

				echo $after_widget;
			}
		}


		function add_error( $error  = '') {
			$all_args = get_option( $this->option_name );
			$all_args[ $this->number ][ 'errors' ] = array( 'time' => time( ), 'message' => is_wp_error( $error ) ? $error->get_error_message( ) : ( string ) $error );

			if( ! update_option( $this->option_name, $all_args ) )
				add_option( $this->option_name, $all_args );
		}


		function form( $instance  ) {
			$instance = wp_parse_args( $instance, $this->defaults );
			extract( $instance, EXTR_SKIP );?>

			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', ICIT_WEATHER_DOM )?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
				<em><?php _e( 'This will override the display of the city name.', ICIT_WEATHER_DOM ); ?></em>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'country' ); ?>"><?php _e( 'Choose the country:', ICIT_WEATHER_DOM )?></label>
				<select id="<?php echo $this->get_field_id( 'country' ); ?>" name="<?php echo $this->get_field_name( 'country' ); ?>" class="widefat"><?php
					global $iso3166;
					foreach( ( array ) $iso3166 as $code => $country_name ) { ?>
						<option value="<?php echo esc_attr( $code ); ?>" <?php echo selected( strtolower( $country ), strtolower( $code ) )?>><?php echo htmlentities2( ucwords( strtolower( $country_name ) ), ENT_QUOTES, get_bloginfo( 'charset' ) ) ?></option><?php
					}?>
				</select>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'city' ); ?>"><?php _e( 'City, town, postcode or zip code:', ICIT_WEATHER_DOM )?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'city' ); ?>" name="<?php echo $this->get_field_name( 'city' ); ?>" type="text" value="<?php echo esc_attr( $city ); ?>" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'frequency' ); ?>"><?php _e( 'How often do we check the weather (mins):', ICIT_WEATHER_DOM )?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'frequency' ); ?>" name="<?php echo $this->get_field_name( 'frequency' ); ?>" type="text" value="<?php echo esc_attr( $frequency ); ?>" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'celsius' ); ?>"><?php _e( 'Show temperature in celsius: ', ICIT_WEATHER_DOM );?>
					<input type="checkbox" name="<?php echo $this->get_field_name( 'celsius' ); ?>" id="<?php echo $this->get_field_id( 'celsius' ); ?>" value="1" <?php echo checked( $celsius ); ?>/>
				</label>
			</p>
			<p><em><?php printf( $updated > 0 ? __( 'Last updated "%1$s". Current server time is "%2$s".', ICIT_WEATHER_DOM ) : __( 'Will update when the frontend is next loaded. Current server time is %2$s.', ICIT_WEATHER_DOM ), date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $updated), date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), time( ) ) ); ?></em></p> <?php

			if ( ! empty( $instance[ 'errors' ] ) ) { ?>
			<div style="background-color: #FFEBE8;border:solid 1px #C00;padding:5px">
				<p><?php printf( __( 'The last error occured at "%s" with the message "%s".', ICIT_WEATHER_DOM ), date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $instance[ 'errors' ][ 'time' ] ), $instance[ 'errors' ][ 'message' ] ) ?></p>
				<label for="<?php echo $this->get_field_id( 'clear_errors' ); ?>"><?php _e( 'Clear errors: ', ICIT_WEATHER_DOM );?>
					<input type="checkbox" name="<?php echo $this->get_field_name( 'clear_errors' ); ?>" id="<?php echo $this->get_field_id( 'clear_errors' ); ?>" value="1" />
				</label>
			</div>
			<?php
			}
		}


		function update( $new_instance, $old_instance = array( ) ) {
			global $iso3166;

			$instance[ 'title' ] = sanitize_text_field( $new_instance[ 'title' ] );
			$instance[ 'country' ] = in_array( $new_instance[ 'country' ], array_keys( ( array ) $iso3166 ) ) ? $new_instance[ 'country' ] : $this->defaults[ 'country' ];
			$instance[ 'city' ] = sanitize_text_field( isset( $new_instance[ 'city' ] ) ? $new_instance[ 'city' ] : $this->defaults[ 'city' ] );
			$instance[ 'frequency' ] = intval( $new_instance[ 'frequency' ] ) > 0 ? intval( $new_instance[ 'frequency' ] ) : $this->defaults[ 'frequency' ] ;
			$instance[ 'celsius' ] = isset( $new_instance[ 'celsius' ] ) && ( bool ) $new_instance[ 'celsius' ] ? true : false;
			$instance[ 'updated' ] = 0;
			$instance[ 'data' ] = isset( $new_instance[ 'city' ], $old_instance[ 'city' ], $new_instance[ 'country' ], $old_instance[ 'country' ] ) && $new_instance[ 'city' ] == $old_instance[ 'city' ] && $new_instance[ 'country' ] == $old_instance[ 'country' ] ? $old_instance[ 'data' ] : array( );

			if ( isset( $old_instance[ 'errors' ], $instance[ 'clear_errors '] ) && ! $instance[ 'clear_errors '] )
				$instance[ 'errors' ] = $old_instance[ 'errors' ];
			else
				$instance[ 'errors' ] = array( );

			return $instance;
		}


		function _init (){
			register_widget( __CLASS__ );
		}


		function css( ) { ?>

<!-- ICIT Weather widget -->
<style type="text/css" media="screen" >
.weather-wrapper{border:solid 2px #ADC0CF;background:url('<?php echo ICIT_WEATHER_URL; ?>/images/background.png') repeat-x bottom left #F4FFFF;text-align:center;position:relative;padding:50px 10px 10px 10px;width:160px;margin:50px auto 0;/* CSS 3 Stuff */background:-webkit-gradient(linear,0% 20%,0% 100%,from(#F4FFFF),to(#d2e5f3));background:-moz-linear-gradient( 80% 100% 90deg,#d2e5f3,#F4FFFF);-moz-border-radius:5px;-moz-box-shadow:1px 1px 4px rgba(0,0,0,0.2);box-shadow:1px 1px 4px rgba(0,0,0,0.2);-webkit-border-radius:5px;-webkit-box-shadow:1px 1px 4px rgba(0,0,0,0.2);border-radius:7px;}
.weather-wrapper .weather-icon{position:absolute;top:-50px;left:10px;text-align:center}
.temperature{display:block;font-size:34px;height:34px;line-height:40px;margin:2px auto 10px;text-shadow:1px 1px 1px #fff}
</style>
<?php
		}
	}
}

/*
 Images, With these we can use our own images instead of the ones that come from
 google.

/ig/images/weather/sunny.gif
/ig/images/weather/mostly_sunny.gif
/ig/images/weather/partly_cloudy.gif
/ig/images/weather/mostly_cloudy.gif
/ig/images/weather/chance_of_storm.gif
/ig/images/weather/rain.gif
/ig/images/weather/chance_of_rain.gif
/ig/images/weather/chance_of_snow.gif
/ig/images/weather/cloudy.gif
/ig/images/weather/mist.gif
/ig/images/weather/storm.gif
/ig/images/weather/thunderstorm.gif
/ig/images/weather/chance_of_tstorm.gif
/ig/images/weather/sleet.gif
/ig/images/weather/snow.gif
/ig/images/weather/icy.gif
/ig/images/weather/dust.gif
/ig/images/weather/fog.gif
/ig/images/weather/smoke.gif
/ig/images/weather/haze.gif
/ig/images/weather/flurries.gif
*/
?>
