<?php
/**
 * Template Name: About
 * Page content (headings + paragraphs) is rendered in the theme's prose style, followed by the counters.
 */
get_header();
the_post();
$sub = get_the_excerpt();
echo vs_page_hero( __( 'Who Is<br/><span class="text-accent">Vision Studios.</span>', 'vision-studios' ), $sub, get_post_thumbnail_id(), __( 'About Us', 'vision-studios' ) ); // phpcs:ignore
?>
<section class="bg-black py-20"><div class="max-w-7xl mx-auto px-6 lg:px-10 grid lg:grid-cols-3 gap-14">
<div class="lg:col-span-2 prose-vs"><?php the_content(); ?></div>
<div class="space-y-10">
<div class="fade-up grid grid-cols-1 gap-8 border border-white/10 p-6">
<?php for ( $i = 1; $i <= 3; $i++ ) : if ( '' === vs_opt( "counter{$i}_value" ) ) { continue; } ?><div><div class="font-display text-4xl sm:text-5xl"><span data-count="<?php echo esc_attr( vs_opt( "counter{$i}_value" ) ); ?>">0</span><?php echo esc_html( vs_opt( "counter{$i}_suffix" ) ); ?></div><div class="font-mono-tag text-xs lg:text-[11px] uppercase tracking-[0.15em] text-white/50 mt-1"><?php echo esc_html( vs_opt( "counter{$i}_label" ) ); ?></div></div><?php endfor; ?>
</div>
<?php $reasons = vs_services( 'reason' ); if ( $reasons ) : ?><div class="fade-up"><?php echo vs_eyebrow( __( 'Why Vision', 'vision-studios' ) ); // phpcs:ignore ?><ul class="divide-y divide-white/10"><?php foreach ( $reasons as $r ) : ?><li class="py-3"><h3 class="font-display uppercase text-lg"><?php echo esc_html( $r->post_title ); ?></h3><p class="text-white/60 text-sm mt-1"><?php echo esc_html( $r->post_excerpt ?: wp_strip_all_tags( $r->post_content ) ); ?></p></li><?php endforeach; ?></ul></div><?php endif; ?>
</div></div></section>
<?php echo vs_cta_band(); // phpcs:ignore ?>
<?php get_footer();
