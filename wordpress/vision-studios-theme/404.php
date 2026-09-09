<?php get_header(); ?>
<section class="min-h-[80vh] flex items-center"><div class="max-w-7xl mx-auto px-6 lg:px-10 pt-40 pb-20"><?php echo vs_eyebrow( '404' ); // phpcs:ignore ?><h1 class="font-display uppercase text-[clamp(2.4rem,8vw,5.5rem)] leading-[0.95]"><?php esc_html_e( 'Off Air.', 'vision-studios' ); ?><br/><span class="text-white/40"><?php esc_html_e( 'Page Not Found.', 'vision-studios' ); ?></span></h1><div class="mt-8"><?php echo vs_btn( home_url( '/' ), __( 'Back to the studios', 'vision-studios' ), 'white' ); // phpcs:ignore ?></div></div></section>
<?php get_footer();
