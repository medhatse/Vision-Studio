<?php
/**
 * SEO: titles, meta description, canonical, Open Graph / Twitter, JSON-LD structured data, breadcrumbs,
 * head clean-up. Meta tags and generic schema step aside when an SEO plugin (Rank Math, Yoast, AIOSEO,
 * SEOPress) is active; studio-specific schema and breadcrumbs are always output because plugins do not
 * know about studios.
 */
defined( 'ABSPATH' ) || exit;

function vs_seo_plugin_active(): bool {
	return defined( 'RANK_MATH_VERSION' ) || defined( 'WPSEO_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' );
}

/** Best description for the current view. */
function vs_seo_description(): string {
	$d = '';
	if ( is_front_page() ) {
		$d = vs_opt( 'choose_intro' ) ?: vs_opt( 'hero_intro' );
	} elseif ( is_singular( 'studio' ) ) {
		$p = get_queried_object();
		$city = vs_studio_city( $p->ID );
		$d = sprintf( __( '%1$s for hire in %2$s: %3$s', 'vision-studios' ), $p->post_title, $city ? $city->name : '', implode( ' ', array_slice( vs_lines( vs_meta( $p->ID, 'specs' ) ?: vs_meta( $p->ID, 'highlights' ) ), 0, 4 ) ) );
	} elseif ( is_tax( 'city' ) ) {
		$t = get_queried_object();
		$names = implode( ', ', wp_list_pluck( vs_city_studios( $t ), 'post_title' ) );
		$d = $t->description ?: sprintf( __( 'Fully equipped broadcast and production studios for hire in %1$s, %2$s: %3$s.', 'vision-studios' ), $t->name, vs_term_meta( $t->term_id, 'country' ), $names );
	} elseif ( is_singular() ) {
		$p = get_queried_object();
		$d = $p->post_excerpt ?: wp_trim_words( wp_strip_all_tags( strip_shortcodes( $p->post_content ) ), 30, '' );
	} elseif ( is_home() ) {
		$d = __( 'Productions, live events and behind-the-scenes stories from Vision Studios in London, Dublin, Paris and Istanbul.', 'vision-studios' );
	} elseif ( is_post_type_archive( 'studio' ) ) {
		$d = __( 'Every Vision Studios broadcast and production studio in London, Dublin, Paris and Istanbul.', 'vision-studios' );
	} else {
		$d = get_bloginfo( 'description' );
	}
	if ( ! trim( (string) $d ) && is_page_template( 'template-gallery.php' ) ) {
		$d = sprintf( __( 'Photographs from every Vision Studios broadcast and production studio in London, Dublin, Paris and Istanbul — %d studios, filterable by city.', 'vision-studios' ), (int) wp_count_posts( 'studio' )->publish );
	}
	$d = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $d ?: get_bloginfo( 'description' ) ) ) );
	return mb_strlen( $d ) > 158 ? mb_substr( $d, 0, 155 ) . '…' : $d;
}

/** Attachment ID of the share / hero image for the current view. */
function vs_seo_image_id(): int {
	if ( is_singular( 'studio' ) ) {
		return (int) ( vs_studio_gallery_ids( get_queried_object_id() )[0] ?? 0 );
	}
	if ( is_tax( 'city' ) ) {
		return vs_city_image_id( get_queried_object() );
	}
	if ( is_singular() && has_post_thumbnail() ) {
		return (int) get_post_thumbnail_id();
	}
	if ( is_home() ) {
		$first = get_posts( [ 'posts_per_page' => 1 ] );
		return $first ? (int) get_post_thumbnail_id( $first[0] ) : 0;
	}
	$hero = (int) vs_opt( 'hero_image' );
	if ( $hero && wp_attachment_is_image( $hero ) ) {
		return $hero;
	}
	$cities = vs_cities();
	return $cities ? vs_city_image_id( $cities[1] ?? $cities[0] ) : 0;
}

/** Share image URL for the current view. */
function vs_seo_image(): string {
	return vs_img_url( vs_seo_image_id(), 'vs-wide' );
}

function vs_seo_canonical(): string {
	if ( is_front_page() ) {
		return home_url( '/' );
	}
	if ( is_singular() ) {
		return (string) get_permalink();
	}
	if ( is_tax() || is_category() || is_tag() ) {
		$l = get_term_link( get_queried_object() );
		return is_wp_error( $l ) ? '' : $l;
	}
	if ( is_post_type_archive() ) {
		return (string) get_post_type_archive_link( get_query_var( 'post_type' ) );
	}
	if ( is_home() ) {
		return vs_news_url();
	}
	return '';
}

// With Rank Math active, fill in a description / share image wherever Rank Math has none of its own
// (imported studios, cities and pages have no per-item SEO meta yet).
add_filter( 'rank_math/frontend/description', fn( $d ) => trim( (string) $d ) ?: vs_seo_description() );
add_filter( 'rank_math/opengraph/facebook/image', fn( $i ) => $i ?: vs_seo_image() );
add_filter( 'rank_math/opengraph/twitter/image', fn( $i ) => $i ?: vs_seo_image() );
add_filter( 'wpseo_metadesc', fn( $d ) => trim( (string) $d ) ?: vs_seo_description() );

// Rank Math loads its options before the theme, so write the sitemap settings into its option once
// (studios + cities in, services + clients out) and clear its sitemap cache.
add_action( 'admin_init', function () {
	if ( ! defined( 'RANK_MATH_VERSION' ) || get_option( 'vs_rank_math_sitemap_v1' ) ) {
		return;
	}
	$opts = get_option( 'rank-math-options-sitemap', [] );
	if ( ! is_array( $opts ) ) {
		$opts = [];
	}
	update_option( 'rank-math-options-sitemap', array_merge( $opts, [ 'pt_studio_sitemap' => 'on', 'tax_city_sitemap' => 'on', 'pt_vs_service_sitemap' => 'off', 'pt_vs_client_sitemap' => 'off' ] ) );
	update_option( 'vs_rank_math_sitemap_v1', 1 );
	if ( class_exists( '\RankMath\Sitemap\Cache' ) && method_exists( '\RankMath\Sitemap\Cache', 'invalidate_storage' ) ) {
		\RankMath\Sitemap\Cache::invalidate_storage();
	}
	flush_rewrite_rules();
} );

// ----- <title> -----
add_filter( 'document_title_parts', function ( array $parts ) {
	if ( vs_seo_plugin_active() ) {
		return $parts;
	}
	if ( is_front_page() ) {
		$parts = [ 'title' => __( 'Broadcast & Production Studios for Hire', 'vision-studios' ), 'site' => get_bloginfo( 'name' ) ];
	} elseif ( is_singular( 'studio' ) ) {
		$city = vs_studio_city( get_queried_object_id() );
		$parts['title'] = sprintf( __( '%1$s — TV Studio Hire in %2$s', 'vision-studios' ), $parts['title'], $city ? $city->name : '' );
	} elseif ( is_tax( 'city' ) ) {
		$parts['title'] = sprintf( __( '%s TV & Production Studios for Hire', 'vision-studios' ), get_queried_object()->name );
	} elseif ( is_home() ) {
		$parts['title'] = __( 'News', 'vision-studios' );
	}
	return $parts;
} );
add_filter( 'document_title_separator', fn() => '|' );

// ----- Meta tags (only without an SEO plugin) -----
add_action( 'wp_head', function () {
	if ( vs_seo_plugin_active() ) {
		return;
	}
	$desc  = vs_seo_description();
	$canon = vs_seo_canonical();
	$img   = vs_seo_image();
	$title = wp_get_document_title();
	echo "\n<!-- Vision Studios SEO -->\n";
	if ( $desc ) {
		echo '<meta name="description" content="' . esc_attr( $desc ) . '"/>' . "\n";
	}
// robots: see wp_robots filter below
	if ( $canon && ! is_singular() ) { // WP core already prints rel=canonical on singular views.
		echo '<link rel="canonical" href="' . esc_url( $canon ) . '"/>' . "\n";
	}
	echo '<meta property="og:locale" content="' . esc_attr( get_locale() ) . '"/>' . "\n";
	echo '<meta property="og:type" content="' . ( is_singular( 'post' ) ? 'article' : 'website' ) . '"/>' . "\n";
	echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '"/>' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '"/>' . "\n";
	if ( $desc ) {
		echo '<meta property="og:description" content="' . esc_attr( $desc ) . '"/>' . "\n";
	}
	if ( $canon ) {
		echo '<meta property="og:url" content="' . esc_url( $canon ) . '"/>' . "\n";
	}
	if ( $img ) {
		echo '<meta property="og:image" content="' . esc_url( $img ) . '"/>' . "\n";
		echo '<meta property="og:image:width" content="1600"/><meta property="og:image:height" content="900"/>' . "\n";
		echo '<meta property="og:image:alt" content="' . esc_attr( $title ) . '"/>' . "\n";
	}
	if ( is_singular( 'post' ) ) {
		echo '<meta property="article:published_time" content="' . esc_attr( get_the_date( 'c' ) ) . '"/>' . "\n";
		echo '<meta property="article:modified_time" content="' . esc_attr( get_the_modified_date( 'c' ) ) . '"/>' . "\n";
	}
	echo '<meta name="twitter:card" content="' . ( $img ? 'summary_large_image' : 'summary' ) . '"/>' . "\n";
	echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '"/>' . "\n";
	if ( $desc ) {
		echo '<meta name="twitter:description" content="' . esc_attr( $desc ) . '"/>' . "\n";
	}
	if ( $img ) {
		echo '<meta name="twitter:image" content="' . esc_url( $img ) . '"/>' . "\n";
	}
}, 1 );

// Robots directives through WordPress's own API (keeps max-image-preview:large and avoids duplicate tags).
add_filter( 'wp_robots', function ( array $robots ) {
	if ( is_search() || is_404() || ( is_paged() && ! is_home() ) || is_attachment() ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
		unset( $robots['index'] );
	}
	return $robots;
} );

// Favicon fallback when no Site Icon is set in the Customizer.
add_action( 'wp_head', function () {
	if ( ! has_site_icon() ) {
		echo '<link rel="icon" href="' . esc_url( VS_URI . '/assets/img/logo-mark.png' ) . '" type="image/png"/><link rel="apple-touch-icon" href="' . esc_url( VS_URI . '/assets/img/logo-mark.png' ) . '"/>' . "\n";
	}
}, 3 );

// Preload the LCP hero image on studio, city and front pages.
add_action( 'wp_head', function () {
	if ( is_singular( 'studio' ) || is_front_page() || is_tax( 'city' ) ) {
		$id = vs_seo_image_id();
		if ( $id ) {
			$srcset = wp_get_attachment_image_srcset( $id, 'vs-wide' );
			echo '<link rel="preload" as="image" href="' . esc_url( vs_img_url( $id, 'vs-wide' ) ) . '"' . ( $srcset ? ' imagesrcset="' . esc_attr( $srcset ) . '" imagesizes="100vw"' : '' ) . ' fetchpriority="high"/>' . "\n";
		}
	}
}, 4 );

/** "41.0082° N / 28.9784° E" → [lat, lng] or null. */
function vs_parse_coords( string $s ): ?array {
	if ( preg_match( '/(-?[\d.]+)°?\s*([NS])?\s*\/\s*(-?[\d.]+)°?\s*([EW])?/u', $s, $m ) ) {
		$lat = (float) $m[1] * ( 'S' === ( $m[2] ?? '' ) ? -1 : 1 );
		$lng = (float) $m[3] * ( 'W' === ( $m[4] ?? '' ) ? -1 : 1 );
		return [ $lat, $lng ];
	}
	return null;
}

// ----- Breadcrumbs (data + visible trail + JSON-LD) -----
function vs_breadcrumb_items(): array {
	$items = [ [ __( 'Home', 'vision-studios' ), home_url( '/' ) ] ];
	if ( is_singular( 'studio' ) ) {
		$city = vs_studio_city( get_queried_object_id() );
		$items[] = [ __( 'Studios', 'vision-studios' ), get_post_type_archive_link( 'studio' ) ];
		if ( $city ) {
			$items[] = [ $city->name, get_term_link( $city ) ];
		}
		$items[] = [ get_the_title(), get_permalink() ];
	} elseif ( is_tax( 'city' ) ) {
		$items[] = [ __( 'Studios', 'vision-studios' ), get_post_type_archive_link( 'studio' ) ];
		$items[] = [ get_queried_object()->name, get_term_link( get_queried_object() ) ];
	} elseif ( is_post_type_archive( 'studio' ) ) {
		$items[] = [ __( 'Studios', 'vision-studios' ), get_post_type_archive_link( 'studio' ) ];
	} elseif ( is_singular( 'post' ) ) {
		$items[] = [ __( 'News', 'vision-studios' ), vs_news_url() ];
		$items[] = [ get_the_title(), get_permalink() ];
	} elseif ( is_home() ) {
		$items[] = [ __( 'News', 'vision-studios' ), vs_news_url() ];
	} elseif ( is_page() && ! is_front_page() ) {
		$items[] = [ get_the_title(), get_permalink() ];
	}
	return $items;
}

function vs_breadcrumbs(): string {
	$items = vs_breadcrumb_items();
	if ( count( $items ) < 2 ) {
		return '';
	}
	$out = '<nav class="max-w-7xl mx-auto px-6 lg:px-10 pt-6 font-mono-tag text-xs lg:text-[10px] uppercase tracking-[0.2em] text-white/55" aria-label="' . esc_attr__( 'Breadcrumb', 'vision-studios' ) . '"><ol class="flex flex-wrap gap-2">';
	$last = count( $items ) - 1;
	foreach ( $items as $i => [ $label, $url ] ) {
		$out .= '<li class="flex gap-2">' . ( $i ? '<span aria-hidden="true">/</span>' : '' ) . ( $i === $last ? '<span class="text-white/70" aria-current="page">' . esc_html( $label ) . '</span>' : '<a href="' . esc_url( $url ) . '" class="hover:text-accent">' . esc_html( $label ) . '</a>' ) . '</li>';
	}
	return $out . '</ol></nav>';
}

// ----- JSON-LD -----
add_action( 'wp_head', function () {
	$graph  = [];
	$plugin = vs_seo_plugin_active();
	$org_id = home_url( '/#organization' );
	$phones = array_values( array_filter( [ vs_opt( 'phone_uk' ), vs_opt( 'phone_eu' ), vs_opt( 'phone_tr' ) ] ) );

	if ( ! $plugin ) {
		$graph[] = [
			'@type'  => 'Organization', '@id' => $org_id, 'name' => get_bloginfo( 'name' ), 'url' => home_url( '/' ),
			'logo'   => VS_URI . '/assets/img/logo-mark.png', 'email' => vs_opt( 'email' ), 'telephone' => $phones[0] ?? '',
			'address' => [ '@type' => 'PostalAddress', 'streetAddress' => vs_opt( 'hq_address' ), 'addressCountry' => 'GB' ],
			'sameAs' => array_values( array_filter( [ vs_opt( 'instagram' ), vs_opt( 'linkedin' ) ] ) ),
			'contactPoint' => array_values( array_filter( array_map( fn( $k, $area ) => vs_opt( $k ) ? [ '@type' => 'ContactPoint', 'telephone' => vs_opt( $k ), 'contactType' => 'sales', 'areaServed' => $area, 'availableLanguage' => [ 'en' ] ] : null, [ 'phone_uk', 'phone_eu', 'phone_tr' ], [ 'GB', 'IE', 'TR' ] ) ) ),
		];
		$graph[] = [ '@type' => 'WebPage', '@id' => ( vs_seo_canonical() ?: home_url( '/' ) ) . '#webpage', 'url' => vs_seo_canonical() ?: home_url( '/' ), 'name' => wp_specialchars_decode( wp_get_document_title(), ENT_QUOTES ), 'description' => vs_seo_description(), 'isPartOf' => [ '@id' => home_url( '/#website' ) ], 'primaryImageOfPage' => vs_seo_image() ? [ '@type' => 'ImageObject', 'url' => vs_seo_image() ] : null, 'inLanguage' => get_locale() ];
		$graph[] = [ '@type' => 'WebSite', '@id' => home_url( '/#website' ), 'url' => home_url( '/' ), 'name' => get_bloginfo( 'name' ), 'publisher' => [ '@id' => $org_id ], 'inLanguage' => get_locale() ];
		if ( is_singular( 'post' ) ) {
			$graph[] = [
				'@type' => 'NewsArticle', 'headline' => get_the_title(), 'datePublished' => get_the_date( 'c' ), 'dateModified' => get_the_modified_date( 'c' ),
				'image' => array_values( array_filter( [ get_the_post_thumbnail_url( null, 'vs-wide' ) ] ) ), 'author' => [ '@type' => 'Organization', 'name' => get_bloginfo( 'name' ) ],
				'publisher' => [ '@id' => $org_id ], 'mainEntityOfPage' => get_permalink(), 'description' => vs_seo_description(),
			];
		}
	}

	if ( is_singular( 'studio' ) ) {
		$id   = get_queried_object_id();
		$city = vs_studio_city( $id );
		$cid  = $city ? $city->term_id : 0;
		$graph[] = [
			'@type' => [ 'LocalBusiness', 'Place' ], '@id' => get_permalink() . '#studio', 'name' => get_bloginfo( 'name' ) . ' — ' . get_the_title(), 'url' => get_permalink(),
			'description' => vs_seo_description(), 'image' => array_values( array_filter( array_map( fn( $a ) => vs_img_url( $a, 'vs-wide' ), array_slice( vs_studio_gallery_ids( $id ), 0, 5 ) ) ) ),
			'telephone' => vs_meta( $id, 'phone', vs_term_meta( $cid, 'phone' ) ), 'email' => vs_opt( 'email' ),
			'address' => [ '@type' => 'PostalAddress', 'streetAddress' => vs_meta( $id, 'address', vs_term_meta( $cid, 'address' ) ), 'addressLocality' => $city ? $city->name : '', 'addressCountry' => vs_term_meta( $cid, 'country' ) ],
			'parentOrganization' => [ '@id' => $org_id ], 'priceRange' => '$$', 'openingHours' => vs_opt( 'opening_hours' ),
			'hasMap' => 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( vs_meta( $id, 'map_query', vs_term_meta( $cid, 'map_query' ) ) ),
			'geo' => ( $g = vs_parse_coords( vs_term_meta( $cid, 'coords' ) ) ) ? [ '@type' => 'GeoCoordinates', 'latitude' => $g[0], 'longitude' => $g[1] ] : null,
			'amenityFeature' => array_map( fn( $h ) => [ '@type' => 'LocationFeatureSpecification', 'name' => $h, 'value' => true ], vs_lines( vs_meta( $id, 'highlights' ) ) ),
		];
	} elseif ( is_tax( 'city' ) ) {
		$t = get_queried_object();
		$graph[] = [
			'@type' => 'LocalBusiness', '@id' => get_term_link( $t ) . '#location', 'name' => get_bloginfo( 'name' ) . ' ' . $t->name, 'url' => get_term_link( $t ), 'image' => vs_city_image( $t, 'vs-wide' ),
			'telephone' => vs_term_meta( $t->term_id, 'phone' ), 'email' => vs_opt( 'email' ), 'parentOrganization' => [ '@id' => $org_id ],
			'address' => [ '@type' => 'PostalAddress', 'streetAddress' => vs_term_meta( $t->term_id, 'address' ), 'addressLocality' => $t->name, 'addressCountry' => vs_term_meta( $t->term_id, 'country' ) ],
			'geo' => ( $g = vs_parse_coords( vs_term_meta( $t->term_id, 'coords' ) ) ) ? [ '@type' => 'GeoCoordinates', 'latitude' => $g[0], 'longitude' => $g[1] ] : null,
		];
		$graph[] = [
			'@type' => 'CollectionPage', 'name' => wp_specialchars_decode( wp_get_document_title(), ENT_QUOTES ), 'url' => get_term_link( $t ), 'description' => vs_seo_description(),
			'mainEntity' => [ '@type' => 'ItemList', 'itemListElement' => array_map( fn( $s, $i ) => [ '@type' => 'ListItem', 'position' => $i + 1, 'url' => get_permalink( $s ), 'name' => $s->post_title ], vs_city_studios( $t ), array_keys( vs_city_studios( $t ) ) ) ],
		];
	}

	$crumbs = vs_breadcrumb_items();
	if ( count( $crumbs ) > 1 ) {
		$graph[] = [ '@type' => 'BreadcrumbList', 'itemListElement' => array_map( fn( $c, $i ) => [ '@type' => 'ListItem', 'position' => $i + 1, 'name' => $c[0], 'item' => $c[1] ], $crumbs, array_keys( $crumbs ) ) ];
	}

	// Drop null/empty values so the JSON validates cleanly.
	$clean = function ( $v ) use ( &$clean ) {
		if ( is_array( $v ) ) {
			$v = array_map( $clean, array_filter( $v, fn( $x ) => null !== $x && '' !== $x && [] !== $x ) );
			return array_keys( $v ) === range( 0, count( $v ) - 1 ) || [] === $v ? array_values( $v ) : $v;
		}
		return $v;
	};
	$graph = array_map( $clean, $graph );
	if ( $graph ) {
		echo '<script type="application/ld+json">' . wp_json_encode( [ '@context' => 'https://schema.org', '@graph' => $graph ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
	}
}, 5 );

// ----- Head clean-up / performance -----
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
add_filter( 'emoji_svg_url', '__return_false' );

// Attachment pages add no value: send them to the parent post or 404.
add_action( 'template_redirect', function () {
	if ( is_attachment() ) {
		$parent = wp_get_post_parent_id( get_queried_object_id() );
		wp_safe_redirect( $parent ? get_permalink( $parent ) : home_url( '/' ), 301 );
		exit;
	}
} );

// Alt text fallback: attachments without alt text inherit the title of the studio/post using them.
add_filter( 'wp_get_attachment_image_attributes', function ( array $attr, WP_Post $attachment ) {
	if ( empty( $attr['alt'] ) ) {
		$attr['alt'] = trim( wp_strip_all_tags( $attachment->post_excerpt ?: $attachment->post_title ) );
	}
	return $attr;
}, 10, 2 );
