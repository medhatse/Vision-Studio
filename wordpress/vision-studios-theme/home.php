<?php
/** News index (posts page). */
get_header();
$first = have_posts() ? get_posts( [ 'posts_per_page' => 1 ] )[0] ?? null : null;
$total = (int) wp_count_posts()->publish;
echo vs_page_hero( esc_html__( 'Our News.', 'vision-studios' ), __( 'Productions, live events and behind-the-scenes stories from our studios in London, Dublin, Paris and Istanbul.', 'vision-studios' ), $first ? (string) get_the_post_thumbnail_url( $first, 'vs-wide' ) : '', sprintf( _n( '%d story', '%d stories', $total, 'vision-studios' ), $total ) ); // phpcs:ignore
?>
<section class="bg-black py-20"><div class="max-w-7xl mx-auto px-6 lg:px-10">
<?php if ( $first && ! is_paged() ) : ?>
<a href="<?php echo esc_url( get_permalink( $first ) ); ?>" class="group fade-up grid lg:grid-cols-2 gap-10 items-center mb-20"><div class="img-zoom relative aspect-[16/10] overflow-hidden bg-white/5"><?php echo get_the_post_thumbnail( $first, 'vs-wide', [ 'alt' => $first->post_title, 'class' => 'absolute inset-0 w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500' ] ); ?></div><div><?php echo vs_eyebrow( __( 'Latest', 'vision-studios' ) . ' · ' . get_the_date( '', $first ) ); // phpcs:ignore ?><h2 class="font-display uppercase text-[clamp(1.8rem,4vw,3rem)] leading-[0.95] group-hover:text-accent transition-colors"><?php echo esc_html( $first->post_title ); ?></h2><p class="text-white/60 mt-5 max-w-lg"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt( $first ) ) ); ?></p></div></a>
<?php endif; ?>
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-14">
<?php while ( have_posts() ) : the_post(); if ( $first && get_the_ID() === $first->ID && ! is_paged() ) { continue; } echo vs_news_card( get_post() ); endwhile; // phpcs:ignore ?>
</div>
<div class="mt-16 flex justify-between font-mono-tag text-xs uppercase tracking-[0.15em] text-white/60"><?php previous_posts_link( '← ' . __( 'Newer', 'vision-studios' ) ); next_posts_link( __( 'Older', 'vision-studios' ) . ' →' ); ?></div>
</div></section>
<?php echo vs_cta_band(); // phpcs:ignore ?>
<?php get_footer();
