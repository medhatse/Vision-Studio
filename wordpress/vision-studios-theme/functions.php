<?php
/**
 * Vision Studios theme bootstrap.
 */
defined( 'ABSPATH' ) || exit;

define( 'VS_VERSION', '1.0.0' );
define( 'VS_DIR', get_template_directory() );
define( 'VS_URI', get_template_directory_uri() );

require VS_DIR . '/inc/post-types.php';
require VS_DIR . '/inc/meta-boxes.php';
require VS_DIR . '/inc/customizer.php';
require VS_DIR . '/inc/template-tags.php';
require VS_DIR . '/inc/redirects.php';
require VS_DIR . '/inc/importer.php';

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [ 'search-form', 'gallery', 'caption', 'script', 'style' ] );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'custom-logo' );
	register_nav_menus( [ 'primary' => __( 'Primary navigation', 'vision-studios' ) ] );
	add_image_size( 'vs-card', 900, 1200, true );
	add_image_size( 'vs-wide', 1600, 900, false );
	add_image_size( 'vs-thumb', 800, 600, true );
	load_theme_textdomain( 'vision-studios', VS_DIR . '/languages' );
} );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'vs-fonts', 'https://fonts.googleapis.com/css2?family=Anton&family=Inter:wght@400;500;600;700&family=Space+Mono:wght@400;700&display=swap', [], null );
	wp_enqueue_style( 'vs-fontawesome', 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css', [], '6.4.0' );
	wp_enqueue_style( 'vs-app', VS_URI . '/assets/app.css', [], VS_VERSION );
	wp_enqueue_script( 'vs-app', VS_URI . '/assets/app.js', [], VS_VERSION, true );
} );

// Resource hints for the font CDN.
add_filter( 'wp_resource_hints', function ( $urls, $relation ) {
	if ( 'preconnect' === $relation ) {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = [ 'href' => 'https://fonts.gstatic.com', 'crossorigin' ];
	}
	return $urls;
}, 10, 2 );

// Make the Studio archive / city archive use sensible ordering and show everything.
add_action( 'pre_get_posts', function ( WP_Query $q ) {
	if ( is_admin() || ! $q->is_main_query() ) {
		return;
	}
	if ( $q->is_post_type_archive( 'studio' ) || $q->is_tax( 'city' ) ) {
		$q->set( 'posts_per_page', -1 );
		$q->set( 'orderby', [ 'menu_order' => 'ASC', 'title' => 'ASC' ] );
	}
} );

// Body class for the dark canvas.
add_filter( 'body_class', fn( $c ) => array_merge( $c, [ 'bg-black', 'text-white', 'antialiased' ] ) );

// Excerpt tweaks for news cards.
add_filter( 'excerpt_length', fn() => 28 );
add_filter( 'excerpt_more', fn() => '…' );

// Front page falls back to front-page.php even when "Your latest posts" is selected.
add_filter( 'frontpage_template', fn( $t ) => $t ?: locate_template( 'front-page.php' ) );
