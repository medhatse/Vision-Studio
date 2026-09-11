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
// the booking form. The scripts are enqueued while the form renders, so intercept them just before the
// footer scripts print, drop them from the queue, and inject them when the form scrolls near the viewport
// or the visitor interacts with the page. Supports both CF7's built-in v3 integration and the
// "ReCaptcha v2 for Contact Form 7" plugin.
add_action( 'wp_print_footer_scripts', function () {
	if ( is_admin() ) {
		return;
	}
	$scripts = wp_scripts();
	if ( empty( $scripts->registered['google-recaptcha'] ) || ! in_array( 'google-recaptcha', $scripts->queue, true ) ) {
		return;
	}
	$v2   = ! empty( $scripts->registered['wpcf7-recaptcha-controls'] );
	$glue = $v2 ? 'wpcf7-recaptcha-controls' : 'wpcf7-recaptcha';
	$api  = $scripts->registered['google-recaptcha']->src;
	$glue_src = ! empty( $scripts->registered[ $glue ] ) ? $scripts->registered[ $glue ]->src : '';
	$inline   = '';
	foreach ( [ 'google-recaptcha', $glue ] as $h ) {
		foreach ( [ 'data', 'before' ] as $k ) {
			$d = $scripts->get_data( $h, $k );
			if ( is_array( $d ) ) {
				$d = implode( "\n", array_filter( $d, 'is_string' ) );
			}
			if ( is_string( $d ) && '' !== trim( $d ) ) {
				$inline .= $d . "\n";
			}
		}
	}
	wp_dequeue_script( 'google-recaptcha' );
	wp_dequeue_script( $glue );
	// v2 plugin: its controls script defines the api.js onload callback, so it must come first.
	$order = $v2 ? [ $glue_src, $api ] : [ $api, $glue_src ];
	?>
<script>
(function(){var done=false,srcs=<?php echo wp_json_encode( array_values( array_filter( $order ) ) ); ?>;
function add(i){if(i>=srcs.length)return;var s=document.createElement('script');s.src=srcs[i];s.async=true;s.onload=function(){add(i+1);};document.head.appendChild(s);}
function load(){if(done)return;done=true;try{<?php echo $inline; // phpcs:ignore ?>}catch(e){}add(0);}
var forms=document.querySelectorAll('.wpcf7');if(!forms.length)return;
if('IntersectionObserver' in window){var io=new IntersectionObserver(function(es){es.forEach(function(e){if(e.isIntersecting){load();io.disconnect();}});},{rootMargin:'600px'});forms.forEach(function(f){io.observe(f);});}else{load();}
['pointerdown','keydown','touchstart'].forEach(function(ev){window.addEventListener(ev,load,{once:true,passive:true});});
})();
</script>
	<?php
}, 1 );
