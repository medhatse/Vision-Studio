<?php
/**
 * Plugin compatibility: keep Elementor to the posts that use it, and force https on same-domain assets.
 */
defined( 'ABSPATH' ) || exit;

/** True when the current singular view was built with Elementor (news posts on vision-studios.net). */
function vs_is_elementor_view(): bool {
	// The theme renders the front page and the studio/city/news/about/contact/gallery pages itself, even when
	// an old Elementor page with the same slug was reused by the importer.
	if ( ! is_singular() || is_front_page() || is_page() || ! class_exists( '\Elementor\Plugin' ) ) {
		return false;
	}
	$doc = \Elementor\Plugin::$instance->documents->get( get_queried_object_id() );
	return $doc && $doc->is_built_with_elementor();
}

// Elementor enqueues its frontend CSS/JS, widget styles, kit CSS, Font Awesome and locally hosted Google
// Fonts on every page. The theme uses none of it, so drop it everywhere except Elementor-built posts.
add_action( 'wp_enqueue_scripts', function () {
	if ( is_admin() || vs_is_elementor_view() ) {
		return;
	}
	$pattern = '/^(elementor|e-|widget-|font-awesome|eicons|swiper|elementor-gf-)/';
	foreach ( wp_styles()->queue as $handle ) {
		if ( preg_match( $pattern, $handle ) ) {
			wp_dequeue_style( $handle );
		}
	}
	foreach ( wp_scripts()->queue as $handle ) {
		if ( preg_match( $pattern, $handle ) ) {
			wp_dequeue_script( $handle );
		}
	}
}, 100 );

// Elementor's locally hosted Google Fonts stylesheet was generated with an http:// base URL, so browsers
// block it as mixed content on https pages anyway. Drop it on Elementor posts too (text falls back to the
// theme fonts) until Elementor → Tools → Replace URL has been run.
add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_ssl() ) {
		return;
	}
	foreach ( wp_styles()->registered as $handle => $style ) {
		if ( str_starts_with( $handle, 'elementor-gf-' ) && is_string( $style->src ) && 0 === strpos( $style->src, 'http://' ) ) {
			wp_dequeue_style( $handle );
		}
	}
}, 101 );

// Same-domain asset URLs stored with http:// (old uploads, Elementor data) → https when the site is https.
function vs_force_https_url( $url ) {
	if ( is_string( $url ) && is_ssl() && 0 === strpos( $url, 'http://' ) ) {
		$host = wp_parse_url( home_url(), PHP_URL_HOST );
		if ( $host && 0 === strpos( $url, 'http://' . $host ) ) {
			return 'https://' . substr( $url, 7 );
		}
	}
	return $url;
}
foreach ( [ 'style_loader_src', 'script_loader_src', 'wp_get_attachment_url', 'wp_get_attachment_image_src', 'the_content', 'widget_text' ] as $vs_hook ) {
	add_filter( $vs_hook, function ( $value ) {
		if ( is_array( $value ) && isset( $value[0] ) ) {
			$value[0] = vs_force_https_url( $value[0] );
			return $value;
		}
		if ( is_string( $value ) && is_ssl() ) {
			$host = wp_parse_url( home_url(), PHP_URL_HOST );
			return $host ? str_replace( 'http://' . $host . '/', 'https://' . $host . '/', $value ) : $value;
		}
		return $value;
	}, 20 );
}

// Rank Math structured data: same treatment for the Organization logo and any other stored http URL.
add_filter( 'rank_math/json_ld', function ( $data ) {
	if ( ! is_ssl() ) {
		return $data;
	}
	$host = wp_parse_url( home_url(), PHP_URL_HOST );
	$fix  = function ( $v ) use ( &$fix, $host ) {
		if ( is_array( $v ) ) {
			return array_map( $fix, $v );
		}
		return is_string( $v ) ? str_replace( 'http://' . $host . '/', 'https://' . $host . '/', $v ) : $v;
	};
	return $fix( $data );
}, 99 );

// Elementor / PRO Elements enqueue kit, theme-builder and font styles later than wp_enqueue_scripts,
// so repeat the clean-up right before styles and scripts are printed.
function vs_dequeue_elementor_late(): void {
	if ( is_admin() || vs_is_elementor_view() ) {
		return;
	}
	$pattern = '/^(elementor|e-|widget-|font-awesome|eicons|swiper|elementor-gf-)/';
	foreach ( (array) wp_styles()->queue as $handle ) {
		if ( preg_match( $pattern, $handle ) ) {
			wp_dequeue_style( $handle );
		}
	}
	foreach ( (array) wp_scripts()->queue as $handle ) {
		if ( preg_match( $pattern, $handle ) ) {
			wp_dequeue_script( $handle );
		}
	}
}
add_action( 'wp_print_styles', 'vs_dequeue_elementor_late', 100 );
add_action( 'wp_print_scripts', 'vs_dequeue_elementor_late', 100 );
add_action( 'wp_print_footer_scripts', 'vs_dequeue_elementor_late', 1 );

// ----- Nginx FastCGI page cache -----
// The Nginx Helper plugin only deletes cache files from the path in RT_WP_NGINX_HELPER_CACHE_PATH, which on
// this host points at an empty folder. Until wp-config.php is corrected, purge the real cache directory
// (aaPanel default: /www/server/fastcgi_cache) whenever Nginx Helper purges, or a studio/page/post changes.
if ( ! defined( 'VS_NGINX_CACHE_PATH' ) ) {
	define( 'VS_NGINX_CACHE_PATH', '/www/server/fastcgi_cache' );
}
function vs_purge_nginx_cache(): int {
	$dir = VS_NGINX_CACHE_PATH;
	if ( ! is_dir( $dir ) || ! is_writable( $dir ) ) {
		return 0;
	}
	$n = 0;
	try {
		$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::CHILD_FIRST );
		foreach ( $it as $file ) {
			if ( $file->isFile() && @unlink( $file->getPathname() ) ) {
				$n++;
			}
		}
	} catch ( Exception $e ) {
		return $n;
	}
	return $n;
}
add_action( 'rt_nginx_helper_after_purge_all', 'vs_purge_nginx_cache' );
add_action( 'rt_nginx_helper_after_fastcgi_purge_all', 'vs_purge_nginx_cache' );
add_action( 'transition_post_status', function ( $new, $old, $post ) {
	if ( in_array( $post->post_type, [ 'studio', 'page', 'post', 'vs_service', 'vs_client' ], true ) && ( 'publish' === $new || 'publish' === $old ) && ! wp_is_post_autosave( $post ) ) {
		vs_purge_nginx_cache();
	}
}, 10, 3 );
add_action( 'customize_save_after', 'vs_purge_nginx_cache' );
add_action( 'after_switch_theme', 'vs_purge_nginx_cache' );

// Admin-only manual purge with a report: /wp-admin/?vs_cache_purge=1
add_action( 'admin_init', function () {
	if ( empty( $_GET['vs_cache_purge'] ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	header( 'Content-Type: text/plain' );
	$dir = VS_NGINX_CACHE_PATH;
	echo 'cache dir: ', $dir, ' | exists: ', is_dir( $dir ) ? 'yes' : 'no', ' | writable: ', is_writable( $dir ) ? 'yes' : 'no', "\n";
	if ( is_dir( $dir ) ) {
		$before = 0;
		foreach ( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ) ) as $f ) {
			if ( $f->isFile() ) {
				$before++;
			}
		}
		echo 'files before: ', $before, "\n";
		echo 'deleted: ', vs_purge_nginx_cache(), "\n";
	}
	echo 'php user id: ', function_exists( 'posix_geteuid' ) ? posix_geteuid() : 'n/a', ' | dir owner id: ', is_dir( $dir ) ? fileowner( $dir ) : 'n/a', ' | perms: ', is_dir( $dir ) ? substr( sprintf( '%o', fileperms( $dir ) ), -4 ) : 'n/a', "\n";
	exit;
} );

// Contact Form 7 drops unknown shortcode attributes; allow "studio" through so
// "[text studio default:shortcode_attr]" is prefilled with the studio being booked.
add_filter( 'shortcode_atts_wpcf7', function ( $out, $pairs, $atts ) {
	if ( isset( $atts['studio'] ) ) {
		$out['studio'] = sanitize_text_field( $atts['studio'] );
	}
	return $out;
}, 10, 3 );

// ----- Contact Form 7 + reCAPTCHA: load on demand -----
// Google's reCAPTCHA script is the heaviest asset on studio pages and only matters once someone reaches
// the booking form. WordPress prints the script tags (and the plugin's inline config) as usual, but the
// tags are neutralised (type="text/plain") and re-created by the loader below when the form scrolls near
// the viewport or the visitor interacts with the page. Works with CF7's built-in v3 integration and the
// "ReCaptcha v2 for Contact Form 7" plugin.
add_filter( 'script_loader_tag', function ( $tag, $handle, $src ) {
	$order = [ 'wpcf7-recaptcha-controls' => 1, 'wpcf7-recaptcha' => 1, 'google-recaptcha' => 2 ];
	if ( is_admin() || ! isset( $order[ $handle ] ) ) {
		return $tag;
	}
	// The v2 plugin's controls script defines the api.js onload callback, so it must load before api.js.
	return '<script type="text/plain" data-vs-lazy-src="' . esc_url( $src ) . '" data-vs-order="' . $order[ $handle ] . '"></script>' . "\n";
}, 10, 3 );
add_action( 'wp_print_footer_scripts', function () {
	if ( is_admin() ) {
		return;
	}
	?>
<script>
(function(){var done=false;
function load(){if(done)return;done=true;var tags=[].slice.call(document.querySelectorAll('script[data-vs-lazy-src]')).sort(function(a,b){return a.dataset.vsOrder-b.dataset.vsOrder;});
(function next(i){if(i>=tags.length)return;var s=document.createElement('script');s.src=tags[i].dataset.vsLazySrc;s.async=true;s.onload=function(){next(i+1);};document.head.appendChild(s);})(0);}
var forms=document.querySelectorAll('.wpcf7');if(!forms.length||!document.querySelector('script[data-vs-lazy-src]'))return;
if('IntersectionObserver' in window){var io=new IntersectionObserver(function(es){es.forEach(function(e){if(e.isIntersecting){load();io.disconnect();}});},{rootMargin:'600px'});forms.forEach(function(f){io.observe(f);});}else{load();}
['pointerdown','keydown','touchstart'].forEach(function(ev){window.addEventListener(ev,load,{once:true,passive:true});});
})();
</script>
	<?php
}, 100 );

// ----- Front-end weight -----
// jQuery is only needed by plugin scripts that print in the footer; loading it in <head> blocks the first
// paint. Move it (and jquery-migrate) to the footer on the front end.
add_action( 'wp_enqueue_scripts', function () {
	if ( is_admin() ) {
		return;
	}
	foreach ( [ 'jquery', 'jquery-core', 'jquery-migrate' ] as $h ) {
		wp_scripts()->add_data( $h, 'group', 1 );
	}
	// The theme styles page content (prose-vs) and Contact Form 7 forms itself; these stylesheets are unused.
	foreach ( [ 'wp-block-library', 'wp-block-library-theme', 'global-styles', 'classic-theme-styles', 'contact-form-7' ] as $h ) {
		wp_dequeue_style( $h );
	}
}, 100 );

// ----- Image sizes for media uploaded before the theme -----
// WordPress only creates a theme's custom sizes (vs-card / vs-wide / vs-thumb) at upload time, so images
// that were already in the library fall back to the full-size file. Admin-only, batched:
// /wp-admin/?vs_regen=1&offset=0  (processes 15 attachments per request; follow the "next" link)
add_action( 'admin_init', function () {
	if ( ! isset( $_GET['vs_regen'] ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';
	set_time_limit( 120 );
	$offset = max( 0, (int) ( $_GET['offset'] ?? 0 ) );
	$per    = 15;
	$ids    = [];
	foreach ( get_posts( [ 'post_type' => [ 'studio', 'vs_client', 'vs_testimonial', 'page', 'post' ], 'posts_per_page' => -1, 'post_status' => 'any', 'fields' => 'ids' ] ) as $pid ) {
		$ids = array_merge( $ids, array_map( 'intval', explode( ',', (string) get_post_meta( $pid, '_vs_gallery', true ) ) ), [ (int) get_post_thumbnail_id( $pid ), (int) get_post_meta( $pid, '_vs_floor_plan', true ) ] );
	}
	foreach ( get_terms( [ 'taxonomy' => 'city', 'hide_empty' => false ] ) as $t ) {
		$ids[] = (int) get_term_meta( $t->term_id, '_vs_image', true );
	}
	$ids   = array_values( array_unique( array_filter( $ids ) ) );
	$batch = array_slice( $ids, $offset, $per );
	header( 'Content-Type: text/plain' );
	echo 'attachments: ', count( $ids ), " | batch from ", $offset, "\n";
	foreach ( $batch as $aid ) {
		$file = get_attached_file( $aid );
		if ( ! $file || ! file_exists( $file ) ) {
			echo $aid, ': missing file', "\n";
			continue;
		}
		$meta = wp_get_attachment_metadata( $aid );
		if ( ! empty( $meta['sizes']['vs-wide'] ) || ! empty( $meta['sizes']['vs-card'] ) ) {
			echo $aid, ': already has theme sizes', "\n";
			continue;
		}
		$new = wp_generate_attachment_metadata( $aid, $file );
		if ( is_wp_error( $new ) || empty( $new ) ) {
			echo $aid, ': failed', "\n";
			continue;
		}
		wp_update_attachment_metadata( $aid, $new );
		echo $aid, ': ', implode( ', ', array_keys( $new['sizes'] ?? [] ) ), "\n";
	}
	$next = $offset + $per;
	echo $next < count( $ids ) ? 'next: ' . admin_url( '?vs_regen=1&offset=' . $next ) : 'done', "\n";
	exit;
} );

// Admin-only performance snapshot: /wp-admin/?vs_perf_diag=1
add_action( 'admin_init', function () {
	if ( empty( $_GET['vs_perf_diag'] ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	header( 'Content-Type: text/plain' );
	$oc = function_exists( 'opcache_get_status' ) ? opcache_get_status( false ) : false;
	echo 'php: ', PHP_VERSION, ' sapi: ', PHP_SAPI, "\n";
	echo 'opcache extension: ', extension_loaded( 'Zend OPcache' ) ? 'loaded' : 'MISSING', ' | opcache.enable=', ini_get( 'opcache.enable' ), ' | status: ', $oc ? ( $oc['opcache_enabled'] ? 'enabled, ' . $oc['opcache_statistics']['num_cached_scripts'] . ' scripts cached, hit rate ' . round( $oc['opcache_statistics']['opcache_hit_rate'] ) . '%' : 'disabled' ) : 'n/a', "\n";
	echo 'time to admin_init: ', timer_stop( 0, 3 ), 's | db queries so far: ', get_num_queries(), ' | peak memory: ', round( memory_get_peak_usage() / 1048576 ), 'M', "\n";
	echo 'opcache limits: memory ', ini_get( 'opcache.memory_consumption' ), 'M | max files ', ini_get( 'opcache.max_accelerated_files' ), ' | used ', $oc ? round( $oc['memory_usage']['used_memory'] / 1048576 ) . 'M' : 'n/a', ' | wasted ', $oc ? round( $oc['memory_usage']['current_wasted_percentage'] ) . '%' : 'n/a', "\n";
	echo 'php.ini: memory_limit ', ini_get( 'memory_limit' ), ' | display_errors ', ini_get( 'display_errors' ) ?: 'off', ' | timezone ', ini_get( 'date.timezone' ), "\n";
	echo 'object cache: ', wp_using_ext_object_cache() ? 'persistent' : 'none (default)', "\n";
	$t = microtime( true ); wp_remote_get( 'https://www.google.com/generate_204', [ 'timeout' => 5 ] ); echo 'outbound http test: ', round( microtime( true ) - $t, 2 ), 's', "\n";
	$t = microtime( true ); global $wpdb; $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $wpdb->posts ); echo 'db roundtrip: ', round( ( microtime( true ) - $t ) * 1000 ), 'ms', "\n";
	echo 'active plugins: ', implode( ', ', array_map( fn( $p ) => dirname( $p ), (array) get_option( 'active_plugins' ) ) ), "\n";
	exit;
} );

// ----- Contact Form 7 honeypot -----
// Baseline spam protection that needs no keys: a hidden field real visitors never fill in. Submissions
// with it filled are marked as spam (CF7 shows its spam message and sends nothing). reCAPTCHA can be
// re-added to the forms on top of this once keys are configured under Contact → Integration.
add_filter( 'wpcf7_form_elements', function ( $html ) {
	return $html . '<span class="vs-hp" aria-hidden="true"><label>Leave this field empty <input type="text" name="vs_website" value="" tabindex="-1" autocomplete="off"/></label></span>';
} );
add_filter( 'wpcf7_spam', function ( $spam ) {
	return $spam || ! empty( $_POST['vs_website'] ); // phpcs:ignore
} );
add_action( 'wp_head', function () {
	echo '<style>.vs-hp{position:absolute!important;left:-9999px!important;width:1px;height:1px;overflow:hidden}</style>' . "\n";
}, 6 );

// ----- Content hygiene for imported / Elementor posts -----
// Old page URLs inside post content → the new structure (avoids link-to-redirect warnings), dead editor
// placeholders unwrapped, and Elementor posts that carry their own <h1> get it demoted so each page has one.
add_filter( 'the_content', function ( $html ) {
	if ( ! is_string( $html ) || '' === $html ) {
		return $html;
	}
	$host = preg_quote( wp_parse_url( home_url(), PHP_URL_HOST ), '#' );
	static $map = null;
	if ( null === $map ) {
		$map = [ 'about-us' => vs_page_url( 'about' ), 'contact-us' => vs_page_url( 'contact' ), 'blogs' => vs_news_url() ];
		foreach ( get_posts( [ 'post_type' => 'studio', 'posts_per_page' => -1, 'fields' => 'ids' ] ) as $sid ) {
			$map[ get_post_field( 'post_name', $sid ) ] = get_permalink( $sid );
		}
		foreach ( vs_cities() as $c ) {
			$map[ $c->slug ] = get_term_link( $c );
		}
	}
	$html = preg_replace_callback( '#href="https?://' . $host . '/([a-z0-9-]+)/?(?:[?\#][^"]*)?"#i', function ( $m ) use ( $map ) {
		return isset( $map[ $m[1] ] ) ? 'href="' . esc_url( $map[ $m[1] ] ) . '"' : $m[0];
	}, $html );
	$html = preg_replace( '#<a\s[^>]*href="[^"]*_wp_link_placeholder[^"]*"[^>]*>(.*?)</a>#is', '$1', $html );
	if ( is_singular( 'post' ) && function_exists( 'vs_is_elementor_view' ) && vs_is_elementor_view() ) {
		$html = preg_replace( '#<h1(\s[^>]*)?>#i', '<h2$1>', $html );
		$html = preg_replace( '#</h1>#i', '</h2>', $html );
	}
	return $html;
}, 20 );

// Cloudflare's "Email Address Obfuscation" rewrites mailto links into /cdn-cgi/l/email-protection, which
// crawlers see as a 404. These markers tell Cloudflare to leave the wrapped markup alone.
function vs_email_off( string $html ): string {
	return '<!--email_off-->' . $html . '<!--/email_off-->';
}
add_filter( 'the_content', function ( $html ) {
	return is_string( $html ) && false !== strpos( $html, 'mailto:' ) ? preg_replace( '#(<a\s[^>]*href="mailto:[^"]*"[^>]*>.*?</a>)#is', '<!--email_off-->$1<!--/email_off-->', $html ) : $html;
}, 21 );

// /llms.txt — a plain-text guide for AI search crawlers (same idea as robots.txt).
add_action( 'template_redirect', function () {
	if ( '/llms.txt' !== wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ) ) {
		return;
	}
	status_header( 200 ); // WordPress has already decided this URL is a 404.
	header( 'Content-Type: text/plain; charset=utf-8' );
	header( 'Cache-Control: public, max-age=86400' );
	$lines   = [ '# ' . get_bloginfo( 'name' ), '', '> ' . ( vs_opt( 'hero_intro' ) ?: get_bloginfo( 'description' ) ), '', 'Broadcast and production studio hire in London, Dublin, Paris and Istanbul. Booking: ' . vs_opt( 'email' ) . '.', '', '## Cities', '' ];
	foreach ( vs_cities() as $c ) {
		$lines[] = '- [' . $c->name . ' studios](' . get_term_link( $c ) . '): ' . count( vs_city_studios( $c ) ) . ' studios, ' . vs_term_meta( $c->term_id, 'address' );
	}
	$lines[] = ''; $lines[] = '## Studios'; $lines[] = '';
	foreach ( get_posts( [ 'post_type' => 'studio', 'posts_per_page' => -1, 'orderby' => [ 'menu_order' => 'ASC', 'title' => 'ASC' ] ] ) as $s ) {
		$area = vs_meta( $s->ID, 'area' );
		$lines[] = '- [' . $s->post_title . '](' . get_permalink( $s ) . '): ' . ( $area ? $area . ' sq. mt., ' : '' ) . implode( '; ', array_slice( vs_lines( vs_meta( $s->ID, 'highlights' ) ), 0, 3 ) );
	}
	$lines[] = ''; $lines[] = '## Pages'; $lines[] = '';
	foreach ( [ 'about' => 'About Vision Studios', 'contact' => 'Contact and booking', 'gallery' => 'Photo gallery' ] as $slug => $label ) {
		$lines[] = '- [' . $label . '](' . vs_page_url( $slug ) . ')';
	}
	$lines[] = '- [News](' . vs_news_url() . ')';
	echo implode( "\n", $lines ), "\n";
	exit;
} );
