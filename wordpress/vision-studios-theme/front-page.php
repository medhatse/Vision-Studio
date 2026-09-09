<?php
/**
 * Home page.
 */
get_header();
$hero_id = (int) vs_opt( 'hero_image' );
$cities  = vs_cities();
if ( ! $hero_id && $cities ) {
	$hero_id = vs_city_image_id( $cities[1] ?? $cities[0] );
}
$studio_count = (int) wp_count_posts( 'studio' )->publish;
?>
<section id="top" class="relative min-h-screen flex flex-col justify-end overflow-hidden">
<?php echo vs_hero_img( $hero_id, __( 'Broadcast studio set at Vision Studios', 'vision-studios' ) ); // phpcs:ignore ?>
<div class="absolute inset-0 bg-gradient-to-t from-black via-black/70 to-black/30"></div><div class="grain-overlay"></div>
<div class="relative z-10 max-w-7xl w-full mx-auto px-6 lg:px-10 pt-40 pb-16">
<div class="flex items-center justify-between font-mono-tag text-xs lg:text-[11px] uppercase tracking-[0.2em] text-white/60 mb-6"><span><?php echo esc_html( vs_opt( 'hero_left' ) ); ?></span><span class="hidden sm:inline text-accent"><?php echo esc_html( vs_opt( 'hero_center' ) ); ?></span><span class="hidden sm:inline"><?php echo esc_html( vs_opt( 'hero_right' ) ); ?></span></div>
<h1 class="font-display uppercase leading-[0.95] text-[clamp(2.4rem,8vw,5.5rem)]"><?php esc_html_e( 'Broadcast &', 'vision-studios' ); ?><br/><?php esc_html_e( 'Production Studios', 'vision-studios' ); ?><br/><span class="text-white/55">London · Dublin ·</span> Paris ·<br class="hidden sm:block"/><span class="text-white/55">Istanbul</span></h1>
<p class="mt-6 max-w-xl text-white/70 text-base sm:text-lg"><?php echo esc_html( vs_opt( 'hero_intro' ) ); ?></p>
<div class="mt-8 flex flex-wrap gap-4"><?php echo vs_btn( '#studios', __( 'View Studios', 'vision-studios' ), 'white' ) . vs_btn( vs_page_url( 'contact' ), __( 'Book a Studio', 'vision-studios' ) ); // phpcs:ignore ?></div>
</div></section>

<section class="bg-black border-t border-white/10"><div class="max-w-7xl mx-auto px-6 lg:px-10 py-14 grid grid-cols-2 sm:grid-cols-4 gap-8">
<?php for ( $i = 1; $i <= 4; $i++ ) : ?><div class="fade-up"><div class="font-display text-4xl sm:text-5xl"><?php echo esc_html( vs_opt( "stat{$i}_value" ) ); ?></div><div class="font-mono-tag text-xs lg:text-[11px] uppercase tracking-[0.15em] text-white/50 mt-1"><?php echo esc_html( vs_opt( "stat{$i}_label" ) ); ?></div></div><?php endfor; ?>
</div></section>

<section id="studios" class="bg-black py-24 lg:py-32"><div class="max-w-7xl mx-auto px-6 lg:px-10">
<div class="fade-up mb-14 flex items-end justify-between flex-wrap gap-4"><div><?php echo vs_eyebrow( __( 'Our Studios', 'vision-studios' ) ) . vs_h2( __( 'Four Cities. One Production Standard.', 'vision-studios' ), 'max-w-3xl' ); // phpcs:ignore ?></div><p class="font-mono-tag text-xs uppercase tracking-[0.15em] text-white/55"><?php echo esc_html( sprintf( __( '%1$d studios · %2$d cities', 'vision-studios' ), $studio_count, count( $cities ) ) ); ?></p></div>
<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
<?php foreach ( $cities as $c ) :
	$cs = vs_city_studios( $c );
	$max = max( array_map( fn( $s ) => (float) vs_meta( $s->ID, 'area', 0 ), $cs ) ?: [ 0 ] );
	?>
<a href="<?php echo esc_url( get_term_link( $c ) ); ?>" class="group fade-up img-zoom relative aspect-[3/4] block overflow-hidden bg-white/5">
<img src="<?php echo esc_url( vs_city_image( $c ) ); ?>" alt="<?php echo esc_attr( sprintf( __( 'Vision Studios %1$s, %2$s', 'vision-studios' ), $c->name, vs_term_meta( $c->term_id, 'country' ) ) ); ?>" loading="lazy" class="absolute inset-0 w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500"/>
<div class="absolute inset-0 bg-gradient-to-t from-black via-black/20 to-transparent"></div>
<div class="absolute bottom-0 left-0 p-4"><p class="font-mono-tag text-xs lg:text-[10px] uppercase tracking-[0.15em] text-accent mb-1"><?php echo esc_html( vs_term_meta( $c->term_id, 'tagline', vs_term_meta( $c->term_id, 'country' ) ) ); ?></p><h3 class="font-display uppercase text-2xl leading-none"><?php echo esc_html( $c->name ); ?></h3><p class="font-mono-tag text-xs lg:text-[10px] text-white/50 mt-1"><?php echo esc_html( sprintf( _n( '%1$d studio · up to %2$s sq. mt.', '%1$d studios · up to %2$s sq. mt.', count( $cs ), 'vision-studios' ), count( $cs ), $max ) ); ?></p></div></a>
<?php endforeach; ?>
</div></div></section>

<?php $services = vs_services( 'service' ); if ( $services ) : ?>
<section id="services" class="bg-black py-24 lg:py-32 border-t border-white/10"><div class="max-w-7xl mx-auto px-6 lg:px-10">
<div class="fade-up mb-14"><?php echo vs_eyebrow( __( 'Services', 'vision-studios' ) ) . vs_h2( __( 'End-to-End Production, Under One Roof.', 'vision-studios' ), 'max-w-3xl' ); // phpcs:ignore ?></div>
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-px bg-white/10">
<?php foreach ( $services as $i => $s ) : ?>
<div class="fade-up service-card bg-black p-8 flex flex-col gap-4"><?php echo vs_icon( str_replace( 'fa-', '', vs_meta( $s->ID, 'icon', 'circle' ) ), 'text-2xl text-accent' ); ?><h3 class="font-display uppercase text-lg"><?php echo esc_html( $s->post_title ); ?></h3><p class="text-white/60 text-sm leading-relaxed"><?php echo esc_html( $s->post_excerpt ?: wp_trim_words( wp_strip_all_tags( $s->post_content ), 24 ) ); ?></p><p class="font-mono-tag text-xs lg:text-[10px] text-white/50 mt-auto pt-2"><?php echo esc_html( sprintf( '%02d / %02d', $i + 1, count( $services ) ) ); ?></p></div>
<?php endforeach; ?>
</div></div></section>
<?php endif; ?>

<?php
// Gallery: first photo of the six largest studios.
$gallery_studios = get_posts( [ 'post_type' => 'studio', 'posts_per_page' => 6, 'meta_key' => '_vs_area', 'orderby' => 'meta_value_num', 'order' => 'DESC' ] );
$spans = [ 1, 1, 2, 2, 1, 1 ];
if ( $gallery_studios ) : ?>
<section id="gallery" class="bg-black py-24 lg:py-32 border-t border-white/10"><div class="max-w-7xl mx-auto px-6 lg:px-10">
<div class="fade-up flex items-end justify-between mb-10 flex-wrap gap-4"><div><?php echo vs_eyebrow( __( 'Gallery', 'vision-studios' ) ) . vs_h2( __( 'From Behind The Lens.', 'vision-studios' ) ); // phpcs:ignore ?></div><?php echo vs_btn( vs_page_url( 'gallery' ), __( 'View All', 'vision-studios' ), 'ghost' ); // phpcs:ignore ?></div>
<div class="grid md:grid-cols-4 gap-3">
<?php foreach ( $gallery_studios as $i => $s ) : $ids = vs_studio_gallery_ids( $s->ID ); $src = vs_img_url( $ids[ $i % 2 ] ?? $ids[0] ?? 0, 'vs-wide' ); if ( ! $src ) { continue; } ?>
<button type="button" data-lightbox="home" data-src="<?php echo esc_url( $src ); ?>" data-caption="<?php echo esc_attr( $s->post_title ); ?>" class="fade-up img-zoom relative aspect-[4/3] overflow-hidden bg-white/5 md:col-span-<?php echo (int) $spans[ $i ]; ?> text-left"><img src="<?php echo esc_url( $src ); ?>" alt="<?php echo esc_attr( sprintf( __( '%s broadcast set', 'vision-studios' ), $s->post_title ) ); ?>" loading="lazy" class="absolute inset-0 w-full h-full object-cover grayscale"/></button>
<?php endforeach; ?>
</div></div></section>
<?php endif; ?>

<?php $reasons = vs_services( 'reason' ); if ( $reasons ) : ?>
<section class="bg-black py-24 lg:py-32 border-t border-white/10"><div class="max-w-7xl mx-auto px-6 lg:px-10 grid lg:grid-cols-2 gap-14">
<div class="fade-up"><?php echo vs_eyebrow( __( 'Why Vision', 'vision-studios' ) ) . vs_h2( __( 'The Reasons Directors Keep Coming Back.', 'vision-studios' ) ); // phpcs:ignore ?><p class="mt-6 text-white/60 max-w-md"><?php echo esc_html( vs_opt( 'why_intro' ) ); ?></p><div class="mt-8"><?php echo vs_btn( vs_page_url( 'about' ), __( 'More About Us', 'vision-studios' ), 'ghost' ); // phpcs:ignore ?></div></div>
<div class="fade-up divide-y divide-white/10">
<?php foreach ( $reasons as $i => $r ) : ?><div class="flex gap-6 py-5"><span class="font-mono-tag text-accent text-sm pt-1"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span><div><h3 class="font-display uppercase text-lg"><?php echo esc_html( $r->post_title ); ?></h3><p class="text-white/60 text-sm mt-1"><?php echo esc_html( $r->post_excerpt ?: wp_strip_all_tags( $r->post_content ) ); ?></p></div></div><?php endforeach; ?>
</div></div></section>
<?php endif; ?>

<?php $choose = vs_opt( 'choose_intro' ); if ( $choose ) : ?>
<section class="bg-black py-24 lg:py-32 border-t border-white/10"><div class="max-w-7xl mx-auto px-6 lg:px-10">
<div class="fade-up mb-12 max-w-3xl"><?php echo vs_eyebrow( __( 'Why Choose Vision Studios?', 'vision-studios' ) ); // phpcs:ignore ?><p class="text-white/60"><?php echo esc_html( $choose ); ?></p></div>
<div class="grid grid-cols-3 gap-8 pt-10 border-t border-white/10">
<?php for ( $i = 1; $i <= 3; $i++ ) : if ( '' === vs_opt( "counter{$i}_value" ) ) { continue; } ?><div class="fade-up"><div class="font-display text-4xl sm:text-5xl"><span data-count="<?php echo esc_attr( vs_opt( "counter{$i}_value" ) ); ?>">0</span><?php echo esc_html( vs_opt( "counter{$i}_suffix" ) ); ?></div><div class="font-mono-tag text-xs lg:text-[11px] uppercase tracking-[0.15em] text-white/50 mt-1"><?php echo esc_html( vs_opt( "counter{$i}_label" ) ); ?></div></div><?php endfor; ?>
</div></div></section>
<?php endif; ?>

<?php $clients = get_posts( [ 'post_type' => 'vs_client', 'posts_per_page' => -1, 'orderby' => [ 'menu_order' => 'ASC', 'title' => 'ASC' ] ] ); if ( $clients ) : ?>
<section id="trusted-by" class="bg-black py-20 border-t border-white/10"><div class="max-w-7xl mx-auto px-6 lg:px-10">
<div class="fade-up flex items-center justify-between mb-8 flex-wrap gap-2"><p class="font-mono-tag text-xs uppercase tracking-[0.25em] text-white/50"><?php esc_html_e( 'Trusted By Broadcasters & Brands', 'vision-studios' ); ?></p><p class="font-mono-tag text-xs uppercase tracking-[0.25em] text-white/50"><?php esc_html_e( 'Some of Our Clients', 'vision-studios' ); ?></p></div>
<div class="fade-up grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 border border-white/10">
<?php foreach ( $clients as $c ) : ?><div class="client-logo flex items-center justify-center h-28 p-6 border-white/10 [&:not(:nth-child(5n))]:border-r [&:not(:nth-last-child(-n+5))]:border-b sm:[&:not(:nth-child(3n))]:border-r"><?php echo get_the_post_thumbnail( $c, 'medium', [ 'class' => 'max-h-14 max-w-full object-contain opacity-70 grayscale transition-all duration-300', 'title' => $c->post_title, 'alt' => sprintf( __( '%s logo', 'vision-studios' ), $c->post_title ), 'loading' => 'lazy' ] ); ?></div><?php endforeach; ?>
</div></div></section>
<?php endif; ?>

<?php $latest = get_posts( [ 'posts_per_page' => 3 ] ); if ( $latest ) : ?>
<section id="news" class="bg-black py-24 lg:py-32 border-t border-white/10"><div class="max-w-7xl mx-auto px-6 lg:px-10">
<div class="fade-up flex items-end justify-between mb-10 flex-wrap gap-4"><div><?php echo vs_eyebrow( __( 'Our News', 'vision-studios' ) ) . vs_h2( __( 'Recently On Set.', 'vision-studios' ) ); // phpcs:ignore ?></div><?php echo vs_btn( vs_news_url(), __( 'Explore Our News', 'vision-studios' ), 'ghost' ); // phpcs:ignore ?></div>
<div class="grid md:grid-cols-3 gap-8"><?php foreach ( $latest as $p ) { echo vs_news_card( $p ); } // phpcs:ignore ?></div>
</div></section>
<?php endif; ?>

<?php echo vs_cta_band(); // phpcs:ignore ?>
<?php get_footer();
