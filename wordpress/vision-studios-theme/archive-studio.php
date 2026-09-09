<?php
/** All studios. */
get_header();
$studios = get_posts( [ 'post_type' => 'studio', 'posts_per_page' => -1, 'orderby' => [ 'menu_order' => 'ASC', 'title' => 'ASC' ] ] );
echo vs_page_hero( esc_html__( 'All Studios.', 'vision-studios' ), sprintf( __( '%d studios across London, Dublin, Paris and Istanbul.', 'vision-studios' ), count( $studios ) ), vs_city_image( vs_cities()[0] ?? null, 'vs-wide' ), __( 'Our Studios', 'vision-studios' ) ); // phpcs:ignore
?>
<section class="bg-black py-16"><div class="max-w-7xl mx-auto px-6 lg:px-10">
<?php foreach ( vs_cities() as $c ) : $cs = vs_city_studios( $c ); if ( ! $cs ) { continue; } ?>
<div class="fade-up flex items-end justify-between mt-12 mb-6 flex-wrap gap-4"><h2 class="font-display uppercase text-3xl"><?php echo esc_html( $c->name ); ?></h2><a href="<?php echo esc_url( get_term_link( $c ) ); ?>" class="nav-link font-mono-tag text-xs uppercase tracking-[0.15em] text-white/70"><?php esc_html_e( 'City page', 'vision-studios' ); ?> <?php echo VS_ARROW; // phpcs:ignore ?></a></div>
<div class="grid grid-cols-2 md:grid-cols-4 gap-4"><?php foreach ( $cs as $s ) { echo vs_studio_card( $s ); } // phpcs:ignore ?></div>
<?php endforeach; ?>
</div></section>
<?php echo vs_cta_band(); // phpcs:ignore ?>
<?php get_footer();
