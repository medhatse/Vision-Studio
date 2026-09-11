<?php
/**
 * Content importer: Studios → Import Content. Loads data/import.json (studios, cities, services,
 * clients, pages, news, settings). Images are matched against the existing media library by URL and
 * only downloaded when missing. Safe to re-run: existing items (matched by slug/title) are updated.
 */
defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', function () {
	add_submenu_page( 'edit.php?post_type=studio', __( 'Import Content', 'vision-studios' ), __( 'Import Content', 'vision-studios' ), 'manage_options', 'vs-import', 'vs_import_page' );
} );

function vs_import_page(): void {
	$report = null;
	if ( isset( $_POST['vs_import'] ) && check_admin_referer( 'vs_import' ) && current_user_can( 'manage_options' ) ) {
		set_time_limit( 0 );
		$report = vs_run_import( [
			'posts'         => ! empty( $_POST['with_posts'] ),
			'draft_legacy'  => ! empty( $_POST['draft_legacy'] ),
			'set_front'     => ! empty( $_POST['set_front'] ),
		] );
	}
	$file = VS_DIR . '/data/import.json';
	echo '<div class="wrap"><h1>' . esc_html__( 'Import Vision Studios content', 'vision-studios' ) . '</h1>';
	if ( $report ) {
		echo '<div class="notice notice-success"><p><strong>' . esc_html__( 'Import finished.', 'vision-studios' ) . '</strong></p><ul style="list-style:disc;padding-left:20px">';
		foreach ( $report as $line ) {
			echo '<li>' . esc_html( $line ) . '</li>';
		}
		echo '</ul></div>';
	}
	if ( ! file_exists( $file ) ) {
		echo '<p>' . esc_html__( 'data/import.json is missing from the theme.', 'vision-studios' ) . '</p></div>';
		return;
	}
	$data = json_decode( file_get_contents( $file ), true );
	echo '<p>' . esc_html( sprintf( __( 'The theme ships with %1$d studios in %2$d cities, %3$d services, %4$d clients and %5$d news posts, generated on %6$s from vision-studios.net.', 'vision-studios' ), count( $data['studios'] ), count( $data['cities'] ), count( $data['services'] ), count( $data['clients'] ), count( $data['posts'] ), substr( $data['generated'], 0, 10 ) ) ) . '</p>';
	echo '<p>' . esc_html__( 'Images are looked up in your media library by URL first; anything missing is downloaded from vision-studios.net. Running the import again updates existing items rather than duplicating them.', 'vision-studios' ) . '</p>';
	echo '<form method="post">';
	wp_nonce_field( 'vs_import' );
	echo '<p><label><input type="checkbox" name="with_posts" value="1"/> ' . esc_html__( 'Also import news posts (skipped automatically for posts that already exist — leave unticked on the live site, where the posts already exist)', 'vision-studios' ) . '</label></p>';
	echo '<p><label><input type="checkbox" name="draft_legacy" value="1" checked/> ' . esc_html__( 'Set the old Elementor pages (london-studio-1, about-us, …) to Draft so their URLs redirect to the new pages', 'vision-studios' ) . '</label></p>';
	echo '<p><label><input type="checkbox" name="set_front" value="1" checked/> ' . esc_html__( 'Use the new Home page as the front page and the News page for posts', 'vision-studios' ) . '</label></p>';
	submit_button( __( 'Run import', 'vision-studios' ), 'primary', 'vs_import' );
	echo '</form></div>';
}

/** Find (or download) an attachment for a remote upload URL. Returns attachment ID or 0. */
function vs_import_attachment( string $url, array &$cache ): int {
	if ( ! $url ) {
		return 0;
	}
	if ( isset( $cache[ $url ] ) ) {
		return $cache[ $url ];
	}
	// 1. Downloaded by a previous run of this importer (exact source URL).
	$q  = get_posts( [ 'post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => 1, 'meta_key' => '_vs_source_url', 'meta_value' => $url, 'fields' => 'ids' ] );
	$id = $q ? (int) $q[0] : 0;
	// 2. Already in this site's media library under the same URL (the live vision-studios.net case).
	if ( ! $id ) {
		foreach ( array_unique( [ $url, str_replace( 'http://', 'https://', $url ), preg_replace( '/-scaled(?=\.\w+$)/', '', $url ), preg_replace( '/(?=\.\w+$)/', '-scaled', $url, 1 ) ] ) as $c ) {
			$id = (int) attachment_url_to_postid( $c );
			if ( $id ) {
				break;
			}
		}
	}
	// 3. Same upload path (year/month/file) on a site served from another domain — exact match only.
	if ( ! $id && preg_match( '#/wp-content/uploads/(.+)$#', $url, $m ) ) {
		foreach ( array_unique( [ $m[1], preg_replace( '/-scaled(?=\.\w+$)/', '', $m[1] ), preg_replace( '/(?=\.\w+$)/', '-scaled', $m[1], 1 ) ] ) as $rel ) {
			$q = get_posts( [ 'post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => 1, 'meta_key' => '_wp_attached_file', 'meta_value' => $rel, 'fields' => 'ids' ] );
			if ( $q ) {
				$id = (int) $q[0];
				break;
			}
		}
	}
	if ( ! $id ) {
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$res = media_sideload_image( $url, 0, null, 'id' );
		if ( ! is_wp_error( $res ) ) {
			$id = (int) $res;
			update_post_meta( $id, '_vs_source_url', $url );
		}
	}
	$cache[ $url ] = $id;
	return $id;
}

function vs_import_blocks( array $blocks ): string {
	$out = '';
	foreach ( $blocks as [ $type, $content ] ) {
		switch ( $type ) {
			case 'h2': $out .= "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">" . esc_html( $content ) . "</h2>\n<!-- /wp:heading -->\n\n"; break;
			case 'h3': $out .= "<!-- wp:heading {\"level\":3} -->\n<h3 class=\"wp-block-heading\">" . esc_html( $content ) . "</h3>\n<!-- /wp:heading -->\n\n"; break;
			case 'ul': $out .= "<!-- wp:list -->\n<ul class=\"wp-block-list\">" . implode( '', array_map( fn( $l ) => "<!-- wp:list-item --><li>" . esc_html( $l ) . '</li><!-- /wp:list-item -->', (array) $content ) ) . "</ul>\n<!-- /wp:list -->\n\n"; break;
			case 'img': $out .= "<!-- wp:image {\"id\":$content} -->\n<figure class=\"wp-block-image size-large\">" . wp_get_attachment_image( (int) $content, 'large' ) . "</figure>\n<!-- /wp:image -->\n\n"; break;
			case 'embed': $out .= "<!-- wp:embed {\"url\":\"" . esc_url( $content ) . "\",\"type\":\"video\",\"providerNameSlug\":\"youtube\",\"responsive\":true} -->\n<figure class=\"wp-block-embed is-type-video is-provider-youtube wp-block-embed-youtube\"><div class=\"wp-block-embed__wrapper\">\n" . esc_url( $content ) . "\n</div></figure>\n<!-- /wp:embed -->\n\n"; break;
			default: $out .= "<!-- wp:paragraph -->\n<p>" . esc_html( $content ) . "</p>\n<!-- /wp:paragraph -->\n\n";
		}
	}
	return $out;
}

/** Upsert a post by slug + type. */
function vs_import_upsert( array $args ): int {
	$existing = get_posts( [ 'post_type' => $args['post_type'], 'name' => $args['post_name'], 'post_status' => 'any', 'posts_per_page' => 1 ] );
	if ( $existing ) {
		$args['ID'] = $existing[0]->ID;
		return (int) wp_update_post( $args );
	}
	return (int) wp_insert_post( $args );
}

function vs_run_import( array $opts = [] ): array {
	$data   = json_decode( file_get_contents( VS_DIR . '/data/import.json' ), true );
	$cache  = [];
	$report = [];
	$img    = function ( $u ) use ( &$cache ) {
		return vs_import_attachment( (string) $u, $cache );
	};

	// Settings.
	foreach ( $data['options'] as $k => $v ) {
		set_theme_mod( "vs_$k", 'hero_image' === $k ? $img( $v ) : $v );
	}
	$report[] = __( 'Theme settings saved (contact details, hero, stats, counters).', 'vision-studios' );

	// Cities.
	$term_ids = [];
	foreach ( $data['cities'] as $c ) {
		$t = term_exists( $c['slug'], 'city' );
		$t = $t ? (int) $t['term_id'] : (int) wp_insert_term( $c['name'], 'city', [ 'slug' => $c['slug'] ] )['term_id'];
		foreach ( [ 'country', 'tagline', 'heading', 'phone', 'address', 'map_query', 'coords', 'faq' ] as $k ) {
			update_term_meta( $t, "_vs_$k", $c[ $k ] ?? '' );
		}
		if ( ! empty( $c['description'] ) ) {
			wp_update_term( $t, 'city', [ 'description' => $c['description'] ] );
		}
		update_term_meta( $t, '_vs_image', $img( $c['image'] ) );
		$term_ids[ $c['slug'] ] = $t;
	}
	$report[] = sprintf( __( '%d cities.', 'vision-studios' ), count( $term_ids ) );

	// Studios.
	foreach ( $data['studios'] as $s ) {
		$gallery = array_filter( array_map( $img, $s['gallery'] ) );
		$args = [ 'post_type' => 'studio', 'post_name' => $s['slug'], 'post_title' => $s['title'], 'post_status' => 'publish', 'post_excerpt' => $s['excerpt'], 'menu_order' => $s['order'] ];
		if ( ! empty( $s['content'] ) ) {
			$args['post_content'] = vs_import_blocks( $s['content'] );
		}
		$id = vs_import_upsert( $args );
		wp_set_object_terms( $id, [ $term_ids[ $s['city'] ] ], 'city' );
		foreach ( [ 'tagline', 'area', 'specs', 'highlights', 'use_cases_heading', 'use_cases', 'phone', 'address', 'map_query', 'faq' ] as $k ) {
			if ( isset( $s[ $k ] ) ) {
				update_post_meta( $id, "_vs_$k", $s[ $k ] );
			}
		}
		if ( ! empty( $s['price_from'] ) || '' === vs_meta( $id, 'price_from' ) ) {
			update_post_meta( $id, '_vs_price_from', $s['price_from'] ?? '' );
		}
		foreach ( array_values( $gallery ) as $gi => $aid ) {
			if ( ! get_post_meta( $aid, '_wp_attachment_image_alt', true ) ) {
				update_post_meta( $aid, '_wp_attachment_image_alt', sprintf( '%s — photo %d', $s['title'], $gi + 1 ) );
			}
		}
		update_post_meta( $id, '_vs_gallery', implode( ',', $gallery ) );
		update_post_meta( $id, '_vs_floor_plan', $img( $s['floor_plan'] ) );
		if ( $gallery ) {
			set_post_thumbnail( $id, reset( $gallery ) );
		}
	}
	$report[] = sprintf( __( '%d studios with galleries and floor plans.', 'vision-studios' ), count( $data['studios'] ) );

	// Services & reasons.
	foreach ( $data['services'] as $s ) {
		$id = vs_import_upsert( [ 'post_type' => 'vs_service', 'post_name' => sanitize_title( $s['type'] . '-' . $s['title'] ), 'post_title' => $s['title'], 'post_status' => 'publish', 'post_excerpt' => $s['excerpt'], 'post_content' => $s['content'], 'menu_order' => $s['order'] ] );
		update_post_meta( $id, '_vs_icon', $s['icon'] );
		update_post_meta( $id, '_vs_type', $s['type'] );
	}
	$report[] = sprintf( __( '%d services and reasons.', 'vision-studios' ), count( $data['services'] ) );

	// Clients.
	foreach ( $data['clients'] as $c ) {
		$id = vs_import_upsert( [ 'post_type' => 'vs_client', 'post_name' => sanitize_title( $c['name'] ), 'post_title' => $c['name'], 'post_status' => 'publish', 'menu_order' => $c['order'] ] );
		if ( $logo = $img( $c['logo'] ) ) {
			set_post_thumbnail( $id, $logo );
			update_post_meta( $logo, '_wp_attachment_image_alt', $c['name'] . ' logo' );
		}
	}
	$report[] = sprintf( __( '%d client logos.', 'vision-studios' ), count( $data['clients'] ) );

	// Pages.
	$pages = [
		'home'    => [ __( 'Home', 'vision-studios' ), '', [], '' ],
		'about'   => [ __( 'About Us', 'vision-studios' ), 'template-about.php', $data['pages']['about']['blocks'], $data['pages']['about']['excerpt'], $data['pages']['about']['image'] ?? '' ],
		'contact' => [ __( 'Contact Us', 'vision-studios' ), 'template-contact.php', $data['pages']['contact']['blocks'], $data['pages']['contact']['excerpt'] ],
		'gallery' => [ __( 'Gallery', 'vision-studios' ), 'template-gallery.php', [], __( 'Photographs from every Vision Studios broadcast and production studio in London, Dublin, Paris and Istanbul.', 'vision-studios' ) ],
		'news'    => [ __( 'News', 'vision-studios' ), '', [], '' ],
	];
	$page_ids = [];
	foreach ( $pages as $slug => $p ) {
		$page_ids[ $slug ] = vs_import_upsert( [ 'post_type' => 'page', 'post_name' => $slug, 'post_title' => $p[0], 'post_status' => 'publish', 'post_content' => vs_import_blocks( $p[2] ), 'post_excerpt' => $p[3] ] );
		if ( $p[1] ) {
			update_post_meta( $page_ids[ $slug ], '_wp_page_template', $p[1] );
		}
		if ( ! empty( $p[4] ) && ( $t = $img( $p[4] ) ) ) {
			set_post_thumbnail( $page_ids[ $slug ], $t );
		}
	}
	if ( ! empty( $opts['set_front'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $page_ids['home'] );
		update_option( 'page_for_posts', $page_ids['news'] );
	}
	$report[] = __( 'Pages: Home, About Us, Contact Us, Gallery, News.', 'vision-studios' );

	// News posts (only where missing).
	if ( ! empty( $opts['posts'] ) ) {
		$n = 0;
		foreach ( $data['posts'] as $p ) {
			if ( get_posts( [ 'name' => $p['slug'], 'post_status' => 'any', 'posts_per_page' => 1 ] ) ) {
				continue;
			}
			$blocks = array_map( fn( $t ) => [ preg_match( '/^[^.]{3,70}$/', $t ) && ! preg_match( '/[.!?…]$/', $t ) && str_word_count( $t ) < 12 ? 'h2' : 'p', $t ], $p['paragraphs'] );
			foreach ( $p['videos'] as $v ) {
				$blocks[] = [ 'embed', $v ];
			}
			$featured = $img( $p['image'] );
			foreach ( $p['images'] as $u ) {
				if ( $u !== $p['image'] && ( $aid = $img( $u ) ) ) {
					$blocks[] = [ 'img', $aid ];
				}
			}
			$id = wp_insert_post( [ 'post_type' => 'post', 'post_name' => $p['slug'], 'post_title' => $p['title'], 'post_status' => 'publish', 'post_date' => $p['date'] . ' 12:00:00', 'post_excerpt' => $p['excerpt'], 'post_content' => vs_import_blocks( $blocks ) ] );
			if ( $featured ) {
				set_post_thumbnail( $id, $featured );
			}
			$n++;
		}
		$report[] = sprintf( __( '%d news posts imported (existing posts left untouched).', 'vision-studios' ), $n );
	}

	// Old Elementor pages → draft (their URLs then 301 to the new content via inc/redirects.php).
	if ( ! empty( $opts['draft_legacy'] ) ) {
		$n = 0;
		foreach ( $data['legacy_pages'] as $slug ) {
			$old = get_page_by_path( $slug );
			if ( $old && 'publish' === $old->post_status && ! isset( $page_ids[ $slug ] ) ) {
				wp_update_post( [ 'ID' => $old->ID, 'post_status' => 'draft' ] );
				$n++;
			}
		}
		$report[] = sprintf( __( '%d old pages set to Draft; their old URLs now redirect.', 'vision-studios' ), $n );
	}

	flush_rewrite_rules();
	$report[] = sprintf( __( 'Images resolved: %d.', 'vision-studios' ), count( array_filter( $cache ) ) );
	return $report;
}

// WP-CLI: wp vs import [--posts] [--no-draft-legacy]
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'vs import', function ( $args, $assoc ) {
		foreach ( vs_run_import( [ 'posts' => isset( $assoc['posts'] ), 'draft_legacy' => ! isset( $assoc['no-draft-legacy'] ), 'set_front' => true ] ) as $line ) {
			WP_CLI::log( $line );
		}
		WP_CLI::success( 'Import complete.' );
	} );
}
