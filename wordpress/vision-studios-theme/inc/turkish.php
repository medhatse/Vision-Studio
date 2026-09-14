<?php
/**
 * Turkish section: /tr/ versions of the Istanbul city page and its studios.
 *
 * No multilingual plugin. The same templates render a Turkish page when the URL starts with /tr/:
 *  - content comes from _vs_tr_* meta on the Istanbul term and studios (written from data/tr.json),
 *  - interface strings are swapped through the gettext filter,
 *  - links, canonical, hreflang, titles and a sitemap point search engines at the right version.
 * Turn the whole thing off by deleting data/tr.json.
 */
defined( 'ABSPATH' ) || exit;

const VS_TR_CITY = 'istanbul';

/** data/tr.json (city, studios keyed by slug, ui strings), or [] when the file is missing. */
function vs_tr_data(): array {
	static $d = null;
	if ( null === $d ) {
		$f = VS_DIR . '/data/tr.json';
		$d = file_exists( $f ) ? ( json_decode( (string) file_get_contents( $f ), true ) ?: [] ) : [];
	}
	return $d;
}

/** True on the front end when the request is for a /tr/ URL. Works before the query is parsed. */
function vs_is_tr(): bool {
	static $is = null;
	if ( null === $is ) {
		$path = (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH );
		$path = '/' . ltrim( substr( $path, strlen( rtrim( wp_parse_url( home_url( '/' ), PHP_URL_PATH ) ?: '', '/' ) ) ), '/' );
		$is   = ! is_admin() && ( '/tr' === $path || 0 === strpos( $path, '/tr/' ) );
	}
	return $is;
}

/** Does this studio have Turkish content? */
function vs_tr_has( int $post_id ): bool {
	return '' !== (string) get_post_meta( $post_id, '_vs_tr_title', true );
}

function vs_tr_studio_url( WP_Post $p ): string {
	return home_url( '/tr/studios/' . $p->post_name . '/' );
}
function vs_tr_city_url(): string {
	return home_url( '/tr/city/' . VS_TR_CITY . '/' );
}
function vs_tr_en_studio_url( WP_Post $p ): string {
	return home_url( '/studios/' . $p->post_name . '/' );
}
function vs_tr_en_city_url(): string {
	return home_url( '/city/' . VS_TR_CITY . '/' );
}

/** [ 'en' => url, 'tr' => url ] for the current view when a Turkish twin exists, else []. */
function vs_tr_alternates(): array {
	if ( is_singular( 'studio' ) && vs_tr_has( get_queried_object_id() ) ) {
		$p = get_queried_object();
		return [ 'en' => vs_tr_en_studio_url( $p ), 'tr' => vs_tr_studio_url( $p ) ];
	}
	if ( is_tax( 'city' ) && VS_TR_CITY === get_queried_object()->slug ) {
		return [ 'en' => vs_tr_en_city_url(), 'tr' => vs_tr_city_url() ];
	}
	return [];
}

// ----- URLs -----
add_action( 'init', function () {
	add_rewrite_tag( '%vs_lang%', '(tr)' );
	add_rewrite_tag( '%vs_sitemap_tr%', '(1)' );
	add_rewrite_rule( '^tr/?$', 'index.php?city=' . VS_TR_CITY . '&vs_lang=tr', 'top' );
	add_rewrite_rule( '^tr/city/' . VS_TR_CITY . '/?$', 'index.php?city=' . VS_TR_CITY . '&vs_lang=tr', 'top' );
	add_rewrite_rule( '^tr/studios/([^/]+)/?$', 'index.php?studio=$matches[1]&vs_lang=tr', 'top' );
	add_rewrite_rule( '^sitemap-tr\.xml$', 'index.php?vs_sitemap_tr=1', 'top' );
	if ( get_option( 'vs_tr_rewrites' ) !== VS_VERSION ) {
		flush_rewrite_rules();
		update_option( 'vs_tr_rewrites', VS_VERSION );
	}
} );

// A /tr/ URL for something without Turkish content goes back to the English page.
add_action( 'template_redirect', function () {
	if ( get_query_var( 'vs_sitemap_tr' ) ) {
		vs_tr_sitemap();
	}
	if ( ! vs_is_tr() || is_404() ) {
		return;
	}
	if ( is_singular( 'studio' ) && ! vs_tr_has( get_queried_object_id() ) ) {
		wp_safe_redirect( get_permalink(), 302 );
		exit;
	}
	if ( is_tax( 'city' ) && VS_TR_CITY !== get_queried_object()->slug ) {
		wp_safe_redirect( get_term_link( get_queried_object() ), 302 );
		exit;
	}
} );

// Links inside a Turkish page stay in Turkish where a Turkish page exists.
add_filter( 'post_type_link', function ( $link, $post ) {
	return vs_is_tr() && 'studio' === $post->post_type && vs_tr_has( $post->ID ) ? vs_tr_studio_url( $post ) : $link;
}, 10, 2 );
add_filter( 'term_link', function ( $link, $term, $taxonomy ) {
	return vs_is_tr() && 'city' === $taxonomy && VS_TR_CITY === $term->slug ? vs_tr_city_url() : $link;
}, 10, 3 );

// ----- Language -----
add_filter( 'locale', fn( $l ) => vs_is_tr() ? 'tr_TR' : $l );
add_filter( 'language_attributes', fn( $a ) => vs_is_tr() ? preg_replace( '/lang="[^"]*"/', 'lang="tr"', $a ) : $a );

add_filter( 'gettext', function ( $translated, $original, $domain ) {
	if ( 'vision-studios' !== $domain || ! vs_is_tr() ) {
		return $translated;
	}
	$ui = vs_tr_data()['ui'] ?? [];
	return isset( $ui[ $original ] ) && '' !== $ui[ $original ] ? $ui[ $original ] : $translated;
}, 10, 3 );
add_filter( 'ngettext', function ( $translated, $single, $plural, $number, $domain ) {
	if ( 'vision-studios' !== $domain || ! vs_is_tr() ) {
		return $translated;
	}
	$ui  = vs_tr_data()['ui'] ?? [];
	$key = 1 === (int) $number ? $single : $plural;
	return isset( $ui[ $key ] ) && '' !== $ui[ $key ] ? $ui[ $key ] : $translated;
}, 10, 5 );

// Customizer copy used on the page (contact band) follows the same table.
foreach ( [ 'cta_heading', 'cta_text' ] as $vs_tr_mod ) {
	add_filter( "theme_mod_vs_$vs_tr_mod", function ( $v ) {
		$ui = vs_tr_data()['ui'] ?? [];
		return vs_is_tr() && is_string( $v ) && ! empty( $ui[ $v ] ) ? $ui[ $v ] : $v;
	} );
}

// ----- Content: every theme field reads its _vs_tr_ twin when one is set -----
add_filter( 'get_post_metadata', function ( $value, $object_id, $meta_key, $single ) {
	if ( ! vs_is_tr() || 0 !== strpos( $meta_key, '_vs_' ) || 0 === strpos( $meta_key, '_vs_tr_' ) ) {
		return $value;
	}
	$tr = get_post_meta( $object_id, '_vs_tr_' . substr( $meta_key, 4 ), true );
	if ( '' === $tr || null === $tr ) {
		return $value;
	}
	return $single ? $tr : [ $tr ];
}, 10, 4 );
add_filter( 'get_term_metadata', function ( $value, $object_id, $meta_key, $single ) {
	if ( ! vs_is_tr() || 0 !== strpos( $meta_key, '_vs_' ) || 0 === strpos( $meta_key, '_vs_tr_' ) ) {
		return $value;
	}
	$tr = get_term_meta( $object_id, '_vs_tr_' . substr( $meta_key, 4 ), true );
	if ( '' === $tr || null === $tr ) {
		return $value;
	}
	return $single ? $tr : [ $tr ];
}, 10, 4 );

add_filter( 'the_title', function ( $title, $id = 0 ) {
	if ( vs_is_tr() && $id && 'studio' === get_post_type( $id ) ) {
		$t = get_post_meta( $id, '_vs_tr_title', true );
		return $t ?: $title;
	}
	return $title;
}, 10, 2 );
add_filter( 'the_content', function ( $content ) {
	if ( vs_is_tr() && 'studio' === get_post_type() ) {
		$t = get_post_meta( get_the_ID(), '_vs_tr_content', true );
		return $t ?: $content;
	}
	return $content;
}, 5 );
add_filter( 'vs_post_excerpt', function ( $excerpt, WP_Post $p ) {
	if ( ! vs_is_tr() || 'studio' !== $p->post_type ) {
		return $excerpt;
	}
	$e = get_post_meta( $p->ID, '_vs_tr_excerpt', true );
	if ( $e ) {
		return $e;
	}
	$c = get_post_meta( $p->ID, '_vs_tr_content', true );
	return $c ? wp_trim_words( wp_strip_all_tags( $c ), 40 ) : $excerpt;
}, 10, 2 );
add_filter( 'get_term', function ( $term, $taxonomy ) {
	if ( vs_is_tr() && 'city' === $taxonomy && $term instanceof WP_Term && VS_TR_CITY === $term->slug ) {
		$n = get_term_meta( $term->term_id, '_vs_tr_name', true );
		$d = get_term_meta( $term->term_id, '_vs_tr_description', true );
		if ( $n ) {
			$term->name = $n;
		}
		if ( $d ) {
			$term->description = $d;
		}
	}
	return $term;
}, 10, 2 );

// ----- Search engines -----
/** Turkish SEO title / description for the current view (meta on the studio or the city term). */
function vs_tr_seo( string $field ): string {
	if ( is_singular( 'studio' ) ) {
		return (string) get_post_meta( get_queried_object_id(), "_vs_tr_seo_$field", true );
	}
	if ( is_tax( 'city' ) ) {
		return (string) get_term_meta( get_queried_object_id(), "_vs_tr_seo_$field", true );
	}
	return '';
}
add_filter( 'rank_math/frontend/title', fn( $t ) => vs_is_tr() && vs_tr_seo( 'title' ) ? vs_tr_seo( 'title' ) : $t );
add_filter( 'rank_math/frontend/description', fn( $d ) => vs_is_tr() && vs_tr_seo( 'description' ) ? vs_tr_seo( 'description' ) : $d, 5 );
add_filter( 'rank_math/frontend/canonical', fn( $c ) => vs_is_tr() && vs_tr_alternates() ? vs_tr_alternates()['tr'] : $c );
add_filter( 'rank_math/opengraph/url', fn( $c ) => vs_is_tr() && vs_tr_alternates() ? vs_tr_alternates()['tr'] : $c );
add_filter( 'rank_math/opengraph/facebook/og_locale', fn( $l ) => vs_is_tr() ? 'tr_TR' : $l );
add_filter( 'document_title_parts', function ( $parts ) {
	if ( vs_is_tr() && vs_tr_seo( 'title' ) ) {
		return [ 'title' => vs_tr_seo( 'title' ) ];
	}
	return $parts;
}, 20 );
add_filter( 'vs_seo_description', fn( $d ) => vs_is_tr() && vs_tr_seo( 'description' ) ? vs_tr_seo( 'description' ) : $d );

// hreflang on both versions of every page that has a twin.
add_action( 'wp_head', function () {
	$alt = vs_tr_alternates();
	if ( ! $alt ) {
		return;
	}
	foreach ( $alt + [ 'x-default' => $alt['en'] ] as $lang => $url ) {
		echo '<link rel="alternate" hreflang="' . esc_attr( $lang ) . '" href="' . esc_url( $url ) . '"/>' . "\n";
	}
}, 2 );

/** /sitemap-tr.xml: the Turkish URLs, since the SEO plugin's sitemap only knows the English ones. */
function vs_tr_sitemap(): void {
	status_header( 200 );
	header( 'Content-Type: application/xml; charset=utf-8' );
	header( 'Cache-Control: public, max-age=3600' );
	$term = get_term_by( 'slug', VS_TR_CITY, 'city' );
	$urls = [];
	if ( $term ) {
		$urls[] = [ vs_tr_city_url(), vs_tr_en_city_url(), null ];
		foreach ( vs_city_studios( $term ) as $s ) {
			if ( vs_tr_has( $s->ID ) ) {
				$urls[] = [ vs_tr_studio_url( $s ), vs_tr_en_studio_url( $s ), get_post_modified_time( 'c', true, $s ) ];
			}
		}
	}
	echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
	foreach ( $urls as [ $tr, $en, $mod ] ) {
		echo '<url><loc>' . esc_url( $tr ) . '</loc>' . ( $mod ? '<lastmod>' . esc_html( $mod ) . '</lastmod>' : '' )
			. '<xhtml:link rel="alternate" hreflang="tr" href="' . esc_url( $tr ) . '"/><xhtml:link rel="alternate" hreflang="en" href="' . esc_url( $en ) . '"/><xhtml:link rel="alternate" hreflang="x-default" href="' . esc_url( $en ) . '"/></url>' . "\n";
	}
	echo '</urlset>' . "\n";
	exit;
}
add_filter( 'robots_txt', function ( $output, $public ) {
	return $public && vs_tr_data() ? rtrim( $output, "\n" ) . "\nSitemap: " . home_url( '/sitemap-tr.xml' ) . "\n" : $output;
}, 100, 2 );

// ----- Language switch (header) -----
function vs_lang_switch( string $class = '' ): string {
	$alt = vs_tr_alternates();
	if ( ! $alt ) {
		return '';
	}
	$to = vs_is_tr() ? [ $alt['en'], 'EN', 'English' ] : [ $alt['tr'], 'TR', 'Türkçe' ];
	return '<a href="' . esc_url( $to[0] ) . '" hreflang="' . ( vs_is_tr() ? 'en' : 'tr' ) . '" lang="' . ( vs_is_tr() ? 'en' : 'tr' ) . '" class="' . esc_attr( $class ) . '" aria-label="' . esc_attr( $to[2] ) . '">' . esc_html( $to[1] ) . '</a>';
}

// ----- Import: data/tr.json → _vs_tr_* meta -----
function vs_tr_import(): array {
	$data = vs_tr_data();
	$log  = [];
	if ( ! $data ) {
		return [ 'data/tr.json missing' ];
	}
	$c = $data['city'] ?? [];
	$t = term_exists( VS_TR_CITY, 'city' );
	if ( $t && $c ) {
		foreach ( [ 'name', 'heading', 'tagline', 'description', 'faq', 'seo_title', 'seo_description' ] as $k ) {
			update_term_meta( (int) $t['term_id'], "_vs_tr_$k", $c[ $k ] ?? '' );
		}
		clean_term_cache( (int) $t['term_id'], 'city' );
		$log[] = 'city ' . VS_TR_CITY;
	}
	foreach ( $data['studios'] ?? [] as $slug => $s ) {
		$p = get_page_by_path( $slug, OBJECT, 'studio' );
		if ( ! $p ) {
			$log[] = "missing studio $slug";
			continue;
		}
		foreach ( [ 'title', 'tagline', 'excerpt', 'content', 'highlights', 'specs', 'use_cases_heading', 'use_cases', 'faq', 'price_from', 'seo_title', 'seo_description' ] as $k ) {
			update_post_meta( $p->ID, "_vs_tr_$k", $s[ $k ] ?? '' );
		}
		clean_post_cache( $p->ID );
		$log[] = "studio $slug";
	}
	if ( function_exists( 'vs_purge_nginx_cache' ) ) {
		vs_purge_nginx_cache();
	}
	return $log;
}
// Runs by itself once per change to data/tr.json (so a theme update carries new translations), or on demand.
add_action( 'admin_init', function () {
	$f = VS_DIR . '/data/tr.json';
	if ( ! file_exists( $f ) ) {
		return;
	}
	$hash   = md5_file( $f );
	$manual = ! empty( $_GET['vs_tr_import'] ) && current_user_can( 'manage_options' );
	if ( ! $manual && get_option( 'vs_tr_import_hash' ) === $hash ) {
		return;
	}
	$log = vs_tr_import();
	update_option( 'vs_tr_import_hash', $hash );
	if ( $manual ) {
		header( 'Content-Type: text/plain; charset=utf-8' );
		echo implode( "\n", $log ), "\n";
		exit;
	}
} );
