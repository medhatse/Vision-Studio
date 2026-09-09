<?php
/**
 * 301 redirects from the old page-based URLs (vision-studios.net/london-studio-1/ …) to the new structure.
 * Only fires when the old URL would otherwise 404 (e.g. after the importer drafts the old pages).
 */
defined( 'ABSPATH' ) || exit;

add_action( 'template_redirect', function () {
	if ( ! is_404() ) {
		return;
	}
	$path = trim( (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
	if ( '' === $path ) {
		return;
	}
	$target = '';
	if ( $studio = get_page_by_path( $path, OBJECT, 'studio' ) ) {
		$target = get_permalink( $studio );
	} elseif ( $term = get_term_by( 'slug', $path, 'city' ) ) {
		$target = get_term_link( $term );
	} else {
		$map = [ 'about-us' => 'about', 'contact-us' => 'contact', 'blogs' => 'news', 'category/news' => 'news', 'the-vision-studios' => '' ];
		if ( array_key_exists( $path, $map ) ) {
			$page   = $map[ $path ] ? get_page_by_path( $map[ $path ] ) : null;
			$target = $page ? get_permalink( $page ) : home_url( '/' );
		}
	}
	if ( $target && ! is_wp_error( $target ) ) {
		wp_safe_redirect( $target, 301 );
		exit;
	}
} );
