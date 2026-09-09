<?php
/** Default page. */
get_header();
the_post();
echo vs_page_hero( esc_html( get_the_title() ), '', (string) get_the_post_thumbnail_url( null, 'vs-wide' ), get_bloginfo( 'name' ) ); // phpcs:ignore
?>
<article class="bg-black py-16"><div class="max-w-3xl mx-auto px-6 lg:px-10 prose-vs"><?php the_content(); ?></div></article>
<?php echo vs_cta_band(); // phpcs:ignore ?>
<?php get_footer();
