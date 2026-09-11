<?php
/**
 * Meta boxes for Studios and Services, term meta for Cities. No plugin dependency.
 */
defined( 'ABSPATH' ) || exit;

/** Field definitions: key => [label, type, help]. Stored as post meta "_vs_{key}". */
function vs_studio_fields(): array {
	return [
		'tagline'           => [ __( 'Tag line', 'vision-studios' ), 'text', __( 'Short label shown above the title on cards, e.g. "Flagship · 72-screen video wall".', 'vision-studios' ) ],
		'area'              => [ __( 'Studio area (sq. mt.)', 'vision-studios' ), 'number', '' ],
		'price_from'        => [ __( 'Price from', 'vision-studios' ), 'text', __( 'Shown as "From …" on the page, e.g. "£1,200 / day". Leave empty to hide.', 'vision-studios' ) ],
		'faq'               => [ __( 'FAQ', 'vision-studios' ), 'textarea', __( 'One per line as "Question | Answer". Shown at the bottom of the page with FAQ structured data.', 'vision-studios' ) ],
		'specs'             => [ __( 'Full specification', 'vision-studios' ), 'textarea', __( 'One item per line.', 'vision-studios' ) ],
		'highlights'        => [ __( 'Highlights', 'vision-studios' ), 'textarea', __( 'Up to six short lines shown as icon tiles under the hero.', 'vision-studios' ) ],
		'use_cases_heading' => [ __( '"What to shoot here" heading', 'vision-studios' ), 'text', '' ],
		'use_cases'         => [ __( 'What to shoot here', 'vision-studios' ), 'textarea', __( 'One per line as "Title | Description".', 'vision-studios' ) ],
		'gallery'           => [ __( 'Photo gallery', 'vision-studios' ), 'gallery', __( 'First image is used as the card image and first hero slide.', 'vision-studios' ) ],
		'floor_plan'        => [ __( 'Floor plan', 'vision-studios' ), 'image', '' ],
		'phone'             => [ __( 'Phone', 'vision-studios' ), 'text', __( 'Leave empty to use the city phone.', 'vision-studios' ) ],
		'address'           => [ __( 'Address', 'vision-studios' ), 'text', __( 'Leave empty to use the city address.', 'vision-studios' ) ],
		'map_query'         => [ __( 'Google Maps query', 'vision-studios' ), 'text', __( 'Address or place name for the embedded map.', 'vision-studios' ) ],
	];
}

function vs_service_fields(): array {
	return [
		'icon' => [ __( 'Font Awesome icon class', 'vision-studios' ), 'text', __( 'e.g. fa-tower-broadcast', 'vision-studios' ) ],
		'type' => [ __( 'Shown as', 'vision-studios' ), 'select', [ 'service' => __( 'Service card (home & city pages)', 'vision-studios' ), 'reason' => __( '"Why Vision" reason (home page)', 'vision-studios' ) ] ],
	];
}

function vs_city_fields(): array {
	return [
		'country'   => [ __( 'Country', 'vision-studios' ), 'text' ],
		'tagline'   => [ __( 'Tag line', 'vision-studios' ), 'text' ],
		'heading'   => [ __( 'Page heading', 'vision-studios' ), 'text' ],
		'phone'     => [ __( 'Phone', 'vision-studios' ), 'text' ],
		'address'   => [ __( 'Address', 'vision-studios' ), 'text' ],
		'map_query' => [ __( 'Google Maps query', 'vision-studios' ), 'text' ],
		'coords'    => [ __( 'Coordinates label', 'vision-studios' ), 'text' ],
		'image'     => [ __( 'Hero / card image', 'vision-studios' ), 'image' ],
		'faq'       => [ __( 'FAQ (one per line: Question | Answer)', 'vision-studios' ), 'textarea' ],
	];
}

/** Read a studio/service field. */
function vs_meta( $post_id, string $key, $default = '' ) {
	$v = get_post_meta( $post_id, "_vs_$key", true );
	return ( '' === $v || null === $v ) ? $default : $v;
}

/** Read a city term field. */
function vs_term_meta( $term_id, string $key, $default = '' ) {
	$v = get_term_meta( $term_id, "_vs_$key", true );
	return ( '' === $v || null === $v ) ? $default : $v;
}

function vs_lines( $text ): array {
	return array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $text ) ) ) );
}

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'vs_studio', __( 'Studio details', 'vision-studios' ), 'vs_render_meta_box', 'studio', 'normal', 'high', [ 'fields' => vs_studio_fields() ] );
	add_meta_box( 'vs_service', __( 'Service details', 'vision-studios' ), 'vs_render_meta_box', 'vs_service', 'side', 'default', [ 'fields' => vs_service_fields() ] );
} );

function vs_render_field( string $name, array $def, $value ): void {
	[ $label, $type ] = $def;
	$help = $def[2] ?? '';
	$id   = esc_attr( $name );
	echo '<p style="margin:14px 0 4px"><label for="' . $id . '"><strong>' . esc_html( $label ) . '</strong></label></p>';
	switch ( $type ) {
		case 'textarea':
			echo '<textarea class="large-text" rows="6" id="' . $id . '" name="' . $id . '">' . esc_textarea( $value ) . '</textarea>';
			break;
		case 'select':
			echo '<select id="' . $id . '" name="' . $id . '">';
			foreach ( $help as $k => $l ) {
				echo '<option value="' . esc_attr( $k ) . '"' . selected( $value, $k, false ) . '>' . esc_html( $l ) . '</option>';
			}
			echo '</select>';
			$help = '';
			break;
		case 'image':
		case 'gallery':
			$ids  = array_filter( array_map( 'intval', explode( ',', (string) $value ) ) );
			echo '<div class="vs-media" data-multiple="' . ( 'gallery' === $type ? '1' : '0' ) . '"><input type="hidden" id="' . $id . '" name="' . $id . '" value="' . esc_attr( implode( ',', $ids ) ) . '"/><div class="vs-media-preview" style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:8px">';
			foreach ( $ids as $aid ) {
				echo '<span data-id="' . $aid . '" style="position:relative">' . wp_get_attachment_image( $aid, [ 90, 90 ], false, [ 'style' => 'display:block;width:90px;height:90px;object-fit:cover' ] ) . '<button type="button" class="vs-media-remove" style="position:absolute;top:0;right:0;background:#000;color:#fff;border:0;cursor:pointer">×</button></span>';
			}
			echo '</div><button type="button" class="button vs-media-pick">' . ( 'gallery' === $type ? esc_html__( 'Select images', 'vision-studios' ) : esc_html__( 'Select image', 'vision-studios' ) ) . '</button></div>';
			break;
		default:
			echo '<input class="large-text" type="' . esc_attr( $type ) . '" id="' . $id . '" name="' . $id . '" value="' . esc_attr( $value ) . '"/>';
	}
	if ( $help && is_string( $help ) ) {
		echo '<p class="description">' . esc_html( $help ) . '</p>';
	}
}

function vs_render_meta_box( WP_Post $post, array $box ): void {
	wp_nonce_field( 'vs_meta_' . $post->ID, 'vs_meta_nonce' );
	foreach ( $box['args']['fields'] as $key => $def ) {
		vs_render_field( "vs_$key", $def, vs_meta( $post->ID, $key ) );
	}
}

add_action( 'save_post', function ( int $post_id, WP_Post $post ) {
	if ( ! isset( $_POST['vs_meta_nonce'] ) || ! wp_verify_nonce( $_POST['vs_meta_nonce'], 'vs_meta_' . $post_id ) || ! current_user_can( 'edit_post', $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}
	$fields = 'studio' === $post->post_type ? vs_studio_fields() : ( 'vs_service' === $post->post_type ? vs_service_fields() : [] );
	foreach ( $fields as $key => $def ) {
		$name = "vs_$key";
		if ( ! isset( $_POST[ $name ] ) ) {
			continue;
		}
		$raw = wp_unslash( $_POST[ $name ] );
		$val = 'textarea' === $def[1] ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
		update_post_meta( $post_id, "_vs_$key", $val );
	}
}, 10, 2 );

// ----- City term meta -----
add_action( 'city_add_form_fields', function () {
	foreach ( vs_city_fields() as $key => $def ) {
		echo '<div class="form-field">';
		vs_render_field( "vs_$key", $def, '' );
		echo '</div>';
	}
} );
add_action( 'city_edit_form_fields', function ( WP_Term $term ) {
	foreach ( vs_city_fields() as $key => $def ) {
		echo '<tr class="form-field"><th scope="row">' . esc_html( $def[0] ) . '</th><td>';
		vs_render_field( "vs_$key", [ '', $def[1] ], vs_term_meta( $term->term_id, $key ) );
		echo '</td></tr>';
	}
} );
$vs_save_term = function ( int $term_id ) {
	foreach ( vs_city_fields() as $key => $def ) {
		if ( isset( $_POST[ "vs_$key" ] ) ) {
			$raw = wp_unslash( $_POST[ "vs_$key" ] );
			update_term_meta( $term_id, "_vs_$key", 'textarea' === $def[1] ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw ) );
		}
	}
};
add_action( 'created_city', $vs_save_term );
add_action( 'edited_city', $vs_save_term );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( in_array( $hook, [ 'post.php', 'post-new.php', 'edit-tags.php', 'term.php' ], true ) ) {
		wp_enqueue_media();
		wp_enqueue_script( 'vs-admin', VS_URI . '/assets/admin.js', [ 'jquery' ], VS_VERSION, true );
	}
} );
