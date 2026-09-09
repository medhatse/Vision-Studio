<?php
/** Single news post. */
get_header();
the_post();
echo vs_page_hero( esc_html( get_the_title() ), '', get_post_thumbnail_id(), get_the_date() ); // phpcs:ignore
$prev = get_previous_post();
$next = get_next_post();
?>
<article class="bg-black py-16"><div class="max-w-3xl mx-auto px-6 lg:px-10">
<div class="prose-vs"><?php the_content(); ?></div>
<div class="mt-16 pt-8 border-t border-white/10 flex justify-between gap-6 font-mono-tag text-xs uppercase tracking-[0.15em]">
<?php if ( $prev ) : ?><a href="<?php echo esc_url( get_permalink( $prev ) ); ?>" class="nav-link text-white/60 hover:text-accent">← <?php echo esc_html( wp_trim_words( $prev->post_title, 6 ) ); ?></a><?php else : ?><span></span><?php endif; ?>
<?php if ( $next ) : ?><a href="<?php echo esc_url( get_permalink( $next ) ); ?>" class="nav-link text-white/60 hover:text-accent text-right"><?php echo esc_html( wp_trim_words( $next->post_title, 6 ) ); ?> →</a><?php endif; ?>
</div>
<p class="mt-8"><a href="<?php echo esc_url( vs_news_url() ); ?>" class="font-mono-tag text-xs uppercase tracking-[0.15em] text-accent"><?php esc_html_e( 'All news', 'vision-studios' ); ?></a></p>
</div></article>
<?php echo vs_cta_band(); // phpcs:ignore ?>
<?php get_footer();
