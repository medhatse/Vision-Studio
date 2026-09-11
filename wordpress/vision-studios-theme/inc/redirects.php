<?php
/**
 * Legacy URLs: 301 redirects from the old page-based URLs (vision-studios.net/london-studio-1/, ?page_id=378 …)
 * to the new structure, and the same mapping applied to menu items that still point at the old pages.
 */
defined( 'ABSPATH' ) || exit;

/** New URL for an old page slug, or '' when there is no mapping. */
function vs_legacy_target( string $slug ): string {
	$slug = trim( $slug, '/' );
	if ( '' === $slug ) {
		return '';
	}
	if ( $studio = get_page_by_path( $slug, OBJECT, 'studio' ) ) {
		return (string) get_permalink( $studio );
	}
	if ( $term = get_term_by( 'slug', $slug, 'city' ) ) {
		$l = get_term_link( $term );
		return is_wp_error( $l ) ? '' : $l;
	}
	$map = [ 'about-us' => 'about', 'contact-us' => 'contact', 'blogs' => 'news', 'category/news' => 'news', 'the-vision-studios' => '', 'home' => '' ];
	if ( array_key_exists( $slug, $map ) ) {
		$page = $map[ $slug ] ? get_page_by_path( $map[ $slug ] ) : null;
		return $page ? (string) get_permalink( $page ) : home_url( '/' );
	}
	return '';
}

add_action( 'template_redirect', function () {
	$target = '';
	// ?page_id=N / ?p=N links to pages the importer set to Draft (old menus, external links).
	$pid = (int) ( $_GET['page_id'] ?? $_GET['p'] ?? 0 );
	if ( $pid && ( is_404() || ( is_singular() && 'publish' !== get_post_status( $pid ) ) ) ) {
		$old = get_post( $pid );
		if ( $old && 'publish' !== $old->post_status ) {
			$target = vs_legacy_target( $old->post_name );
		}
	}
	if ( ! $target && is_404() ) {
		$path = trim( (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
		$target = vs_legacy_target( $path );
	}
	if ( $target ) {
		wp_safe_redirect( $target, 301 );
		exit;
	}
} );

// Menu items that point at drafted old pages get the new URL, so existing menus keep working.
add_filter( 'wp_nav_menu_objects', function ( $items ) {
	foreach ( $items as $item ) {
		if ( 'post_type' === $item->type && 'page' === $item->object && 'publish' !== get_post_status( (int) $item->object_id ) ) {
			$old = get_post( (int) $item->object_id );
			$new = $old ? vs_legacy_target( $old->post_name ) : '';
			if ( $new ) {
				$item->url = $new;
			}
		}
	}
	return $items;
} );
