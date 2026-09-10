<?php
/**
 * Theme settings (Appearance → Customize → Vision Studios).
 */
defined( 'ABSPATH' ) || exit;

function vs_settings(): array {
	return [
		'contact' => [ __( 'Contact details', 'vision-studios' ), [
			'email'         => [ __( 'Booking email', 'vision-studios' ), 'text', 'booking@vision-studios.net' ],
			'phone_uk'      => [ __( 'Phone — United Kingdom', 'vision-studios' ), 'text', '+44 (0) 7920 745134' ],
			'phone_eu'      => [ __( 'Phone — Europe', 'vision-studios' ), 'text', '+353 (0) 8961 14641' ],
			'phone_tr'      => [ __( 'Phone — Turkey', 'vision-studios' ), 'text', '+90 212 988 10 45' ],
			'hq_address'    => [ __( 'HQ address', 'vision-studios' ), 'text', 'Vision Studios, Kendal Avenue, London W3 0XA' ],
			'opening_hours' => [ __( 'Opening hours', 'vision-studios' ), 'text', 'Mon–Sun 09:00–17:00' ],
			'instagram'     => [ __( 'Instagram URL', 'vision-studios' ), 'url', 'https://www.instagram.com/visionstudioslondon/' ],
			'linkedin'      => [ __( 'LinkedIn URL', 'vision-studios' ), 'url', 'https://www.linkedin.com/company/vision-studios-london/' ],
		] ],
		'hero' => [ __( 'Home page hero', 'vision-studios' ), [
			'hero_image'     => [ __( 'Hero image', 'vision-studios' ), 'image', '' ],
			'hero_left'      => [ __( 'Eyebrow left', 'vision-studios' ), 'text', '25+ Years — 4 Cities' ],
			'hero_center'    => [ __( 'Eyebrow centre', 'vision-studios' ), 'text', 'Broadcast · Film · Podcast' ],
			'hero_right'     => [ __( 'Eyebrow right', 'vision-studios' ), 'text', '51.5074° N / -0.1278° W' ],
			'hero_intro'     => [ __( 'Intro paragraph', 'vision-studios' ), 'textarea', 'Premier Broadcast Studios Rentals in Four Iconic Cities. Fully equipped, modern studios for live news, TV shows, corporate advertising, virtual events and podcasts.' ],
			'stat1_value'    => [ __( 'Stat 1 value', 'vision-studios' ), 'text', '25+' ],  'stat1_label' => [ __( 'Stat 1 label', 'vision-studios' ), 'text', 'Years of Experience' ],
			'stat2_value'    => [ __( 'Stat 2 value', 'vision-studios' ), 'text', '215' ],  'stat2_label' => [ __( 'Stat 2 label', 'vision-studios' ), 'text', 'Sq. Mt. Flagship Studio' ],
			'stat3_value'    => [ __( 'Stat 3 value', 'vision-studios' ), 'text', '4' ],    'stat3_label' => [ __( 'Stat 3 label', 'vision-studios' ), 'text', 'Capital Cities' ],
			'stat4_value'    => [ __( 'Stat 4 value', 'vision-studios' ), 'text', '99.9%' ],'stat4_label' => [ __( 'Stat 4 label', 'vision-studios' ), 'text', 'Uptime Guarantee' ],
		] ],
		'home' => [ __( 'Home page sections', 'vision-studios' ), [
			'why_intro'       => [ __( '"Why Vision" intro', 'vision-studios' ), 'textarea', '' ],
			'choose_intro'    => [ __( '"Why choose Vision Studios" intro', 'vision-studios' ), 'textarea', '' ],
			'counter1_value'  => [ __( 'Counter 1 value', 'vision-studios' ), 'number', '1000' ], 'counter1_label' => [ __( 'Counter 1 label', 'vision-studios' ), 'text', 'Yearly Productions' ], 'counter1_suffix' => [ __( 'Counter 1 suffix', 'vision-studios' ), 'text', '+' ],
			'counter2_value'  => [ __( 'Counter 2 value', 'vision-studios' ), 'number', '128' ],  'counter2_label' => [ __( 'Counter 2 label', 'vision-studios' ), 'text', 'Successful Projects' ], 'counter2_suffix' => [ __( 'Counter 2 suffix', 'vision-studios' ), 'text', '' ],
			'counter3_value'  => [ __( 'Counter 3 value', 'vision-studios' ), 'number', '25' ],   'counter3_label' => [ __( 'Counter 3 label', 'vision-studios' ), 'text', 'Years of experience' ], 'counter3_suffix' => [ __( 'Counter 3 suffix', 'vision-studios' ), 'text', '' ],
			'cta_heading'     => [ __( 'Contact band heading (HTML allowed)', 'vision-studios' ), 'textarea', 'Let\'s Make<br><span class="text-accent">Something Worth<br>Watching.</span>' ],
			'cta_text'        => [ __( 'Contact band text', 'vision-studios' ), 'textarea', 'Tell us about your production. We\'ll match you with a studio, a crew and a quote — usually within a working day.' ],
		] ],
		'forms' => [ __( 'Forms', 'vision-studios' ), [
			'cf7_booking' => [ __( 'Contact Form 7 shortcode — studio booking form', 'vision-studios' ), 'text', '' ],
			'cf7_contact' => [ __( 'Contact Form 7 shortcode — contact page form', 'vision-studios' ), 'text', '' ],
			'forms_help'  => [ __( 'Leave both empty to use the built-in forms, which open the visitor\'s email client with a pre-filled message.', 'vision-studios' ), 'note', '' ],
		] ],
	];
}

/** Get a theme setting with its default. */
function vs_opt( string $key ) {
	static $defaults = null;
	if ( null === $defaults ) {
		$defaults = [];
		foreach ( vs_settings() as $section ) {
			foreach ( $section[1] as $k => $d ) {
				$defaults[ $k ] = $d[2];
			}
		}
	}
	return get_theme_mod( "vs_$key", $defaults[ $key ] ?? '' );
}

add_action( 'customize_register', function ( WP_Customize_Manager $wp ) {
	$wp->add_panel( 'vs', [ 'title' => __( 'Vision Studios', 'vision-studios' ), 'priority' => 10 ] );
	foreach ( vs_settings() as $sid => [ $title, $fields ] ) {
		$wp->add_section( "vs_$sid", [ 'title' => $title, 'panel' => 'vs' ] );
		foreach ( $fields as $key => [ $label, $type, $default ] ) {
			if ( 'note' === $type ) {
				$wp->add_setting( "vs_$key", [ 'sanitize_callback' => '__return_empty_string' ] );
				$wp->add_control( "vs_$key", [ 'section' => "vs_$sid", 'type' => 'hidden', 'description' => $label ] );
				continue;
			}
			$sanitizers = [ 'url' => 'esc_url_raw', 'image' => 'absint', 'number' => 'absint', 'textarea' => 'wp_kses_post' ];
			$sanitize   = $sanitizers[ $type ] ?? 'sanitize_text_field';
			$wp->add_setting( "vs_$key", [ 'default' => $default, 'sanitize_callback' => $sanitize ] );
			if ( 'image' === $type ) {
				$wp->add_control( new WP_Customize_Media_Control( $wp, "vs_$key", [ 'label' => $label, 'section' => "vs_$sid", 'mime_type' => 'image' ] ) );
			} else {
				$wp->add_control( "vs_$key", [ 'label' => $label, 'section' => "vs_$sid", 'type' => $type ] );
			}
		}
	}
} );
