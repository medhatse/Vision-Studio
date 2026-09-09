<?php
/**
 * Reusable output helpers.
 */
defined( 'ABSPATH' ) || exit;

const VS_MONO = 'font-mono-tag uppercase tracking-[0.15em]';
function vs_arrow(): string {
	return vs_icon( 'arrow-up-right', 'text-xs lg:text-[10px]' );
}

function vs_eyebrow( string $text ): string {
	return '<p class="font-mono-tag text-xs uppercase tracking-[0.25em] text-accent mb-4">' . esc_html( $text ) . '</p>';
}
function vs_h2( string $html, string $extra = '' ): string {
	return '<h2 class="font-display uppercase text-[clamp(2rem,5vw,3.5rem)] leading-[0.95] ' . esc_attr( $extra ) . '">' . wp_kses_post( $html ) . '</h2>';
}
function vs_btn( string $href, string $label, string $style = 'primary' ): string {
	$cls = match ( $style ) {
		'white' => 'inline-flex items-center gap-2 bg-white text-black ' . VS_MONO . ' text-xs px-6 py-4',
		'ghost' => 'nav-link inline-flex items-center gap-2 ' . VS_MONO . ' text-xs text-white/70',
		default => 'btn-primary inline-flex items-center gap-2 bg-accent text-black ' . VS_MONO . ' text-xs px-6 py-4',
	};
	return '<a href="' . esc_url( $href ) . '" class="' . $cls . '">' . esc_html( $label ) . ' ' . vs_arrow() . '</a>';
}
function vs_tel( string $phone ): string {
	return 'tel:' . preg_replace( '/\(0\)|[\s()]/', '', $phone );
}
function vs_img_url( $attachment_id, string $size = 'full' ): string {
	$u = $attachment_id ? wp_get_attachment_image_url( (int) $attachment_id, $size ) : '';
	return $u ?: '';
}
function vs_page_url( string $slug ): string {
	$p = get_page_by_path( $slug );
	return $p ? get_permalink( $p ) : home_url( "/$slug/" );
}
function vs_news_url(): string {
	$id = (int) get_option( 'page_for_posts' );
	return $id ? get_permalink( $id ) : home_url( '/' );
}

// ----- Studio data -----
function vs_studio_gallery_ids( $post_id ): array {
	$ids = array_filter( array_map( 'intval', explode( ',', (string) vs_meta( $post_id, 'gallery' ) ) ) );
	if ( ! $ids && has_post_thumbnail( $post_id ) ) {
		$ids = [ get_post_thumbnail_id( $post_id ) ];
	}
	return array_values( $ids );
}
function vs_studio_city( $post_id ): ?WP_Term {
	$terms = get_the_terms( $post_id, 'city' );
	return $terms && ! is_wp_error( $terms ) ? $terms[0] : null;
}
/** Attachment ID for a city's hero/card image (term image, else first photo of its first studio). */
function vs_city_image_id( ?WP_Term $term ): int {
	if ( ! $term ) {
		return 0;
	}
	$id = (int) vs_term_meta( $term->term_id, 'image' );
	if ( ! $id || ! wp_attachment_is_image( $id ) ) {
		$s  = get_posts( [ 'post_type' => 'studio', 'tax_query' => [ [ 'taxonomy' => 'city', 'terms' => $term->term_id ] ], 'posts_per_page' => 1, 'orderby' => 'menu_order', 'order' => 'ASC' ] );
		$id = $s ? (int) ( vs_studio_gallery_ids( $s[0]->ID )[0] ?? 0 ) : 0;
	}
	return $id;
}
function vs_city_image( ?WP_Term $term, string $size = 'vs-card' ): string {
	return vs_img_url( vs_city_image_id( $term ), $size );
}
/** Full-bleed responsive image (srcset + sizes=100vw) for heroes; $image may be an attachment ID or a URL. */
function vs_hero_img( $image, string $alt, bool $eager = true ): string {
	$attrs = [ 'class' => 'absolute inset-0 w-full h-full object-cover', 'alt' => $alt, 'sizes' => '100vw', 'decoding' => 'async' ];
	if ( $eager ) {
		$attrs['loading'] = 'eager';
		$attrs['fetchpriority'] = 'high';
	}
	if ( is_numeric( $image ) && (int) $image ) {
		return wp_get_attachment_image( (int) $image, 'vs-wide', false, $attrs );
	}
	return $image ? '<img src="' . esc_url( (string) $image ) . '" alt="' . esc_attr( $alt ) . '" class="' . $attrs['class'] . '"' . ( $eager ? ' fetchpriority="high"' : '' ) . '/>' : '';
}
function vs_city_studios( WP_Term $term ): array {
	return get_posts( [ 'post_type' => 'studio', 'posts_per_page' => -1, 'orderby' => [ 'menu_order' => 'ASC', 'title' => 'ASC' ], 'tax_query' => [ [ 'taxonomy' => 'city', 'terms' => $term->term_id ] ] ] );
}
function vs_cities(): array {
	$terms = get_terms( [ 'taxonomy' => 'city', 'hide_empty' => false ] );
	if ( is_wp_error( $terms ) ) {
		return [];
	}
	$order = [ 'london' => 0, 'dublin' => 1, 'paris' => 2, 'istanbul' => 3 ];
	usort( $terms, fn( $a, $b ) => ( $order[ $a->slug ] ?? 9 ) <=> ( $order[ $b->slug ] ?? 9 ) );
	return $terms;
}

// ----- Cards -----
function vs_studio_card( WP_Post $s, string $ratio = 'aspect-[3/4]' ): string {
	$city  = vs_studio_city( $s->ID );
	$tag   = vs_meta( $s->ID, 'tagline', $city ? $city->name : '' );
	$area  = vs_meta( $s->ID, 'area' );
	$sub   = $area ? sprintf( __( '%s sq. mt. studio', 'vision-studios' ), $area ) : ( vs_lines( vs_meta( $s->ID, 'highlights' ) )[0] ?? '' );
	$img   = wp_get_attachment_image( vs_studio_gallery_ids( $s->ID )[0] ?? 0, 'vs-card', false, [ 'alt' => sprintf( __( '%1$s, %2$s', 'vision-studios' ), $s->post_title, $city ? $city->name : '' ), 'loading' => 'lazy', 'class' => 'absolute inset-0 w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500' ] );
	return '<a href="' . esc_url( get_permalink( $s ) ) . '" class="group fade-up img-zoom relative ' . esc_attr( $ratio ) . ' block overflow-hidden bg-white/5">' . $img
		. '<div class="absolute inset-0 bg-gradient-to-t from-black via-black/20 to-transparent"></div>'
		. '<div class="absolute bottom-0 left-0 p-4"><p class="font-mono-tag text-xs lg:text-[10px] uppercase tracking-[0.15em] text-accent mb-1">' . esc_html( $tag ) . '</p><h3 class="font-display uppercase text-2xl leading-none">' . esc_html( $s->post_title ) . '</h3><p class="font-mono-tag text-xs lg:text-[10px] text-white/50 mt-1">' . esc_html( $sub ) . '</p></div></a>';
}

function vs_news_card( WP_Post $p ): string {
	$img = get_the_post_thumbnail( $p, 'vs-thumb', [ 'alt' => $p->post_title, 'loading' => 'lazy', 'class' => 'absolute inset-0 w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500' ] );
	return '<a href="' . esc_url( get_permalink( $p ) ) . '" class="group fade-up block">'
		. '<div class="img-zoom relative aspect-[16/10] overflow-hidden bg-white/5">' . $img . '</div>'
		. '<p class="font-mono-tag text-xs lg:text-[10px] uppercase tracking-[0.2em] text-white/55 mt-4">' . esc_html( get_the_date( '', $p ) ) . '</p>'
		. '<h3 class="font-display uppercase text-xl leading-tight mt-2 group-hover:text-accent transition-colors">' . esc_html( $p->post_title ) . '</h3>'
		. '<p class="text-white/60 text-sm mt-2 line-clamp-3">' . esc_html( wp_strip_all_tags( get_the_excerpt( $p ) ) ) . '</p></a>';
}

function vs_page_hero( string $title_html, string $sub, $image, string $eyebrow, string $alt = '' ): string {
	return '<section class="relative min-h-[55vh] flex flex-col justify-end overflow-hidden">'
		. vs_hero_img( $image, $alt ?: wp_strip_all_tags( $title_html ) )
		. '<div class="absolute inset-0 bg-gradient-to-t from-black via-black/75 to-black/40"></div><div class="grain-overlay"></div>'
		. '<div class="relative z-10 max-w-7xl w-full mx-auto px-6 lg:px-10 pt-40 pb-14">' . vs_eyebrow( $eyebrow ) . '<h1 class="font-display uppercase leading-[0.95] text-[clamp(2.4rem,7vw,5rem)]">' . wp_kses_post( $title_html ) . '</h1>' . ( $sub ? '<p class="mt-6 max-w-2xl text-white/70">' . esc_html( $sub ) . '</p>' : '' ) . '</div></section>' . vs_breadcrumbs();
}

function vs_cta_band( string $heading = '', string $text = '' ): string {
	$heading = $heading ?: vs_opt( 'cta_heading' );
	$text    = $text ?: vs_opt( 'cta_text' );
	return '<section id="contact" class="bg-black py-24 lg:py-32 border-t border-white/10"><div class="max-w-7xl mx-auto px-6 lg:px-10 grid lg:grid-cols-2 gap-10 items-center">'
		. '<h2 class="fade-up font-display uppercase text-[clamp(2.4rem,7vw,4.5rem)] leading-[0.95]">' . wp_kses_post( $heading ) . '</h2>'
		. '<div class="fade-up"><p class="text-white/70 max-w-md mb-6">' . esc_html( $text ) . '</p><div class="flex flex-wrap gap-4">' . vs_btn( vs_page_url( 'contact' ), __( 'Start a Booking', 'vision-studios' ), 'white' ) . vs_btn( 'mailto:' . vs_opt( 'email' ), vs_opt( 'email' ), 'ghost' ) . '</div></div></div></section>';
}

function vs_map( string $query, string $title ): string {
	$src = 'https://maps.google.com/maps?q=' . rawurlencode( $query ) . '&t=m&z=16&output=embed&iwloc=near';
	// Facade: the Google Maps iframe (~1 MB of JS) is only loaded when the visitor asks for it.
	return '<div class="vs-map" data-src="' . esc_url( $src ) . '" data-title="' . esc_attr( $title ) . '"><button type="button" class="inline-flex items-center gap-3 bg-white text-black font-mono-tag text-xs uppercase tracking-[0.15em] px-5 py-3">' . vs_icon( 'map' ) . esc_html__( 'Load map', 'vision-studios' ) . '</button><a class="absolute bottom-3 font-mono-tag text-xs lg:text-[10px] uppercase tracking-[0.2em] text-white/60 hover:text-accent" href="https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $query ) . '" target="_blank" rel="noopener">' . esc_html__( 'Open in Google Maps', 'vision-studios' ) . '</a></div>';
}

/** Booking / contact form: CF7 shortcode when configured, otherwise the built-in mailto form. */
function vs_form( string $which, string $studio = '' ): string {
	$shortcode = vs_opt( 'booking' === $which ? 'cf7_booking' : 'cf7_contact' );
	if ( $shortcode && shortcode_exists( 'contact-form-7' ) ) {
		return '<div class="fade-up wpcf7-wrap">' . do_shortcode( $shortcode ) . '</div>';
	}
	$f = fn( $name, $label, $type = 'text', $req = false ) => '<label class="block"><span>' . esc_html( $label ) . ( $req ? ' *' : '' ) . '</span><input name="' . $name . '" type="' . $type . '"' . ( $req ? ' required' : '' ) . '/></label>';
	$studios = get_posts( [ 'post_type' => 'studio', 'posts_per_page' => -1, 'orderby' => 'menu_order', 'order' => 'ASC' ] );
	$out = '<form class="fade-up vs-form booking-form grid sm:grid-cols-2 gap-4 self-start" data-studio="' . esc_attr( $studio ) . '" data-to="' . esc_attr( vs_opt( 'email' ) ) . '">';
	$out .= $f( 'name', __( 'Your name', 'vision-studios' ), 'text', true ) . $f( 'email', __( 'Your email', 'vision-studios' ), 'email', true );
	if ( 'booking' === $which ) {
		$out .= $f( 'company', __( 'Your company name', 'vision-studios' ) ) . $f( 'phone', __( 'Your phone no.', 'vision-studios' ), 'tel' ) . $f( 'checkin', __( 'Check-in', 'vision-studios' ), 'date' ) . $f( 'checkout', __( 'Check-out', 'vision-studios' ), 'date' );
		$out .= '<label class="sm:col-span-2 block"><span>' . esc_html__( 'Where did you hear about us', 'vision-studios' ) . '</span><select name="source"><option>Google</option><option>Social network</option><option>Referral</option><option>Other</option></select></label>';
	} else {
		$out .= '<label class="sm:col-span-2 block"><span>' . esc_html__( 'Studio / city', 'vision-studios' ) . '</span><select name="studio"><option value="">' . esc_html__( 'Not sure yet', 'vision-studios' ) . '</option>';
		foreach ( $studios as $s ) {
			$out .= '<option>' . esc_html( $s->post_title ) . '</option>';
		}
		$out .= '</select></label>';
	}
	$out .= '<label class="sm:col-span-2 block"><span>' . esc_html__( 'Your message', 'vision-studios' ) . '</span><textarea name="message" rows="5"></textarea></label>';
	$out .= '<div class="sm:col-span-2 flex flex-wrap items-center gap-4"><button type="submit" class="btn-primary inline-flex items-center gap-2">' . esc_html__( 'Send', 'vision-studios' ) . ' ' . vs_arrow() . '</button><p class="text-white/55 text-xs">' . esc_html__( 'Opens your email client with the message pre-filled.', 'vision-studios' ) . '</p></div></form>';
	return $out;
}

function vs_lightbox(): string {
	return '<div id="lightbox" class="fixed inset-0 z-[60] bg-black/95 hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-label="' . esc_attr__( 'Image viewer', 'vision-studios' ) . '"><button id="lightbox-close" class="absolute top-5 right-6 text-white/70 hover:text-white text-3xl" aria-label="' . esc_attr__( 'Close', 'vision-studios' ) . '">&times;</button><button id="lightbox-prev" class="absolute left-4 top-1/2 -translate-y-1/2 text-white/60 hover:text-accent text-3xl px-3" aria-label="' . esc_attr__( 'Previous', 'vision-studios' ) . '">&#8249;</button><img id="lightbox-img" src="" alt="" class="max-h-[88vh] max-w-full object-contain"/><button id="lightbox-next" class="absolute right-4 top-1/2 -translate-y-1/2 text-white/60 hover:text-accent text-3xl px-3" aria-label="' . esc_attr__( 'Next', 'vision-studios' ) . '">&#8250;</button><p id="lightbox-caption" class="absolute bottom-5 inset-x-0 text-center font-mono-tag text-xs lg:text-[11px] uppercase tracking-[0.2em] text-white/50"></p></div>';
}

/** Services / reasons by type. */
function vs_services( string $type = 'service' ): array {
	return get_posts( [ 'post_type' => 'vs_service', 'posts_per_page' => -1, 'orderby' => [ 'menu_order' => 'ASC', 'title' => 'ASC' ], 'meta_query' => [ [ 'key' => '_vs_type', 'value' => $type ] ] ] );
}
