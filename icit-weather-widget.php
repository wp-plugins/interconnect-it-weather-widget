<?php
/*
 Plugin Name: ICIT Weather Widget
 Plugin URI: http://interconnectit.com
 Description: The ICIT Weather Widget provides a simple way to show a weather forecast that can be styled to suit your theme and won't hit any usage limits.
 Version: 2.1
 Author: Interconnect IT, James R Whitehead, Andrew Walmsley & Miriam McNeela
 Author URI: http://interconnectit.com
*/

/*
 Mim: 	 CSS and designed the icon font.
 Andrew: Changed from Google API to OpenWeatherMap API, updated settings and display
	to reflect this. Added extra settings to the widget.
 Pete: Fixed the Zurich issue by changing the useragent, guess someone in Zuric
	upset Google with WordPress. :D
 James: Changed the class name on the extended forecast LI so it is prefixed
	with the word condition. Problems arose when the weather was "clear", too
	many themes have a class of clear that's there to force open float
	containers, mine included.
 Rob: Changed the google API call to use get_locale() which means it returns
	translated day names, conditions etc... when WPLANG is set or when 'locale'
	filter is used. In multisite WPLANG is in the options tables with same name.

	Checks unit system to determine if f_to_c() needs calling.

	Image handling & fallback hopefully a little more robust now. Google seem to
	have gone back to previous image names - can't use condition name data due
	to translations returned.
*/

if ( ! class_exists( 'icit_weather_widget' ) && version_compare( phpversion( ), 5.0, 'ge' ) && version_compare( $wp_version, 3.0, 'ge' ) ) {

	// Define some fixed elements
	define ( 'ICIT_WEATHER_DOM', 'icit_weather' );
	define ( 'ICIT_WEATHER_PTH', dirname( __FILE__ ) );
	define ( 'ICIT_WEATHER_URL', plugins_url( '', __FILE__ ) );

	// Load translation files if they exist
	$locale = get_locale( );
	load_plugin_textdomain( 'icit_weather', false, dirname( __FILE__ ) . '/lang/' );

	// Created from http://www.iso.org/iso/iso3166_en_code_lists.txt 15/6/2010
	// GB changed to UK
	$iso3166 = array( 'AF' => "AFGHANISTAN", 'AX' => "ÅLAND ISLANDS", 'AL' => "ALBANIA", 'DZ' => "ALGERIA", 'AS' => "AMERICAN SAMOA", 'AD' => "ANDORRA", 'AO' => "ANGOLA", 'AI' => "ANGUILLA", 'AQ' => "ANTARCTICA", 'AG' => "ANTIGUA AND BARBUDA", 'AR' => "ARGENTINA", 'AM' => "ARMENIA", 'AW' => "ARUBA", 'AU' => "AUSTRALIA", 'AT' => "AUSTRIA", 'AZ' => "AZERBAIJAN", 'BS' => "BAHAMAS", 'BH' => "BAHRAIN", 'BD' => "BANGLADESH", 'BB' => "BARBADOS", 'BY' => "BELARUS", 'BE' => "BELGIUM", 'BZ' => "BELIZE", 'BJ' => "BENIN", 'BM' => "BERMUDA", 'BT' => "BHUTAN", 'BO' => "BOLIVIA, PLURINATIONAL STATE OF", 'BA' => "BOSNIA AND HERZEGOVINA", 'BW' => "BOTSWANA", 'BV' => "BOUVET ISLAND", 'BR' => "BRAZIL", 'IO' => "BRITISH INDIAN OCEAN TERRITORY", 'BN' => "BRUNEI DARUSSALAM", 'BG' => "BULGARIA", 'BF' => "BURKINA FASO", 'BI' => "BURUNDI", 'KH' => "CAMBODIA", 'CM' => "CAMEROON", 'CA' => "CANADA", 'CV' => "CAPE VERDE", 'KY' => "CAYMAN ISLANDS", 'CF' => "CENTRAL AFRICAN REPUBLIC", 'TD' => "CHAD", 'CL' => "CHILE", 'CN' => "CHINA", 'CX' => "CHRISTMAS ISLAND", 'CC' => "COCOS (KEELING) ISLANDS", 'CO' => "COLOMBIA", 'KM' => "COMOROS", 'CG' => "CONGO", 'CD' => "CONGO, THE DEMOCRATIC REPUBLIC OF THE", 'CK' => "COOK ISLANDS", 'CR' => "COSTA RICA", 'CI' => "CÔTE D'IVOIRE", 'HR' => "CROATIA", 'CU' => "CUBA", 'CY' => "CYPRUS", 'CZ' => "CZECH REPUBLIC", 'DK' => "DENMARK", 'DJ' => "DJIBOUTI", 'DM' => "DOMINICA", 'DO' => "DOMINICAN REPUBLIC", 'EC' => "ECUADOR", 'EG' => "EGYPT", 'SV' => "EL SALVADOR", 'GQ' => "EQUATORIAL GUINEA", 'ER' => "ERITREA", 'EE' => "ESTONIA", 'ET' => "ETHIOPIA", 'FK' => "FALKLAND ISLANDS (MALVINAS)", 'FO' => "FAROE ISLANDS", 'FJ' => "FIJI", 'FI' => "FINLAND", 'FR' => "FRANCE", 'GF' => "FRENCH GUIANA", 'PF' => "FRENCH POLYNESIA", 'TF' => "FRENCH SOUTHERN TERRITORIES", 'GA' => "GABON", 'GM' => "GAMBIA", 'GE' => "GEORGIA", 'DE' => "GERMANY", 'GH' => "GHANA", 'GI' => "GIBRALTAR", 'GR' => "GREECE", 'GL' => "GREENLAND", 'GD' => "GRENADA", 'GP' => "GUADELOUPE", 'GU' => "GUAM", 'GT' => "GUATEMALA", 'GG' => "GUERNSEY", 'GN' => "GUINEA", 'GW' => "GUINEA-BISSAU", 'GY' => "GUYANA", 'HT' => "HAITI", 'HM' => "HEARD ISLAND AND MCDONALD ISLANDS", 'VA' => "HOLY SEE (VATICAN CITY STATE)", 'HN' => "HONDURAS", 'HK' => "HONG KONG", 'HU' => "HUNGARY", 'IS' => "ICELAND", 'IN' => "INDIA", 'ID' => "INDONESIA", 'IR' => "IRAN, ISLAMIC REPUBLIC OF", 'IQ' => "IRAQ", 'IE' => "IRELAND", 'IM' => "ISLE OF MAN", 'IL' => "ISRAEL", 'IT' => "ITALY", 'JM' => "JAMAICA", 'JP' => "JAPAN", 'JE' => "JERSEY", 'JO' => "JORDAN", 'KZ' => "KAZAKHSTAN", 'KE' => "KENYA", 'KI' => "KIRIBATI", 'KP' => "KOREA, DEMOCRATIC PEOPLE'S REPUBLIC OF", 'KR' => "KOREA, REPUBLIC OF", 'KW' => "KUWAIT", 'KG' => "KYRGYZSTAN", 'LA' => "LAO PEOPLE'S DEMOCRATIC REPUBLIC", 'LV' => "LATVIA", 'LB' => "LEBANON", 'LS' => "LESOTHO", 'LR' => "LIBERIA", 'LY' => "LIBYAN ARAB JAMAHIRIYA", 'LI' => "LIECHTENSTEIN", 'LT' => "LITHUANIA", 'LU' => "LUXEMBOURG", 'MO' => "MACAO", 'MK' => "MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF", 'MG' => "MADAGASCAR", 'MW' => "MALAWI", 'MY' => "MALAYSIA", 'MV' => "MALDIVES", 'ML' => "MALI", 'MT' => "MALTA", 'MH' => "MARSHALL ISLANDS", 'MQ' => "MARTINIQUE", 'MR' => "MAURITANIA", 'MU' => "MAURITIUS", 'YT' => "MAYOTTE", 'MX' => "MEXICO", 'FM' => "MICRONESIA, FEDERATED STATES OF", 'MD' => "MOLDOVA, REPUBLIC OF", 'MC' => "MONACO", 'MN' => "MONGOLIA", 'ME' => "MONTENEGRO", 'MS' => "MONTSERRAT", 'MA' => "MOROCCO", 'MZ' => "MOZAMBIQUE", 'MM' => "MYANMAR", 'NA' => "NAMIBIA", 'NR' => "NAURU", 'NP' => "NEPAL", 'NL' => "NETHERLANDS", 'AN' => "NETHERLANDS ANTILLES", 'NC' => "NEW CALEDONIA", 'NZ' => "NEW ZEALAND", 'NI' => "NICARAGUA", 'NE' => "NIGER", 'NG' => "NIGERIA", 'NU' => "NIUE", 'NF' => "NORFOLK ISLAND", 'MP' => "NORTHERN MARIANA ISLANDS", 'NO' => "NORWAY", 'OM' => "OMAN", 'PK' => "PAKISTAN", 'PW' => "PALAU", 'PS' => "PALESTINIAN TERRITORY, OCCUPIED", 'PA' => "PANAMA", 'PG' => "PAPUA NEW GUINEA", 'PY' => "PARAGUAY", 'PE' => "PERU", 'PH' => "PHILIPPINES", 'PN' => "PITCAIRN", 'PL' => "POLAND", 'PT' => "PORTUGAL", 'PR' => "PUERTO RICO", 'QA' => "QATAR", 'RE' => "REUNION", 'RO' => "ROMANIA", 'RU' => "RUSSIAN FEDERATION", 'RW' => "RWANDA", 'BL' => "SAINT BARTHÉLEMY", 'SH' => "SAINT HELENA", 'KN' => "SAINT KITTS AND NEVIS", 'LC' => "SAINT LUCIA", 'MF' => "SAINT MARTIN", 'PM' => "SAINT PIERRE AND MIQUELON", 'VC' => "SAINT VINCENT AND THE GRENADINES", 'WS' => "SAMOA", 'SM' => "SAN MARINO", 'ST' => "SAO TOME AND PRINCIPE", 'SA' => "SAUDI ARABIA", 'SN' => "SENEGAL", 'RS' => "SERBIA", 'SC' => "SEYCHELLES", 'SL' => "SIERRA LEONE", 'SG' => "SINGAPORE", 'SK' => "SLOVAKIA", 'SI' => "SLOVENIA", 'SB' => "SOLOMON ISLANDS", 'SO' => "SOMALIA", 'ZA' => "SOUTH AFRICA", 'GS' => "SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS", 'ES' => "SPAIN", 'LK' => "SRI LANKA", 'SD' => "SUDAN", 'SR' => "SURINAME", 'SJ' => "SVALBARD AND JAN MAYEN", 'SZ' => "SWAZILAND", 'SE' => "SWEDEN", 'CH' => "SWITZERLAND", 'SY' => "SYRIAN ARAB REPUBLIC", 'TW' => "TAIWAN, PROVINCE OF CHINA", 'TJ' => "TAJIKISTAN", 'TZ' => "TANZANIA, UNITED REPUBLIC OF", 'TH' => "THAILAND", 'TL' => "TIMOR-LESTE", 'TG' => "TOGO", 'TK' => "TOKELAU", 'TO' => "TONGA", 'TT' => "TRINIDAD AND TOBAGO", 'TN' => "TUNISIA", 'TR' => "TURKEY", 'TM' => "TURKMENISTAN", 'TC' => "TURKS AND CAICOS ISLANDS", 'TV' => "TUVALU", 'UG' => "UGANDA", 'UA' => "UKRAINE", 'AE' => "UNITED ARAB EMIRATES", 'UK' => "UNITED KINGDOM", 'US' => "UNITED STATES", 'UM' => "UNITED STATES MINOR OUTLYING ISLANDS", 'UY' => "URUGUAY", 'UZ' => "UZBEKISTAN", 'VU' => "VANUATU", 'VE' => "VENEZUELA", 'VN' => "VIET NAM", 'VG' => "VIRGIN ISLANDS, BRITISH", 'VI' => "VIRGIN ISLANDS, U.S.", 'WF' => "WALLIS AND FUTUNA", 'EH' => "WESTERN SAHARA", 'YE' => "YEMEN", 'ZM' => "ZAMBIA", 'ZW' => "ZIMBABWE" );

	// Load in the helper functions
	include( ICIT_WEATHER_PTH . '/includes/helpers.php' );

	add_action( 'widgets_init', array( 'icit_weather_widget', '_init' ), 1 );

	class icit_weather_widget extends WP_Widget {

		// Define variables and default settigns
		var $images = array();

		var $defaults = array(
			'title' => '',
			'city' => 'Liverpool',
			'frequency' => 60,
			'celsius' => true,
			'breakdown' => true,
			'mph' => true,
			'display' => 'compact',
			'credit' => true,
			'data' => array( ),
			'updated' => 0,
			'errors' => false,
			'country' => 'UK',
			'clear_errors' => false,
			'css' => true,
			'background_day' => '#FF7C80',
			'background_night' => '#FF7C80'
		);

		var $data = array();
		
		/*
		 Basic constructor.
		*/
		function __construct( ) {
			$widget_ops = array( 'classname' => __CLASS__, 'description' => __( 'Show the weather from a location you specify.', 'icit_weather' ) );
			$this->WP_Widget( __CLASS__, __( 'ICIT Weather', 'icit_weather'), $widget_ops);

			$this->images = apply_filters('icit_weather_widget_images', $this->images );
		}


		function widget( $args, $instance  ) {
			global $iso3166;
			
			// Include icon fonts	
			wp_enqueue_style('miriam86', ICIT_WEATHER_URL. '/images/miriam86/style.css');		
				
			extract( $args, EXTR_SKIP );

			$instance = wp_parse_args( $instance, $this->defaults );
			extract( $instance, EXTR_SKIP );

			// Update
			if ( empty( $data ) || intval( $updated ) + ( intval( $frequency ) * 60 ) < time( ) ) {
				// We need to run an update on the data
				$all_args = get_option( $this->option_name );

				$results = icit_fetch_open_weather( $city, $country, $display == 'extended' );

				if ( ! is_wp_error( $results ) ) {
					$data = $all_args[ $this->number ][ 'data' ] = $results;
					$updated = $all_args[ $this->number ][ 'updated' ] = time( );

					if( ! update_option( $this->option_name, $all_args ) )
						add_option( $this->option_name, $all_args );
				} else {
					// If we're looking for somewhere that's not there then we need to drop the cached data
					if ( $results->get_error_code( ) == 'bad_location' ) {
						unset( $data );
					}
					$this->add_error( $results );
				}
			}

			if ( ! empty( $data ) ) {

				// check the widget has class name and id
				if ( !preg_match('/class=\"/', $before_widget) )
					$before_widget = preg_replace("/^\<([a-zA-Z]+)/", '<$1 class="weather-widget"', $before_widget);
				if ( !preg_match('/id=\"/', $before_widget) )
					$before_widget = preg_replace("/^\<([a-zA-Z]+)/", '<$1 id="' . $this->id . '"', $before_widget);

				// add the display style to the widget's class
 				echo preg_replace('/class\=\"/', 'class="weather-'.$display.' ', $before_widget);

				// output the css if desired
				if ( $css )
					$this->css( $background_day, $background_night, $this->is_night( $data ) );
					
				// tidy up location name
				$location = array();
				if ( !empty( $city ) )
					$location[] = '<span class="weather-city">' . __( ucwords( $city ), 'icit_weather' ) . '</span>';
				if ( !empty( $country ) && array_key_exists( $country, $iso3166 ) )
					$location[] = '<span class="weather-country">' . __( ucwords( strtolower( $iso3166[ $country ] ) ), 'icit_weather' ) . '</span>';
				$location = implode(" ", $location);
				
				?>

				<div class="weather-wrapper">
					<div class="top">
						<div class="left">
							<div class="weather-temperature"><?php  echo $celsius ? round($data[ 'current' ][ 'temperature' ] ) . '&deg;C' : round( ($data[ 'current' ][ 'temperature' ] ) * 1.8 + 32 ) . '&deg;F' ; ?></div>
							<?php if ( $breakdown ) { ?>
								<div class="weather-condition"><?php echo ucwords( $this->get_weather( $data[ 'current' ][ 'number' ] ) ) ; ?></div>
							<?php } ?>
						</div>
						<?php if ( $breakdown ) { ?>
						<div class="right">
							<div class="weather-wind-condition"><?php printf( $mph ?  __( 'Wind: %1$smph %2$s', 'icit_weather' ) : __( 'Wind: %3$skm/h %2$s', 'icit_weather' ), round($data[ 'current' ][ 'speed'] * 2.24 ), $data[ 'current' ][ 'direction' ], round($data[ 'current' ][ 'speed' ] * 3.6 ) ); ?></div>
							<div class="weather-humidity"><?php printf( __( 'Humidity: %s%%', 'icit_weather' ), $data[ 'current' ][ 'humidity' ] ) ; ?></div>
						</div>
						<?php } ?>
						<div class="weather-icon">
						<?php echo $this->get_icon( $data[ 'current' ][ 'number' ], $data ); ?>
						</div>
						<div class="weather-location"><?php echo empty( $title ) ? sprintf( __( '%s', 'icit_weather' ), $location ) : sprintf( __( '%s', 'icit_weather' ), $title ); ?></div>

					</div>
					
				<?php
					// Handle extended mode
					if ( $display == 'extended' ) {
				?>
					<ul class="weather-forecast">
						<?php foreach( $data[ 'forecast' ] as $forecast ) {
							$day = date_i18n( 'D', strtotime( $forecast[ 'time' ] ) )
						?>
						<li>
							<div class="weather-day">
								<strong><?php  printf( __( '%s', 'icit_weather' ), $day ) ; ?></strong>
							</div>
							
							<div class="temp">
								<?php echo $celsius ? round( $forecast[ 'temperature' ] ) . '&deg;C' : round( ( $forecast[ 'temperature' ] ) * 1.8 + 32 ) . '&deg;F' ; ?> 
							</div>
							
							<div class="weather-icon-thumb">
								<?php echo $this->get_icon( $forecast[ 'number' ] ); ?>
							</div>
						</li>
						<?php } ?>
					</ul>
	
				<?php } ?>

					<!-- <?php printf( __( 'Last updated at %1$s on %2$s', 'icit_weather' ), date( get_option( 'time_format' ), $updated ), date( get_option( 'date_format' ), $updated ) ) ; ?> -->
				</div>  <?php
				

				if ( $credit ) {
					$interconnect = '<a href="http://interconnectit.com/" title="Wordpress Development Specialists">interconnect/<strong>it</strong></a>';
					printf( '<p class="icit-credit-link">'. __( 'Weather Widget by %s', 'icit_weather' ) .'</p>', $interconnect );
				}
				
				echo $after_widget; 
					 
			}
		}

		
		// Map the weather condition ID to our icon font
		function get_icon( $id, $data = false ) {
			
			$icons = array(
				
				200 => 'Thunder',
				300 => 'Drizzle',
				500 => 'Rain',
				511 => 'Sleet',
				520 => 'Drizzle',
				600 => 'Snow',
				700 => 'Mist',
				741 => 'Fog',
				800 => 'Sun',
				801 => 'Scattered_Cloud_Day',
				804 => 'Cloudy',
				903 => 'Snow',
				904 => 'Sun',
				906 => 'Hail'
			);
			
			if ( isset( $icons[ $id ] ) ) {
				$icon = $icons[ $id ];
			} else {
				foreach( array_reverse( $icons, true ) as $key => $name ) {
					if ( intval( $id ) > intval( $key ) ) {
						$icon = $name;
						break;
					}
				}
			}
			
			// Display different icons for night
			if ( $this->is_night( $data ) ) {

				if ( $id == 800 ) 
					$icon = 'Moon';
				
				if ( $id > 800 && $id < 804 )
					$icon = 'Scattered_Cloud_Night';
				
			}
			
			return '<i class="wicon-' . $icon . '"></i>';
		}
		
		// Determine whether it is night or day
		function is_night( $data ) {
			
			$night = false;
			
			if ( $data ) {
			
				$time = mktime( );
				$rise = strtotime( $data[ 'current' ][ 'rise' ] );
				$set = strtotime( $data[ 'current' ][ 'set' ] );
				
				if ( $time > $set || $time < $rise ) {
					$night = true;
				}
			}
			
			return $night;
		}
		
		// Map weather id to the weather condition to display
		function get_weather( $id ) {
			
			$weather = array(
				
				200 => 'thunder',
				300 => 'drizzle',
				314 => 'heavy drizzle',
				500 => 'rain',
				502 => 'heavy rain',
				511 => 'sleet',
				520 => 'showers',
				522 => 'heavy showers',
				600 => 'light snow',
				601 => 'snow',
				602 => 'heavy snow',
				700 => 'mist',
				711 => 'smoke',
				721 => 'haze',
				731 => 'dust whirls',
				741 => 'fog',
				751 => 'sand',
				761 => 'dust',
				762 => 'volcanic ash',
				771 => 'squalls',
				781 => 'tornado',
				800 => 'clear skies',
				801 => 'scattered clouds',
				802 => 'broken Clouds',
				804 => 'cloudy',
				900 => 'tornado',
				901 => 'tropical storm',
				902 => 'hurricane',
				903 => 'frosty',
				904 => 'hot',
				906 => 'hail',
				950 => 'calm',
				954 => 'breeze',
				957 => 'strong winds',
				960 => 'storm'
			);
			
			if ( isset( $weather[ $id] ) ) {
				$condition = $weather[ $id ];
			} else {
				foreach( array_reverse( $weather, true ) as $key => $name ) {
					if ( intval( $id ) > intval( $key ) ) {
						$condition = sprintf( __( '%s', 'icit_weather' ), $name );
						break;
					}
				}
			}
			
			return $condition;
		}
		

		/*
		 * @param $image = the image path returned by the OpenWeather API
		 * @param $thumb = false, if set to true the function will return the thumbnail url
		 * @return array( 'src' => filename, 'key' => $this->images array key )
		 */
		function check_image( $image, $thumb = false ) {
			// Break the file name into 2 parts name and ext. The array will have basename, name and ext
			preg_match( '/(.*)\.([a-zA-Z0-9]{3,4}$)/is', basename( $image ), $icon );

			if ( ! in_array( $icon[ 1 ], array_keys( $this->images ) ) ) {
				if ( file_exists( ICIT_WEATHER_PTH . '/images/' . $icon[ 0 ] ) )
					$icon[ 'filename' ] = $icon[ 0 ];
				elseif ( file_exists( ICIT_WEATHER_PTH . '/images/' . $icon[ 1 ] . '.png' ) )
					$icon[ 'filename' ] = $icon[ 1 ] . '.png';
				else
					$icon[ 'filename' ] = 'na.png';
			} else
				$icon[ 'filename' ] = $this->images[ $icon[ 1 ] ];


			if ( $thumb && file_exists( ICIT_WEATHER_PTH . '/images/' . str_replace(".png", "-thumb.png", $icon[ 'filename' ]) ) )
				$icon[ 'filename' ] = str_replace(".png", "-thumb.png", $icon[ 'filename' ]);

			return apply_filters('icit_weather_widget_check_image', array(
						'src' => ICIT_WEATHER_URL . '/images/' . $icon[ 'filename' ],
						'key' => $icon[ 1 ]
					), $image, $icon, $thumb);
		}
		
		function add_error( $error  = '') {
			$all_args = get_option( $this->option_name );
			$all_args[ $this->number ][ 'errors' ] = array( 'time' => time( ), 'message' => is_wp_error( $error ) ? $error->get_error_message( ) : ( string ) $error );

			if( ! update_option( $this->option_name, $all_args ) )
				add_option( $this->option_name, $all_args );
		}


		// Create the settings form
		function form( $instance  ) {
			
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script(
				'iris',
				admin_url( 'js/iris.min.js' ),
				array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ),
				false,
				1
			);
			wp_enqueue_script( 'script', ICIT_WEATHER_URL. '/js/script.js' );
			
			$instance = wp_parse_args( $instance, $this->defaults );
			extract( $instance, EXTR_SKIP );?>

			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'icit_weather' )?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
				<em><?php _e( 'This will override the display of the city name.', 'icit_weather' ); ?></em>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'country' ); ?>"><?php _e( 'Choose the country:', 'icit_weather' )?></label>
				<select id="<?php echo $this->get_field_id( 'country' ); ?>" name="<?php echo $this->get_field_name( 'country' ); ?>" class="widefat"><?php
					global $iso3166;
					foreach( ( array ) $iso3166 as $code => $country_name ) { ?>
						<option value="<?php echo esc_attr( $code ); ?>" <?php echo selected( strtolower( $country ), strtolower( $code ) )?>><?php echo htmlentities2( ucwords( strtolower( sprintf( __(  '%s', 'icit_weather' ), $country_name ) ) ), ENT_QUOTES, get_bloginfo( 'charset' ) ) ?></option><?php
					}?>
				</select>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'city' ); ?>"><?php _e( 'City, town, postcode or zip code:', 'icit_weather' )?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'city' ); ?>" name="<?php echo $this->get_field_name( 'city' ); ?>" type="text" value="<?php echo esc_attr( $city ); ?>" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'display' ); ?>"><?php _e( 'Widget display:', 'icit_weather' )?></label>
				<select id="<?php echo $this->get_field_id( 'display' ); ?>" name="<?php echo $this->get_field_name( 'display' ); ?>" class="widefat">
					<option <?php selected( $display, 'compact' ); ?> value="compact"><?php _e('Compact', 'icit_weather'); ?></option>
					<option <?php selected( $display, 'extended' ); ?> value="extended"><?php _e('Extended', 'icit_weather'); ?></option>
				</select>
			</p>
			
			<p>
				<label for="<?php echo $this->get_field_id( 'background_day' ); ?>"><?php _e( 'Background colour during day:', 'icit_weather' )?></label>
				<input class="widefat color-picker" id="<?php echo $this->get_field_id( 'background_day' ); ?>" name="<?php echo $this->get_field_name( 'background_day' ); ?>" type="text" value="<?php echo esc_attr( $background_day ); ?>" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'background_night' ); ?>"><?php _e( 'Background colour during night:', 'icit_weather' )?></label>
				<input class="widefat color-picker" id="<?php echo $this->get_field_id( 'background_night' ); ?>" name="<?php echo $this->get_field_name( 'background_night' ); ?>" type="text" value="<?php echo esc_attr( $background_night ); ?>" />
			</p>
			
			<p>
				<label for="<?php echo $this->get_field_id( 'frequency' ); ?>"><?php _e( 'How often do we check the weather (mins):', 'icit_weather' )?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'frequency' ); ?>" name="<?php echo $this->get_field_name( 'frequency' ); ?>" type="text" value="<?php echo esc_attr( $frequency ); ?>" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'celsius' ); ?>">
					<input type="checkbox" name="<?php echo $this->get_field_name( 'celsius' ); ?>" id="<?php echo $this->get_field_id( 'celsius' ); ?>" value="1" <?php echo checked( $celsius ); ?>/>
					<?php _e( 'Show temperature in celsius', 'icit_weather' ); ?>
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'breakdown' ); ?>">
					<input type="checkbox" name="<?php echo $this->get_field_name( 'breakdown' ); ?>" id="<?php echo $this->get_field_id( 'breakdown' ); ?>" value="1" <?php echo checked( $breakdown ); ?>/>
					<?php _e( 'Show weather breakdown', 'icit_weather' ); ?>
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'mph' ); ?>">
					<input type="checkbox" name="<?php echo $this->get_field_name( 'mph' ); ?>" id="<?php echo $this->get_field_id( 'mph' ); ?>" value="1" <?php echo checked( $mph ); ?>/>
					<?php _e( 'Show wind speed in mph', 'icit_weather' ); ?>
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'css' ); ?>">
					<input type="checkbox" name="<?php echo $this->get_field_name( 'css' ); ?>" id="<?php echo $this->get_field_id( 'css' ); ?>" value="1" <?php echo checked( $css ); ?>/>
					<?php _e( 'Output CSS', 'icit_weather' ); ?>
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'credit' ); ?>">
					<input type="checkbox" name="<?php echo $this->get_field_name( 'credit' ); ?>" id="<?php echo $this->get_field_id( 'credit' ); ?>" value="1" <?php echo checked( $credit ); ?>/>
					<?php _e( 'Show interconnect/it credit link', 'icit_weather' ); ?>
				</label>
			</p>

			<?php do_action('icit_weather_widget_form', $instance); ?>

			<p><em><?php printf( $updated > 0 ? __( 'Last updated "%1$s". Current server time is "%2$s".', 'icit_weather' ) : __( 'Will update when the frontend is next loaded. Current server time is %2$s.', 'icit_weather' ), date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $updated), date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), time( ) ) ); ?></em></p> <?php

			if ( ! empty( $instance[ 'errors' ] ) ) { ?>
			<div style="background-color: #FFEBE8;border:solid 1px #C00;padding:5px">
				<p><?php printf( __( 'The last error occured at "%s" with the message "%s".', 'icit_weather' ), date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $instance[ 'errors' ][ 'time' ] ), $instance[ 'errors' ][ 'message' ] ) ?></p>
				<label for="<?php echo $this->get_field_id( 'clear_errors' ); ?>"><?php _e( 'Clear errors: ', 'icit_weather' );?>
					<input type="checkbox" name="<?php echo $this->get_field_name( 'clear_errors' ); ?>" id="<?php echo $this->get_field_id( 'clear_errors' ); ?>" value="1" />
				</label>
			</div>
			<?php
			}
		}


		// Update to new settings
		function update( $new_instance, $old_instance = array( ) ) {
			global $iso3166;

			$instance[ 'title' ] = sanitize_text_field( $new_instance[ 'title' ] );
			$instance[ 'country' ] = in_array( $new_instance[ 'country' ], array_keys( ( array ) $iso3166 ) ) ? $new_instance[ 'country' ] : $this->defaults[ 'country' ];
			$instance[ 'city' ] = sanitize_text_field( isset( $new_instance[ 'city' ] ) ? $new_instance[ 'city' ] : $this->defaults[ 'city' ] );
			$instance[ 'frequency' ] = intval( $new_instance[ 'frequency' ] ) > 0 ? intval( $new_instance[ 'frequency' ] ) : $this->defaults[ 'frequency' ] ;
			$instance[ 'days' ] = intval( $new_instance[ 'days' ] ) > 0 ? intval( $new_instance[ 'days' ] ) : $this->defaults[ 'days' ] ;
			$instance[ 'display' ] = isset( $new_instance[ 'display' ] ) ? $new_instance[ 'display' ] : $this->defaults[ 'display' ] ;
			$instance[ 'background_day' ] = isset( $new_instance[ 'background_day' ] ) ? $new_instance[ 'background_day' ] : $this->defaults[ 'background_day' ] ;
			$instance[ 'background_night' ] = isset( $new_instance[ 'background_night' ] ) ? $new_instance[ 'background_night' ] : $this->defaults[ 'background_night' ] ;
			$instance[ 'celsius' ] = isset( $new_instance[ 'celsius' ] ) && ( bool ) $new_instance[ 'celsius' ] ? true : false;
			$instance[ 'breakdown' ] = isset( $new_instance[ 'breakdown' ] ) && ( bool ) $new_instance[ 'breakdown' ] ? true : false;
			$instance[ 'mph' ] = isset( $new_instance[ 'mph' ] ) && ( bool ) $new_instance[ 'mph' ] ? true : false;
			$instance[ 'credit' ] = isset( $new_instance[ 'credit' ] ) && ( bool ) $new_instance[ 'credit' ] ? true : false;
			$instance[ 'css' ] = isset( $new_instance[ 'css' ] ) && ( bool ) $new_instance[ 'css' ] ? true : false;
			$instance[ 'updated' ] = 0;
			$instance[ 'data' ] = isset( $new_instance[ 'city' ], $old_instance[ 'city' ], $new_instance[ 'country' ], $old_instance[ 'country' ] ) && $new_instance[ 'city' ] == $old_instance[ 'city' ] && $new_instance[ 'country' ] == $old_instance[ 'country' ] ? $old_instance[ 'data' ] : array( );

			if ( isset( $old_instance[ 'errors' ], $instance[ 'clear_errors '] ) && ! $instance[ 'clear_errors '] )
				$instance[ 'errors' ] = $old_instance[ 'errors' ];
			else
				$instance[ 'errors' ] = array( );

			do_action( 'icit_weather_widget_update', $new_instance, $old_instance, $instance );
			
			return $instance;
		}


		public static function _init (){
			register_widget( __CLASS__ );
		}


		function css( $background_day, $background_night, $night ) { ?>
<!-- ICIT Weather Widget CSS -->
<style type="text/css" media="screen">
#<?php echo $this->id ?> .weather-wrapper {
			  position: relative;
			  margin: 20px 0;
			}
			
#<?php echo $this->id ?> .weather-wrapper .top	{
			  background: <?php echo $night ? $background_night : $background_day; ?>;
			}

#<?php echo $this->id ?> .weather-wrapper .left {
			position: absolute;
			left: 0;
			top: 0;
			min-width: 50%;
			}
			
#<?php echo $this->id ?> .weather-wrapper .right {
			position: absolute;
			right: 0;
			top: 0;
			min-width: 50%;
			}
	
#<?php echo $this->id ?> .weather-wrapper .weather-temperature	{
			color: white;
			font-family: Trebuchet MS, Candara, sans-serif;
			font-size: 1.25em;
			font-weight: bold;
			text-align: left;
			padding-left: 3%;
			padding-top: 5px;
			}
			
#<?php echo $this->id ?> .weather-wrapper .weather-condition	{
			color: white;
			font-family: Trebuchet MS, Candara, sans-serif;
			font-size: 1.1em;
			text-align: left;
			padding-left: 3%;
			padding-top: 3px;
			}

#<?php echo $this->id ?> .weather-wrapper .weather-wind-condition	{
			color: white;
			font-family: Trebuchet MS, Candara, sans-serif;
			font-size: 1.1em;
			text-align: right;
			padding-right: 3%;
			padding-top: 5px;
			}

#<?php echo $this->id ?> .weather-wrapper .weather-humidity	{
			color: white;
			font-family: Trebuchet MS, Candara, sans-serif;
			font-size: 1.1em;
			text-align: right;
			padding-right: 3%;
			padding-top: 5px;
			}
			
#<?php echo $this->id ?> .weather-wrapper .weather-icon		{
			 text-align: center;
			 font-size: 7em;
			 padding-top: 50px;
			 padding-bottom: 10px;
			 color: white;
			}
			
#<?php echo $this->id ?> .weather-wrapper .weather-location	{
			color: white;
			font-family: Trebuchet MS, Candara, sans-serif;
			font-size: 1.25em;
			padding-bottom: 15px;
			font-weight: bold;
			text-align: center;
			}
			  
#<?php echo $this->id ?> .weather-forecast li::before	{
			display: none;
			}
			
			.weather-forecast {
			 background: white;
			 }
			
#<?php echo $this->id ?> .weather-forecast .weather-day  	{
			  color: <?php echo $night ? $background_night : $background_day; ?>;
			  padding-bottom: 10%;
			  padding-top: 10%;
			  }
			
#<?php echo $this->id ?> .weather-forecast .weather-icon-thumb  {
			 color: <?php echo $night ? $background_night : $background_day; ?>;
			 font-size: 2.5em;
			 padding-top: 4%;
			}
			
#<?php echo $this->id ?> .weather-forecast li	{
			background: none;
			float: left;
			display: block;
			text-align: center;
			padding: 5px 0 5px 0;
			margin-bottom: 20px;
			color: <?php echo $night ? $background_night : $background_day; ?>;
			width: 33.3333%;
			border: none;
			}
			
#<?php echo $this->id ?> .weather-forecast	{
			 overflow: hidden;
			}
			
#<?php echo $this->id ?> .weather-wrapper .weather-forecast	{
			  border: solid 2px;
			  border-color: <?php echo $night ? $background_night : $background_day; ?>;
			  margin: 0;
			  }

#<?php echo $this->id ?> .icit-credit-link a	{
			 color: <?php echo $night ? $background_night : $background_day;; ?>;
			}
			
			
@media all and (max-width: 925px) and (min-width: 673px) {

#<?php echo $this->id ?> .weather-wrapper .weather-wind-condition	{
			margin-top: 0px;
			padding-left: 3%;
			}
		

#<?php echo $this->id ?> .weather-wrapper .weather-humidity	{
			margin-top: 1px;
			padding-left: 3%;
			}
			
#<?php echo $this->id ?> .weather-wrapper .weather-icon		{
			 margin: 0 auto;
			}
}
	
	
@media all and (max-width: 672px) and (min-width: 405px) {
			
#<?php echo $this->id ?> .weather-wrapper .weather-icon		{
			font-size: 10em;
			}
}
		
			
@media all and (max-width: 340px) and (min-width: 209px) {

#<?php echo $this->id ?> .weather-wrapper .weather-icon		{
			 padding-top: 60px;
			}
}
	
</style>
<?php
		}
	}
}

?>