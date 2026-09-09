<?php
/**
 * Studio post type, City taxonomy, Service and Client post types.
 */
defined( 'ABSPATH' ) || exit;

function vs_register_post_types(): void {
	register_taxonomy( 'city', [ 'studio' ], [
		'labels'            => [ 'name' => __( 'Cities', 'vision-studios' ), 'singular_name' => __( 'City', 'vision-studios' ), 'menu_name' => __( 'Cities', 'vision-studios' ) ],
		'public'            => true,
		'hierarchical'      => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => [ 'slug' => 'city', 'with_front' => false ],
	] );

	register_post_type( 'studio', [
		'labels'       => [
			'name' => __( 'Studios', 'vision-studios' ), 'singular_name' => __( 'Studio', 'vision-studios' ),
			'add_new_item' => __( 'Add New Studio', 'vision-studios' ), 'edit_item' => __( 'Edit Studio', 'vision-studios' ),
			'all_items' => __( 'All Studios', 'vision-studios' ), 'menu_name' => __( 'Studios', 'vision-studios' ),
		],
		'public'       => true,
		'has_archive'  => 'studios',
		'rewrite'      => [ 'slug' => 'studios', 'with_front' => false ],
		'menu_icon'    => 'dashicons-video-alt2',
		'menu_position'=> 5,
		'supports'     => [ 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'revisions' ],
		'show_in_rest' => true,
		'taxonomies'   => [ 'city' ],
	] );

	register_post_type( 'vs_service', [
		'labels'       => [ 'name' => __( 'Services', 'vision-studios' ), 'singular_name' => __( 'Service', 'vision-studios' ), 'add_new_item' => __( 'Add New Service', 'vision-studios' ), 'menu_name' => __( 'Services', 'vision-studios' ) ],
		'public'       => false,
		'show_ui'      => true,
		'menu_icon'    => 'dashicons-screenoptions',
		'menu_position'=> 6,
		'supports'     => [ 'title', 'editor', 'excerpt', 'page-attributes' ],
		'show_in_rest' => true,
	] );

	register_post_type( 'vs_client', [
		'labels'       => [ 'name' => __( 'Clients', 'vision-studios' ), 'singular_name' => __( 'Client', 'vision-studios' ), 'add_new_item' => __( 'Add New Client', 'vision-studios' ), 'menu_name' => __( 'Clients', 'vision-studios' ) ],
		'public'       => false,
		'show_ui'      => true,
		'menu_icon'    => 'dashicons-groups',
		'menu_position'=> 7,
		'supports'     => [ 'title', 'thumbnail', 'page-attributes' ],
		'show_in_rest' => true,
	] );
}
add_action( 'init', 'vs_register_post_types' );

// Flush rewrite rules on theme switch so /studios/… and /city/… work immediately.
// (after_switch_theme fires from inside init, so register directly — never re-fire init here.)
add_action( 'after_switch_theme', function () {
	vs_register_post_types();
	flush_rewrite_rules();
} );
