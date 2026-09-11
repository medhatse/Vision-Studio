<?php
/**
 * Rank Math integration: fill in focus keywords, SEO titles and descriptions for content the theme
 * created (studios, cities, pages) and for posts that have none, and keep internal post types out of
 * Rank Math. Admin-only, idempotent: existing values are never overwritten.
 *
 * Run: /wp-admin/?vs_seo_fill=1
 */
defined( 'ABSPATH' ) || exit;

function vs_rm_set_post( int $id, string $keyword, string $title, string $description, array &$report, bool $force_keyword = false ): void {
	if ( $force_keyword || '' === trim( (string) get_post_meta( $id, 'rank_math_focus_keyword', true ) ) ) {
		update_post_meta( $id, 'rank_math_focus_keyword', $keyword );
		$report['keywords']++;
	}
	if ( $title && '' === trim( (string) get_post_meta( $id, 'rank_math_title', true ) ) ) {
		update_post_meta( $id, 'rank_math_title', $title );
		$report['titles']++;
	}
	if ( $description && '' === trim( (string) get_post_meta( $id, 'rank_math_description', true ) ) ) {
		update_post_meta( $id, 'rank_math_description', $description );
		$report['descriptions']++;
	}
}

function vs_rm_set_term( int $id, string $keyword, string $title, string $description, array &$report ): void {
	if ( '' === trim( (string) get_term_meta( $id, 'rank_math_focus_keyword', true ) ) ) {
		update_term_meta( $id, 'rank_math_focus_keyword', $keyword );
		$report['keywords']++;
	}
	if ( $title && '' === trim( (string) get_term_meta( $id, 'rank_math_title', true ) ) ) {
		update_term_meta( $id, 'rank_math_title', $title );
		$report['titles']++;
	}
	if ( $description && '' === trim( (string) get_term_meta( $id, 'rank_math_description', true ) ) ) {
		update_term_meta( $id, 'rank_math_description', $description );
		$report['descriptions']++;
	}
}

/** Share image for Rank Math (post meta rank_math_facebook_image / _id), only when none is set. */
function vs_rm_set_post_image( int $id, int $attachment, array &$report ): void {
	if ( $attachment && '' === trim( (string) get_post_meta( $id, 'rank_math_facebook_image_id', true ) ) ) {
		update_post_meta( $id, 'rank_math_facebook_image', (string) wp_get_attachment_image_url( $attachment, 'full' ) );
		update_post_meta( $id, 'rank_math_facebook_image_id', $attachment );
		$report['images']++;
	}
}
function vs_rm_set_term_image( int $id, int $attachment, array &$report ): void {
	if ( $attachment && '' === trim( (string) get_term_meta( $id, 'rank_math_facebook_image_id', true ) ) ) {
		update_term_meta( $id, 'rank_math_facebook_image', (string) wp_get_attachment_image_url( $attachment, 'full' ) );
		update_term_meta( $id, 'rank_math_facebook_image_id', $attachment );
		$report['images']++;
	}
}

/** Trim to a sensible meta-description length on a word boundary. */
function vs_rm_desc( string $text ): string {
	$text = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $text ) ) );
	if ( mb_strlen( $text ) <= 158 ) {
		return $text;
	}
	return preg_replace( '/\s+\S*$/u', '', mb_substr( $text, 0, 155 ) ) . '…';
}

add_action( 'admin_init', function () {
	if ( empty( $_GET['vs_seo_fill'] ) || ! current_user_can( 'manage_options' ) || ! defined( 'RANK_MATH_VERSION' ) ) {
		return;
	}
	$report = [ 'keywords' => 0, 'titles' => 0, 'descriptions' => 0, 'images' => 0, 'renamed' => [], 'fixed' => [] ];
	$site   = get_bloginfo( 'name' );
	// Rank Math's "keyword in title" test looks at the post title, not the SEO title, so every keyword below
	// is a phrase contained in the post title. force=1 re-applies keywords the fill wrote earlier.
	$force  = ! empty( $_GET['force'] );

	// Studios: "<City> studio" is in every title ("Istanbul Studio 1"); the DTL room falls back to its title.
	foreach ( get_posts( [ 'post_type' => 'studio', 'posts_per_page' => -1, 'post_status' => 'publish' ] ) as $s ) {
		$city  = vs_studio_city( $s->ID );
		$cname = $city ? $city->name : 'London';
		$specs = vs_lines( vs_meta( $s->ID, 'specs' ) ?: vs_meta( $s->ID, 'highlights' ) );
		$desc  = $s->post_excerpt ?: sprintf( '%s for hire in %s: %s', $s->post_title, $cname, implode( ' ', array_slice( $specs, 0, 4 ) ) );
		$kw    = false !== stripos( $s->post_title, $cname . ' studio' ) ? strtolower( $cname . ' studio' ) : strtolower( $s->post_title );
		vs_rm_set_post( $s->ID, $kw, sprintf( '%s: %s TV Studio Hire | %s', $s->post_title, $cname, $site ), vs_rm_desc( $desc ), $report, $force );
		vs_rm_set_post_image( $s->ID, (int) ( vs_studio_gallery_ids( $s->ID )[0] ?? 0 ), $report );
	}

	// Cities: "TV studios in <City>".
	foreach ( vs_cities() as $t ) {
		$names = implode( ', ', wp_list_pluck( vs_city_studios( $t ), 'post_title' ) );
		$desc  = $t->description ? wp_trim_words( wp_strip_all_tags( $t->description ), 26, '' ) : sprintf( 'Fully equipped broadcast and production studios for hire in %s: %s.', $t->name, $names );
		vs_rm_set_term( (int) $t->term_id, strtolower( 'TV studios in ' . $t->name ), sprintf( 'TV Studios in %s for Hire | %s', $t->name, $site ), vs_rm_desc( $desc ), $report );
		vs_rm_set_term_image( (int) $t->term_id, vs_city_image_id( $t ), $report );
	}
	$hero_id = (int) vs_opt( 'hero_image' );
	if ( ! $hero_id || ! wp_attachment_is_image( $hero_id ) ) {
		$cities  = vs_cities();
		$hero_id = $cities ? vs_city_image_id( $cities[1] ?? $cities[0] ) : 0;
	}

	// Theme pages: [ keyword, post title containing it, SEO title, description ]. Page titles only show in
	// the admin, breadcrumbs and the browser title, so they can carry the keyword.
	$pages = [
		'home'           => [ 'broadcast studio hire', 'Broadcast Studio Hire', 'Broadcast Studio Hire in London, Dublin, Paris & Istanbul | ' . $site, vs_opt( 'choose_intro' ) ?: vs_opt( 'hero_intro' ) ],
		'about'          => [ 'about Vision Studios', 'About Vision Studios', 'About Vision Studios: 25 Years of Broadcast Studio Hire', '' ],
		'contact'        => [ 'contact Vision Studios', 'Contact Vision Studios', 'Contact Vision Studios: Book a TV Studio in London, Dublin, Paris or Istanbul', '' ],
		'gallery'        => [ 'TV studio gallery', 'TV Studio Gallery', 'TV Studio Gallery: Our Broadcast Studios in Pictures | ' . $site, 'Photographs from every Vision Studios broadcast and production studio in London, Dublin, Paris and Istanbul.' ],
		'news'           => [ 'Vision Studios news', 'Vision Studios News', 'Vision Studios News: Productions, Live Events and Behind the Scenes', 'Productions, live events and behind-the-scenes stories from Vision Studios in London, Dublin, Paris and Istanbul.' ],
		'privacy-policy' => [ 'privacy policy', 'Privacy Policy', 'Privacy Policy | ' . $site, 'How Vision Studios collects, uses and protects personal data submitted through its website and booking forms.' ],
		'terms'          => [ 'terms of studio hire', 'Terms of Studio Hire', 'Terms of Studio Hire | ' . $site, 'The terms that apply to every studio booking with Vision Studios in London, Dublin, Paris and Istanbul.' ],
	];
	foreach ( $pages as $slug => $row ) {
		$p = get_page_by_path( $slug );
		if ( ! $p ) {
			continue;
		}
		if ( false === stripos( $p->post_title, $row[0] ) && $p->post_title !== $row[1] ) {
			wp_update_post( [ 'ID' => $p->ID, 'post_title' => $row[1] ] );
			$report['renamed'][] = $p->post_title . ' → ' . $row[1];
		}
		vs_rm_set_post( $p->ID, $row[0], $row[2], vs_rm_desc( $row[3] ?: ( $p->post_excerpt ?: wp_trim_words( wp_strip_all_tags( $p->post_content ), 26, '' ) ) ), $report, $force );
		vs_rm_set_post_image( $p->ID, (int) get_post_thumbnail_id( $p ) ?: $hero_id, $report );
	}

	// Posts: the title's main phrase is the keyword, so the "keyword in title" check passes. A keyword set
	// by hand that is not in the title is replaced too (and reported), otherwise Rank Math keeps flagging it.
	foreach ( get_posts( [ 'post_type' => 'post', 'posts_per_page' => -1, 'post_status' => 'publish' ] ) as $p ) {
		$kw      = strtolower( trim( preg_split( '/\s*[|:—–]\s*/u', $p->post_title )[0] ) );
		$current = trim( (string) get_post_meta( $p->ID, 'rank_math_focus_keyword', true ) );
		$primary = trim( explode( ',', $current )[0] );
		$mismatch = '' !== $primary && false === stripos( $p->post_title, $primary );
		if ( $mismatch ) {
			$report['fixed'][] = $p->post_title . ': "' . $primary . '" → "' . $kw . '"';
		}
		vs_rm_set_post( $p->ID, $kw, '', vs_rm_desc( $p->post_excerpt ?: wp_trim_words( wp_strip_all_tags( strip_shortcodes( $p->post_content ) ), 26, '' ) ), $report, $mismatch );
		vs_rm_set_post_image( $p->ID, (int) get_post_thumbnail_id( $p ), $report );
	}

	// Internal post types (LinkedIn auto-publish copies, theme services/clients/testimonials): no SEO controls,
	// so Rank Math stops counting them as pages needing keywords.
	$opts    = get_option( 'rank-math-options-titles', [] );
	$changed = [];
	foreach ( get_post_types( [ 'show_ui' => true ], 'objects' ) as $pt ) {
		if ( in_array( $pt->name, [ 'vs_service', 'vs_client', 'vs_testimonial' ], true ) || preg_match( '/linke?d?in/i', $pt->name . ' ' . $pt->label ) ) {
			if ( ( $opts[ "pt_{$pt->name}_add_meta_box" ] ?? 'on' ) !== 'off' ) {
				$opts[ "pt_{$pt->name}_add_meta_box" ] = 'off';
				$changed[] = $pt->name;
			}
		}
	}
	// Site-wide fallback share image, and no "Article by Person" schema on ordinary pages (the theme and
	// Rank Math's Local SEO already describe them as WebPage / Place).
	if ( $hero_id && empty( $opts['open_graph_image_id'] ) ) {
		$opts['open_graph_image']    = (string) wp_get_attachment_image_url( $hero_id, 'full' );
		$opts['open_graph_image_id'] = $hero_id;
		$changed[] = 'default share image';
	}
	if ( ( $opts['pt_page_default_rich_snippet'] ?? '' ) !== 'off' ) {
		$opts['pt_page_default_rich_snippet'] = 'off';
		$changed[] = 'page schema: none';
	}
	if ( $changed ) {
		update_option( 'rank-math-options-titles', $opts );
	}

	header( 'Content-Type: text/plain' );
	printf( "focus keywords set: %d\nSEO titles set: %d\ndescriptions set: %d\nshare images set: %d\noptions changed: %s\npages renamed: %s\npost keywords replaced: %s\n", $report['keywords'], $report['titles'], $report['descriptions'], $report['images'], $changed ? implode( ', ', $changed ) : 'nothing new', $report['renamed'] ? implode( '; ', $report['renamed'] ) : 'none', $report['fixed'] ? implode( '; ', $report['fixed'] ) : 'none' );
	exit;
} );
