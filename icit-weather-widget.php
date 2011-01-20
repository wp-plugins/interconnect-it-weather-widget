<?php
/*
 Plugin Name: ICIT Weather widget
 Plugin URI: http://interconnectit.com/1474/wordpress-weather-widget/
 Description: The ICIT Weather Widget provides a simple way to show a weather forecast that can be styled to suit your theme and won't hit any usage limits.
 Version: 1.0.3
 Author: Interconnect IT, James R Whitehead, Robert O'Rourke
 Author URI: http://interconnectit.com
*/

/*
 Pete: Fixed the Zurich issue by changing the useragent, guess someone in Zuric
	upset Google with WordPress. :D
 James: Changed the class name on the extended forecast LI so it is prefixed
	with the word condition. Problems arose when the weather was "clear", too
	many themes have a class of clear that's there to force open float
	containers,	mine included.
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

	// Created from http://www.iso.org/iso/iso3166_en_code_lists.txt 15/6/2010
	// GB changed to UK
	$iso3166 = array( 'AF' => "AFGHANISTAN", 'AX' => "ÅLAND ISLANDS", 'AL' => "ALBANIA", 'DZ' => "ALGERIA", 'AS' => "AMERICAN SAMOA", 'AD' => "ANDORRA", 'AO' => "ANGOLA", 'AI' => "ANGUILLA", 'AQ' => "ANTARCTICA", 'AG' => "ANTIGUA AND BARBUDA", 'AR' => "ARGENTINA", 'AM' => "ARMENIA", 'AW' => "ARUBA", 'AU' => "AUSTRALIA", 'AT' => "AUSTRIA", 'AZ' => "AZERBAIJAN", 'BS' => "BAHAMAS", 'BH' => "BAHRAIN", 'BD' => "BANGLADESH", 'BB' => "BARBADOS", 'BY' => "BELARUS", 'BE' => "BELGIUM", 'BZ' => "BELIZE", 'BJ' => "BENIN", 'BM' => "BERMUDA", 'BT' => "BHUTAN", 'BO' => "BOLIVIA, PLURINATIONAL STATE OF", 'BA' => "BOSNIA AND HERZEGOVINA", 'BW' => "BOTSWANA", 'BV' => "BOUVET ISLAND", 'BR' => "BRAZIL", 'IO' => "BRITISH INDIAN OCEAN TERRITORY", 'BN' => "BRUNEI DARUSSALAM", 'BG' => "BULGARIA", 'BF' => "BURKINA FASO", 'BI' => "BURUNDI", 'KH' => "CAMBODIA", 'CM' => "CAMEROON", 'CA' => "CANADA", 'CV' => "CAPE VERDE", 'KY' => "CAYMAN ISLANDS", 'CF' => "CENTRAL AFRICAN REPUBLIC", 'TD' => "CHAD", 'CL' => "CHILE", 'CN' => "CHINA", 'CX' => "CHRISTMAS ISLAND", 'CC' => "COCOS (KEELING) ISLANDS", 'CO' => "COLOMBIA", 'KM' => "COMOROS", 'CG' => "CONGO", 'CD' => "CONGO, THE DEMOCRATIC REPUBLIC OF THE", 'CK' => "COOK ISLANDS", 'CR' => "COSTA RICA", 'CI' => "CÔTE D'IVOIRE", 'HR' => "CROATIA", 'CU' => "CUBA", 'CY' => "CYPRUS", 'CZ' => "CZECH REPUBLIC", 'DK' => "DENMARK", 'DJ' => "DJIBOUTI", 'DM' => "DOMINICA", 'DO' => "DOMINICAN REPUBLIC", 'EC' => "ECUADOR", 'EG' => "EGYPT", 'SV' => "EL SALVADOR", 'GQ' => "EQUATORIAL GUINEA", 'ER' => "ERITREA", 'EE' => "ESTONIA", 'ET' => "ETHIOPIA", 'FK' => "FALKLAND ISLANDS (MALVINAS)", 'FO' => "FAROE ISLANDS", 'FJ' => "FIJI", 'FI' => "FINLAND", 'FR' => "FRANCE", 'GF' => "FRENCH GUIANA", 'PF' => "FRENCH POLYNESIA", 'TF' => "FRENCH SOUTHERN TERRITORIES", 'GA' => "GABON", 'GM' => "GAMBIA", 'GE' => "GEORGIA", 'DE' => "GERMANY", 'GH' => "GHANA", 'GI' => "GIBRALTAR", 'GR' => "GREECE", 'GL' => "GREENLAND", 'GD' => "GRENADA", 'GP' => "GUADELOUPE", 'GU' => "GUAM", 'GT' => "GUATEMALA", 'GG' => "GUERNSEY", 'GN' => "GUINEA", 'GW' => "GUINEA-BISSAU", 'GY' => "GUYANA", 'HT' => "HAITI", 'HM' => "HEARD ISLAND AND MCDONALD ISLANDS", 'VA' => "HOLY SEE (VATICAN CITY STATE)", 'HN' => "HONDURAS", 'HK' => "HONG KONG", 'HU' => "HUNGARY", 'IS' => "ICELAND", 'IN' => "INDIA", 'ID' => "INDONESIA", 'IR' => "IRAN, ISLAMIC REPUBLIC OF", 'IQ' => "IRAQ", 'IE' => "IRELAND", 'IM' => "ISLE OF MAN", 'IL' => "ISRAEL", 'IT' => "ITALY", 'JM' => "JAMAICA", 'JP' => "JAPAN", 'JE' => "JERSEY", 'JO' => "JORDAN", 'KZ' => "KAZAKHSTAN", 'KE' => "KENYA", 'KI' => "KIRIBATI", 'KP' => "KOREA, DEMOCRATIC PEOPLE'S REPUBLIC OF", 'KR' => "KOREA, REPUBLIC OF", 'KW' => "KUWAIT", 'KG' => "KYRGYZSTAN", 'LA' => "LAO PEOPLE'S DEMOCRATIC REPUBLIC", 'LV' => "LATVIA", 'LB' => "LEBANON", 'LS' => "LESOTHO", 'LR' => "LIBERIA", 'LY' => "LIBYAN ARAB JAMAHIRIYA", 'LI' => "LIECHTENSTEIN", 'LT' => "LITHUANIA", 'LU' => "LUXEMBOURG", 'MO' => "MACAO", 'MK' => "MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF", 'MG' => "MADAGASCAR", 'MW' => "MALAWI", 'MY' => "MALAYSIA", 'MV' => "MALDIVES", 'ML' => "MALI", 'MT' => "MALTA", 'MH' => "MARSHALL ISLANDS", 'MQ' => "MARTINIQUE", 'MR' => "MAURITANIA", 'MU' => "MAURITIUS", 'YT' => "MAYOTTE", 'MX' => "MEXICO", 'FM' => "MICRONESIA, FEDERATED STATES OF", 'MD' => "MOLDOVA, REPUBLIC OF", 'MC' => "MONACO", 'MN' => "MONGOLIA", 'ME' => "MONTENEGRO", 'MS' => "MONTSERRAT", 'MA' => "MOROCCO", 'MZ' => "MOZAMBIQUE", 'MM' => "MYANMAR", 'NA' => "NAMIBIA", 'NR' => "NAURU", 'NP' => "NEPAL", 'NL' => "NETHERLANDS", 'AN' => "NETHERLANDS ANTILLES", 'NC' => "NEW CALEDONIA", 'NZ' => "NEW ZEALAND", 'NI' => "NICARAGUA", 'NE' => "NIGER", 'NG' => "NIGERIA", 'NU' => "NIUE", 'NF' => "NORFOLK ISLAND", 'MP' => "NORTHERN MARIANA ISLANDS", 'NO' => "NORWAY", 'OM' => "OMAN", 'PK' => "PAKISTAN", 'PW' => "PALAU", 'PS' => "PALESTINIAN TERRITORY, OCCUPIED", 'PA' => "PANAMA", 'PG' => "PAPUA NEW GUINEA", 'PY' => "PARAGUAY", 'PE' => "PERU", 'PH' => "PHILIPPINES", 'PN' => "PITCAIRN", 'PL' => "POLAND", 'PT' => "PORTUGAL", 'PR' => "PUERTO RICO", 'QA' => "QATAR", 'RE' => "REUNION", 'RO' => "ROMANIA", 'RU' => "RUSSIAN FEDERATION", 'RW' => "RWANDA", 'BL' => "SAINT BARTHÉLEMY", 'SH' => "SAINT HELENA", 'KN' => "SAINT KITTS AND NEVIS", 'LC' => "SAINT LUCIA", 'MF' => "SAINT MARTIN", 'PM' => "SAINT PIERRE AND MIQUELON", 'VC' => "SAINT VINCENT AND THE GRENADINES", 'WS' => "SAMOA", 'SM' => "SAN MARINO", 'ST' => "SAO TOME AND PRINCIPE", 'SA' => "SAUDI ARABIA", 'SN' => "SENEGAL", 'RS' => "SERBIA", 'SC' => "SEYCHELLES", 'SL' => "SIERRA LEONE", 'SG' => "SINGAPORE", 'SK' => "SLOVAKIA", 'SI' => "SLOVENIA", 'SB' => "SOLOMON ISLANDS", 'SO' => "SOMALIA", 'ZA' => "SOUTH AFRICA", 'GS' => "SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS", 'ES' => "SPAIN", 'LK' => "SRI LANKA", 'SD' => "SUDAN", 'SR' => "SURINAME", 'SJ' => "SVALBARD AND JAN MAYEN", 'SZ' => "SWAZILAND", 'SE' => "SWEDEN", 'CH' => "SWITZERLAND", 'SY' => "SYRIAN ARAB REPUBLIC", 'TW' => "TAIWAN, PROVINCE OF CHINA", 'TJ' => "TAJIKISTAN", 'TZ' => "TANZANIA, UNITED REPUBLIC OF", 'TH' => "THAILAND", 'TL' => "TIMOR-LESTE", 'TG' => "TOGO", 'TK' => "TOKELAU", 'TO' => "TONGA", 'TT' => "TRINIDAD AND TOBAGO", 'TN' => "TUNISIA", 'TR' => "TURKEY", 'TM' => "TURKMENISTAN", 'TC' => "TURKS AND CAICOS ISLANDS", 'TV' => "TUVALU", 'UG' => "UGANDA", 'UA' => "UKRAINE", 'AE' => "UNITED ARAB EMIRATES", 'UK' => "UNITED KINGDOM", 'US' => "UNITED STATES", 'UM' => "UNITED STATES MINOR OUTLYING ISLANDS", 'UY' => "URUGUAY", 'UZ' => "UZBEKISTAN", 'VU' => "VANUATU", 'VE' => "VENEZUELA", 'VN' => "VIET NAM", 'VG' => "VIRGIN ISLANDS, BRITISH", 'VI' => "VIRGIN ISLANDS, U.S.", 'WF' => "WALLIS AND FUTUNA", 'EH' => "WESTERN SAHARA", 'YE' => "YEMEN", 'ZM' => "ZAMBIA", 'ZW' => "ZIMBABWE" );

	// Load in the helper functions
	include( ICIT_WEATHER_PTH . '/includes/helpers.php' );

	add_action( 'widgets_init', array( 'icit_weather_widget', '_init' ), 1 );

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
							  'days' => 1,
							  'display' => 'compact',
							  'credit' => true,
							  'data' => array( ),
							  'updated' => 0,
							  'errors' => false,
							  'country' => 'UK',
							  'clear_errors' => false,
							  'css' => true
							);

		/*
		 Basic constructor.
		*/
		function icit_weather_widget( ) {
			$widget_ops = array( 'classname' => __CLASS__, 'description' => __( 'Show the weather from a location you specify.', ICIT_WEATHER_DOM ) );
			$this->WP_Widget( __CLASS__, __( 'ICIT Weather', ICIT_WEATHER_DOM), $widget_ops);
		}


		function widget( $args, $instance  ) {
			global $iso3166;

			extract( $args, EXTR_SKIP );

			$instance = wp_parse_args( $instance, $this->defaults );
			extract( $instance, EXTR_SKIP );

			// Update
			if ( empty( $data ) || intval( $updated ) + ( intval( $frequency ) * 60 ) < time( ) ) {
				// We need to run an update on the data
				$all_args = get_option( $this->option_name );

				$results = icit_fetch_google_weather( $city, $country, $display == 'compact' || $days > 1 ? true : false );

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

				// Check that we have a local image mapped to the name expected or try the filename or finally use na.png
				$image = $this->check_image( $data[ 'current' ][ 'icon' ] );

				// check the widget has class name and id
				if ( !preg_match('/class=\"/', $before_widget) )
					$before_widget = preg_replace("/^\<([a-zA-Z]+)/", '<$1 class="weather-widget"', $before_widget);
				if ( !preg_match('/id=\"/', $before_widget) )
					$before_widget = preg_replace("/^\<([a-zA-Z]+)/", '<$1 id="' . $this->id . '"', $before_widget);

				// add the display style to the widget's class
 				echo preg_replace('/class\=\"/', 'class="weather-'.$display.' ', $before_widget);

				// output the css if desired
				if ( $css )
					$this->css();

				// tidy up location name
				$location = array();
				if ( !empty( $city ) && $data[ 'forecast_info' ][ 'city' ] == $data[ 'forecast_info' ][ 'postal_code' ] )
					$location[] = '<span class="weather-city">' . ucwords( $city ) . '</span>';
				if ( !empty( $city ) && $data[ 'forecast_info' ][ 'city' ] != $data[ 'forecast_info' ][ 'postal_code' ] )
					$location[] = '<span class="weather-city">' . $data[ 'forecast_info' ][ 'city' ] . '</span>';
				if ( !empty( $country ) && array_key_exists( $country, $iso3166 ) )
					$location[] = '<span class="weather-country">' . ucwords( strtolower( $iso3166[ $country ] ) ) . '</span>';
				$location = implode(" ", $location);
				?>

				<div class="weather-wrapper">

				<?php if ( $display == 'extended' ) { ?>

					<div class="weather-icon">
						<!--[if lt IE 7]><div style="width:160px;height:103px;filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php echo $image[ 'src' ]; ?>');"></div><div style="display:none"><![endif]-->
						<img src="<?php if(!empty($image[ 'src' ])){ echo $image[ 'src' ];}else{echo plugins_url(basename(dirname(__FILE__))).'/images/na.png' ;} ?>" alt="<?php echo esc_attr( $data[ 'current' ][ 'condition' ] );?>" width="160" height="103" />
						<!--[if lt IE 7]></div><![endif]-->
					</div>
					<div class="weather-location"><?php echo empty( $title ) ? $location : $title; ?></div>
					<div class="weather-temperature"><?php echo $celsius ? $data[ 'current' ][ 'temp_c' ] . '&deg;C' : $data[ 'current' ][ 'temp_f' ] . '&deg;F' ; ?></div>
					<div class="weather-condition"><?php echo $data[ 'current' ][ 'condition' ]; ?></div>
					<div class="weather-humidity"><?php echo $data[ 'current' ][ 'humidity' ]; ?></div>
					<div class="weather-wind-condition"><?php echo $data[ 'current' ][ 'wind_condition' ]; ?></div>

				<?php } ?>

				<?php
					// handle compact mode or subsequent days
					if ( $display == 'compact' || $days > 1 ) {
					$i = 0;
				?>
				<?php if ( $display == 'compact' ) { ?>
					<div class="weather-location"><?php echo empty( $title ) ? $location : $title; ?></div>
				<?php } ?>
					<ul class="weather-forecast">
					<?php foreach( $data[ 'forecast' ] as $day => $day_data ) {
						// limit days
						if ( $i == $days )
							break;
						// skip iteration if today is shown in extended mode
						if ( $display == 'extended' && $i == 0 ){
							$i++; continue;
						}

						$image = $this->check_image( $day_data[ 'icon' ], true );
					?>
						<li class="<?php echo strtolower( $day ); ?> <?php echo strtolower( preg_replace( "/\s/", "-", trim( $day_data[ 'condition' ] ) ) ); ?>" title="<?php esc_attr_e( $day_data[ 'condition' ] ); ?>">
							<div class="weather-icon-thumb">
								<!--[if lt IE 7]><div style="width:50px;height:32px;filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php echo $image[ 'src' ]; ?>');"></div><div style="display:none"><![endif]-->
								<img src="<?php echo $image[ 'src' ]; ?>" alt="<?php echo esc_attr( $image[ 'condition' ] );?>" width="50" height="32" />
								<!--[if lt IE 7]></div><![endif]-->
							</div>
							<div class="weather-day"><strong><?php echo $i == 0 ? __('Today', ICIT_WEATHER_DOM) : $day; ?></strong></div>
							<div class="weather-hilo">
								<span class="weather-high"><?php echo $celsius ? $this->f_to_c( $day_data[ 'high' ] ) . '<span class="deg">&deg;<span class="celsius">C</span></span>' : $day_data[ 'high' ] . '<span class="deg">&deg;<span class="farenheit">F</span></span>'; ?></span>
								<span class="weather-separator">/</span>
								<span class="weather-low"><?php echo $celsius ? $this->f_to_c( $day_data[ 'low' ] ) . '<span class="deg">&deg;<span class="celsius">C</span></span>' : $day_data[ 'low' ] . '<span class="deg">&deg;<span class="farenheit">F</span></span>'; ?></span>
							</div>
						</li>
					<?php $i++; } ?>
					</ul>

				<?php } ?>

					<!-- <?php printf( __( 'Last updated at %1$s on %2$s', ICIT_WEATHER_DOM ), date( get_option( 'time_format' ), $updated ), date( get_option( 'date_format' ), $updated ) ) ; ?> -->
				</div> <?php

				if ( $credit )
					echo '<p class="icit-credit-link">'. __('Weather Widget by <a href="http://interconnectit.com/" title="Wordpress Development Specialists">Interconnect/IT</a>', ICIT_WEATHER_DOM) .'</p>';

				echo $after_widget;
			}
		}

		/*
		 * @param $image = the image path returned by the google API
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

			return array(
						'src' => ICIT_WEATHER_URL . '/images/' . $icon[ 'filename' ],
						'key' => $icon[ 1 ]
						);
		}

		// convert farenheit to celsius
		function f_to_c( $deg ) {
			return round( (5/9)*($deg-32) );
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
				<label for="<?php echo $this->get_field_id( 'display' ); ?>"><?php _e( 'Widget display:', ICIT_WEATHER_DOM )?></label>
				<select id="<?php echo $this->get_field_id( 'display' ); ?>" name="<?php echo $this->get_field_name( 'display' ); ?>" class="widefat">
					<option <?php selected( $display, 'compact' ); ?> value="compact"><?php _e('Compact', ICIT_WEATHER_DOM); ?></option>
					<option <?php selected( $display, 'extended' ); ?> value="extended"><?php _e('Extended', ICIT_WEATHER_DOM); ?></option>
				</select>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'days' ); ?>"><?php _e( 'Show forecast for:', ICIT_WEATHER_DOM )?></label>
				<select id="<?php echo $this->get_field_id( 'days' ); ?>" name="<?php echo $this->get_field_name( 'days' ); ?>" class="widefat"><?php
				for( $i=1; $i<5; $i++ ) { ?>
					<option <?php selected($days,$i); ?> value="<?php echo $i; ?>"><?php printf( $i==1 ? __('Today only', ICIT_WEATHER_DOM) : __('%s days', ICIT_WEATHER_DOM), $i); ?></option><?php
				} ?></select>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'frequency' ); ?>"><?php _e( 'How often do we check the weather (mins):', ICIT_WEATHER_DOM )?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'frequency' ); ?>" name="<?php echo $this->get_field_name( 'frequency' ); ?>" type="text" value="<?php echo esc_attr( $frequency ); ?>" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'celsius' ); ?>">
					<input type="checkbox" name="<?php echo $this->get_field_name( 'celsius' ); ?>" id="<?php echo $this->get_field_id( 'celsius' ); ?>" value="1" <?php echo checked( $celsius ); ?>/>
					<?php _e( 'Show temperature in celsius', ICIT_WEATHER_DOM );?>
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'css' ); ?>">
					<input type="checkbox" name="<?php echo $this->get_field_name( 'css' ); ?>" id="<?php echo $this->get_field_id( 'css' ); ?>" value="1" <?php echo checked( $css ); ?>/>
					<?php _e( 'Output CSS', ICIT_WEATHER_DOM );?>
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'credit' ); ?>">
					<input type="checkbox" name="<?php echo $this->get_field_name( 'credit' ); ?>" id="<?php echo $this->get_field_id( 'credit' ); ?>" value="1" <?php echo checked( $credit ); ?>/>
					<?php _e( 'Show Interconnect IT credit link', ICIT_WEATHER_DOM );?>
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
			$instance[ 'days' ] = intval( $new_instance[ 'days' ] ) > 0 ? intval( $new_instance[ 'days' ] ) : $this->defaults[ 'days' ] ;
			$instance[ 'display' ] = isset( $new_instance[ 'display' ] ) ? $new_instance[ 'display' ] : $this->defaults[ 'display' ] ;
			$instance[ 'celsius' ] = isset( $new_instance[ 'celsius' ] ) && ( bool ) $new_instance[ 'celsius' ] ? true : false;
			$instance[ 'credit' ] = isset( $new_instance[ 'credit' ] ) && ( bool ) $new_instance[ 'credit' ] ? true : false;
			$instance[ 'css' ] = isset( $new_instance[ 'css' ] ) && ( bool ) $new_instance[ 'css' ] ? true : false;
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
<!-- ICIT Weather Widget CSS -->
<style type="text/css" media="screen" >
#<?php echo $this->id ?> .weather-wrapper {border:solid 2px #ADC0CF;background:url('<?php echo ICIT_WEATHER_URL; ?>/images/background.png') repeat-x bottom left #F4FFFF;text-align:center;position:relative;padding:10px 10px 10px 10px;margin: 20px 0;/* CSS 3 Stuff */background:-webkit-gradient(linear,0% 20%,0% 100%,from(#F4FFFF),to(#d2e5f3));background:-moz-linear-gradient( 80% 100% 90deg,#d2e5f3,#F4FFFF);-moz-border-radius:5px;-moz-box-shadow:1px 1px 4px rgba(0,0,0,0.2);box-shadow:1px 1px 4px rgba(0,0,0,0.2);-webkit-border-radius:5px;-webkit-box-shadow:1px 1px 4px rgba(0,0,0,0.2);border-radius:7px;}
#<?php echo $this->id ?> .weather-wrapper .weather-location { font-weight: bold; }
#<?php echo $this->id ?> .weather-wrapper .weather-location .weather-country { display: block; font-size: 12px; }
#<?php echo $this->id ?> .weather-wrapper .weather-forecast { margin: 10px auto 0; width: 200px; padding: 0; list-style: none; text-align: left; background: none; }
#<?php echo $this->id ?> .weather-wrapper .weather-forecast li { overflow: hidden; line-height: 32px; margin: 0; padding: 2px 0; list-style: none; text-align: left; background: none; }
#<?php echo $this->id ?> .weather-wrapper .weather-icon-thumb { display: inline-block; width: 50px; vertical-align: middle; float: left; }
#<?php echo $this->id ?> .weather-wrapper .weather-day { display: inline-block; width: 50px; float: left; }
#<?php echo $this->id ?> .weather-wrapper .weather-hilo { display: inline-block; width: auto; float: left; }
#<?php echo $this->id ?>.weather-compact  .weather-location { margin-top: 5px; }
#<?php echo $this->id ?>.weather-extended .weather-wrapper {padding:50px 10px 10px 10px;margin:50px 0 20px;}
#<?php echo $this->id ?>.weather-extended .weather-wrapper .weather-icon {position:absolute;top:-50px;left:50%;margin-left:-80px;text-align:center;}
#<?php echo $this->id ?>.weather-extended .weather-temperature {display:block;font-size:34px;height:34px;line-height:40px;margin:2px auto 10px;text-shadow:1px 1px 1px #fff}
#<?php echo $this->id ?>.weather-extended .weather-forecast { margin-top: 10px; }
#<?php echo $this->id ?> .icit-credit-link { margin: 20px 0; font-size: 10px; }
* html #<?php echo $this->id ?>.weather-extended .weather-wrapper .weather-icon {left:0;}
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
