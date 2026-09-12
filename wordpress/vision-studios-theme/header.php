<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a href="#content" class="sr-only focus:not-sr-only focus:absolute focus:z-[70] focus:top-4 focus:left-4 focus:bg-accent focus:text-black focus:px-4 focus:py-2 font-mono-tag text-xs uppercase"><?php esc_html_e( 'Skip to content', 'vision-studios' ); ?></a>
<?php
$vs_nav = [
	[ get_post_type_archive_link( 'studio' ), __( 'Studios', 'vision-studios' ) ],
	[ home_url( '/#services' ), __( 'Services', 'vision-studios' ) ],
	[ vs_page_url( 'gallery' ), __( 'Gallery', 'vision-studios' ) ],
	[ vs_news_url(), __( 'News', 'vision-studios' ) ],
	[ vs_page_url( 'about' ), __( 'About', 'vision-studios' ) ],
	[ vs_page_url( 'contact' ), __( 'Contact', 'vision-studios' ) ],
];
$vs_menu_args = [ 'theme_location' => 'primary', 'container' => false, 'echo' => false, 'fallback_cb' => '__return_empty_string', 'items_wrap' => '%3$s', 'depth' => 1 ];
$vs_menu = has_nav_menu( 'primary' ) ? wp_nav_menu( $vs_menu_args ) : '';
$vs_menu = $vs_menu ? preg_replace( '/<a /', '<a class="nav-link" ', $vs_menu ) : implode( '', array_map( fn( $l ) => '<li><a href="' . esc_url( $l[0] ) . '" class="nav-link">' . esc_html( $l[1] ) . '</a></li>', $vs_nav ) );
$vs_mobile = has_nav_menu( 'primary' ) ? wp_nav_menu( $vs_menu_args ) : implode( '', array_map( fn( $l ) => '<li><a href="' . esc_url( $l[0] ) . '" class="block py-1">' . esc_html( $l[1] ) . '</a></li>', $vs_nav ) );
$vs_logo = VS_URI . '/assets/img/logo-mark.png';
?>
<header id="site-header" class="fixed top-0 inset-x-0 z-50 transition-colors duration-300">
<nav class="max-w-7xl mx-auto px-6 lg:px-10 h-20 flex items-center justify-between" aria-label="<?php esc_attr_e( 'Primary', 'vision-studios' ); ?>">
<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="flex items-center gap-3 shrink-0" aria-label="<?php esc_attr_e( 'Vision Studios home', 'vision-studios' ); ?>"><img src="<?php echo esc_url( $vs_logo ); ?>" alt="<?php esc_attr_e( 'Vision Studios logo', 'vision-studios' ); ?>" width="430" height="455" class="h-8 w-auto"/><span class="font-display uppercase leading-none text-lg tracking-wide">Vision<span class="block text-xs lg:text-[10px] font-mono-tag tracking-[0.2em] text-white/50 -mt-0.5">Studios</span></span></a>
<ul class="hidden lg:flex items-center gap-10 font-mono-tag text-xs uppercase tracking-[0.15em] text-white/80"><?php echo $vs_menu; // phpcs:ignore ?></ul>
<div class="hidden lg:block"><a href="<?php echo esc_url( vs_page_url( 'contact' ) ); ?>" class="btn-primary inline-flex items-center gap-2 bg-accent text-black font-mono-tag text-xs uppercase tracking-[0.15em] px-5 py-3 rounded-sm"><?php esc_html_e( 'Book a Studio', 'vision-studios' ); ?><?php echo vs_icon( 'arrow-right', 'text-xs lg:text-[10px]' ); ?></a></div>
<button id="menu-toggle" class="lg:hidden text-white text-2xl" aria-label="<?php esc_attr_e( 'Toggle navigation menu', 'vision-studios' ); ?>" aria-expanded="false" aria-controls="mobile-menu"><span class="menu-icon-open"><?php echo vs_icon( 'bars' ); ?></span><span class="menu-icon-close" hidden><?php echo vs_icon( 'xmark' ); ?></span></button>
</nav>
<div id="mobile-menu" class="lg:hidden max-h-0 overflow-hidden bg-black/95 border-t border-white/10"><ul class="flex flex-col px-6 py-4 gap-4 font-mono-tag text-sm uppercase tracking-[0.15em] text-white/85"><?php echo $vs_mobile; // phpcs:ignore ?><li><a href="<?php echo esc_url( vs_page_url( 'contact' ) ); ?>" class="inline-flex items-center gap-2 bg-accent text-black px-4 py-3 rounded-sm mt-2"><?php esc_html_e( 'Book a Studio', 'vision-studios' ); ?> <?php echo vs_icon( 'arrow-right', 'text-xs lg:text-[10px]' ); ?></a></li></ul></div>
</header>
<main id="content"><!--email_off-->
