<?php
/**
 * Plugin compatibility: keep Elementor to the posts that use it, and force https on same-domain assets.
 */
defined( 'ABSPATH' ) || exit;

/** True when the current singular view was built with Elementor (news posts on vision-studios.net). */
function vs_is_elementor_view(): bool {
	if ( ! is_singular() || ! class_exists( '\Elementor\Plugin' ) ) {
		return false;
	}
	$doc = \Elementor\Plugin::$instance->documents->get( get_queried_object_id() );
	return $doc && $doc->is_built_with_elementor();
}

// Elementor enqueues its frontend CSS/JS, widget styles, kit CSS, Font Awesome and locally hosted Google
// Fonts on every page. The theme uses none of it, so drop it everywhere except Elementor-built posts.
add_action( 'wp_enqueue_scripts', function () {
	if ( is_admin() || vs_is_elementor_view() ) {
		return;
	}
	$pattern = '/^(elementor|e-|widget-|font-awesome|eicons|swiper|elementor-gf-)/';
	foreach ( wp_styles()->queue as $handle ) {
		if ( preg_match( $pattern, $handle ) ) {
			wp_dequeue_style( $handle );
		}
	}
	foreach ( wp_scripts()->queue as $handle ) {
		if ( preg_match( $pattern, $handle ) ) {
			wp_dequeue_script( $handle );
		}
	}
}, 100 );

// Elementor's locally hosted Google Fonts stylesheet was generated with an http:// base URL, so browsers
// block it as mixed content on https pages anyway. Drop it on Elementor posts too (text falls back to the
// theme fonts) until Elementor → Tools → Replace URL has been run.
add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_ssl() ) {
		return;
	}
	foreach ( wp_styles()->registered as $handle => $style ) {
		if ( str_starts_with( $handle, 'elementor-gf-' ) && is_string( $style->src ) && 0 === strpos( $style->src, 'http://' ) ) {
			wp_dequeue_style( $handle );
		}
	}
}, 101 );

// Same-domain asset URLs stored with http:// (old uploads, Elementor data) → https when the site is https.
function vs_force_https_url( $url ) {
	if ( is_string( $url ) && is_ssl() && 0 === strpos( $url, 'http://' ) ) {
		$host = wp_parse_url( home_url(), PHP_URL_HOST );
		if ( $host && 0 === strpos( $url, 'http://' . $host ) ) {
			return 'https://' . substr( $url, 7 );
		}
	}
	return $url;
}
foreach ( [ 'style_loader_src', 'script_loader_src', 'wp_get_attachment_url', 'wp_get_attachment_image_src', 'the_content', 'widget_text' ] as $vs_hook ) {
	add_filter( $vs_hook, function ( $value ) {
		if ( is_array( $value ) && isset( $value[0] ) ) {
			$value[0] = vs_force_https_url( $value[0] );
			return $value;
		}
		if ( is_string( $value ) && is_ssl() ) {
			$host = wp_parse_url( home_url(), PHP_URL_HOST );
			return $host ? str_replace( 'http://' . $host . '/', 'https://' . $host . '/', $value ) : $value;
		}
		return $value;
	}, 20 );
}

// Rank Math structured data: same treatment for the Organization logo and any other stored http URL.
add_filter( 'rank_math/json_ld', function ( $data ) {
	if ( ! is_ssl() ) {
		return $data;
	}
	$host = wp_parse_url( home_url(), PHP_URL_HOST );
	$fix  = function ( $v ) use ( &$fix, $host ) {
		if ( is_array( $v ) ) {
			return array_map( $fix, $v );
		}
		return is_string( $v ) ? str_replace( 'http://' . $host . '/', 'https://' . $host . '/', $v ) : $v;
	};
	return $fix( $data );
}, 99 );
