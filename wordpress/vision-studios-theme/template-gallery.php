<?php
/**
 * Template Name: Gallery
 * Every studio photo, filterable by city.
 */
get_header();
the_post();
$studios = get_posts( [ 'post_type' => 'studio', 'posts_per_page' => -1, 'orderby' => [ 'menu_order' => 'ASC', 'title' => 'ASC' ] ] );
$items   = [];
foreach ( $studios as $s ) {
	$city = vs_studio_city( $s->ID );
	foreach ( vs_studio_gallery_ids( $s->ID ) as $i => $aid ) {
		$items[] = [ $aid, $s, $city ? $city->slug : '', $i ];
	}
}
$cities = vs_cities();
$hero   = get_post_thumbnail_id() ?: ( $items ? (int) $items[0][0] : 0 );
echo vs_page_hero( __( 'From Behind<br/><span class="text-accent">The Lens.</span>', 'vision-studios' ), sprintf( __( '%1$d photographs from %2$d studios across four cities.', 'vision-studios' ), count( $items ), count( $studios ) ), $hero, __( 'Gallery', 'vision-studios' ) ); // phpcs:ignore
?>
<section class="bg-black py-16"><div class="max-w-7xl mx-auto px-6 lg:px-10">
<?php if ( get_the_content() ) : ?><div class="fade-up prose-vs max-w-3xl mb-10"><?php the_content(); ?></div><?php endif; ?>
<div class="fade-up flex flex-wrap gap-2 mb-10" id="gallery-filters"><button type="button" data-filter="all" class="gallery-filter font-mono-tag text-xs lg:text-[10px] uppercase tracking-[0.2em] px-4 py-2 border border-accent text-accent"><?php esc_html_e( 'All cities', 'vision-studios' ); ?></button><?php foreach ( $cities as $c ) : ?><button type="button" data-filter="<?php echo esc_attr( $c->slug ); ?>" class="gallery-filter font-mono-tag text-xs lg:text-[10px] uppercase tracking-[0.2em] px-4 py-2 border border-white/15 text-white/60"><?php echo esc_html( $c->name ); ?></button><?php endforeach; ?></div>
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3" id="gallery-grid">
<?php foreach ( $items as [ $aid, $s, $slug, $i ] ) : ?><button type="button" data-city="<?php echo esc_attr( $slug ); ?>" data-lightbox="all" data-src="<?php echo esc_url( vs_img_url( $aid ) ); ?>" data-caption="<?php echo esc_attr( $s->post_title ); ?>" class="gallery-item fade-up img-zoom relative aspect-[4/3] overflow-hidden bg-white/5 group"><?php echo wp_get_attachment_image( $aid, 'vs-thumb', false, [ 'class' => 'absolute inset-0 w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500', 'loading' => 'lazy', 'alt' => sprintf( __( '%1$s — photo %2$d', 'vision-studios' ), $s->post_title, $i + 1 ) ] ); ?><span class="absolute bottom-2 left-3 font-mono-tag text-xs lg:text-[10px] uppercase tracking-[0.15em] text-white/70 opacity-0 group-hover:opacity-100 transition-opacity"><?php echo esc_html( $s->post_title ); ?></span></button><?php endforeach; ?>
</div></div></section>
<?php echo vs_cta_band(); // phpcs:ignore ?>
<?php get_footer();
