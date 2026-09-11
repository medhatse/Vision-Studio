<?php
/**
 * Single studio.
 */
get_header();
the_post();
$id      = get_the_ID();
$city    = vs_studio_city( $id );
$cid     = $city ? $city->term_id : 0;
$tag     = vs_meta( $id, 'tagline' );
$area    = vs_meta( $id, 'area' );
$phone   = vs_meta( $id, 'phone', vs_term_meta( $cid, 'phone', vs_opt( 'phone_uk' ) ) );
$address = vs_meta( $id, 'address', vs_term_meta( $cid, 'address' ) );
$map     = vs_meta( $id, 'map_query', $address ?: vs_term_meta( $cid, 'map_query' ) );
$coords  = vs_term_meta( $cid, 'coords' );
$specs   = vs_lines( vs_meta( $id, 'specs' ) );
$highs   = vs_lines( vs_meta( $id, 'highlights' ) );
$gallery = vs_studio_gallery_ids( $id );
$plan    = vs_img_url( vs_meta( $id, 'floor_plan' ), 'large' );
$price   = vs_meta( $id, 'price_from' );
$faq     = vs_faq_items( vs_meta( $id, 'faq' ) );
$uses    = array_map( fn( $l ) => array_map( 'trim', explode( '|', $l, 2 ) ), vs_lines( vs_meta( $id, 'use_cases' ) ) );
$email   = vs_opt( 'email' );
$icons   = [ '/sq\.? ?mt|area/i' => 'fa-vector-square', '/decorat|isolat|stage|customi/i' => 'fa-couch', '/video wall|LED screen|screen/i' => 'fa-tv', '/gallery|control room/i' => 'fa-sliders', '/camera/i' => 'fa-video', '/generator|UPS|electric/i' => 'fa-bolt', '/mixer|audio|microphone/i' => 'fa-microphone-lines', '/jib|jip|tripod/i' => 'fa-arrows-up-down-left-right', '/light/i' => 'fa-lightbulb' ];
$icon_for = function ( string $t ) use ( $icons ) { foreach ( $icons as $re => $ic ) { if ( preg_match( $re, $t ) ) { return $ic; } } return 'fa-circle-check'; };
?>
<section class="relative min-h-[80vh] flex flex-col justify-end overflow-hidden" id="hero-slider">
<?php foreach ( $gallery as $i => $aid ) : ?><div class="hero-slide absolute inset-0 transition-opacity duration-1000 <?php echo $i ? 'opacity-0' : 'opacity-100'; ?>" data-slide="<?php echo (int) $i; ?>"<?php echo $i ? ' data-src="' . esc_url( vs_img_url( $aid, 'vs-wide' ) ) . '" data-alt="' . esc_attr( sprintf( __( '%1$s at Vision Studios %2$s — photo %3$d', 'vision-studios' ), get_the_title(), $city ? $city->name : '', $i + 1 ) ) . '"' : ''; ?>><?php if ( ! $i ) { echo wp_get_attachment_image( $aid, 'vs-wide', false, [ 'class' => 'w-full h-full object-cover', 'sizes' => '100vw', 'alt' => sprintf( __( '%1$s at Vision Studios %2$s', 'vision-studios' ), get_the_title(), $city ? $city->name : '' ), 'loading' => 'eager', 'fetchpriority' => 'high', 'decoding' => 'async' ] ); } ?></div><?php endforeach; ?>
<div class="absolute inset-0 bg-gradient-to-t from-black via-black/60 to-black/20 pointer-events-none"></div><div class="grain-overlay"></div>
<div class="relative z-10 max-w-7xl w-full mx-auto px-6 lg:px-10 pt-40 pb-14">
<div class="flex items-center justify-between font-mono-tag text-xs lg:text-[11px] uppercase tracking-[0.2em] text-white/60 mb-6"><?php if ( $city ) : ?><a href="<?php echo esc_url( get_term_link( $city ) ); ?>" class="hover:text-accent">← <?php echo esc_html( sprintf( __( '%s studios', 'vision-studios' ), $city->name ) ); ?></a><?php endif; ?><span class="hidden sm:inline text-accent"><?php echo esc_html( $tag ); ?></span><span class="hidden sm:inline"><?php echo esc_html( $coords ); ?></span></div>
<h1 class="font-display uppercase leading-[0.95] text-[clamp(2.4rem,8vw,5.5rem)]"><?php the_title(); ?></h1>
<p class="mt-5 max-w-xl text-white/70"><?php echo esc_html( ( $area ? sprintf( __( '%s sq. mt. studio area · ', 'vision-studios' ), $area ) : '' ) . ( $city ? $city->name . ( vs_term_meta( $cid, 'country' ) ? ', ' . vs_term_meta( $cid, 'country' ) : '' ) : '' ) ); ?><?php if ( $price ) : ?> <span class="text-accent font-mono-tag text-xs uppercase tracking-[0.15em] ml-2"><?php echo esc_html( sprintf( __( 'From %s', 'vision-studios' ), $price ) ); ?></span><?php endif; ?></p>
<div class="mt-8 flex flex-wrap items-center gap-6"><?php echo vs_btn( '#book', __( 'Book This Studio', 'vision-studios' ) ); // phpcs:ignore ?><div class="flex items-center gap-2"><?php foreach ( $gallery as $i => $aid ) : ?><button type="button" class="hero-dot <?php echo $i ? '' : 'is-active'; ?>" data-goto="<?php echo (int) $i; ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Show image %d', 'vision-studios' ), $i + 1 ) ); ?>"></button><?php endforeach; ?></div></div>
</div></section>
<?php echo vs_breadcrumbs(); // phpcs:ignore ?>

<?php if ( $highs ) : ?>
<section class="bg-black border-t border-white/10"><div class="max-w-7xl mx-auto px-6 lg:px-10 py-10 grid sm:grid-cols-2 lg:grid-cols-3 gap-px bg-white/10 border border-white/10">
<?php foreach ( $highs as $h ) : ?><div class="fade-up flex items-start gap-4 p-6 bg-black"><?php echo vs_icon( str_replace( 'fa-', '', $icon_for( $h ) ), 'text-accent text-xl mt-1 shrink-0' ); ?><p class="text-sm text-white/80 leading-snug"><?php echo esc_html( $h ); ?></p></div><?php endforeach; ?>
</div></section>
<?php endif; ?>

<section class="bg-black py-20"><div class="max-w-7xl mx-auto px-6 lg:px-10 grid lg:grid-cols-5 gap-14">
<div class="lg:col-span-3 fade-up"><?php echo vs_eyebrow( __( 'Description', 'vision-studios' ) ) . vs_h2( __( 'Full Specification.', 'vision-studios' ), 'mb-8' ); // phpcs:ignore ?>
<?php if ( get_the_content() ) : ?><div class="prose-vs mb-10"><?php the_content(); ?></div><?php endif; ?>
<ul><?php foreach ( $specs ?: $highs as $sp ) : ?><li class="flex gap-3 py-2.5 border-b border-white/10 text-sm text-white/75"><span class="text-accent font-mono-tag text-xs pt-0.5">—</span><?php echo esc_html( rtrim( $sp, '.' ) ); ?></li><?php endforeach; ?></ul>
<?php if ( ! $specs ) : ?><p class="text-white/55 text-sm mt-6"><?php esc_html_e( 'Need a longer equipment list for this studio?', 'vision-studios' ); ?> <a href="mailto:<?php echo esc_attr( $email ); ?>" class="text-accent"><?php esc_html_e( 'Email us', 'vision-studios' ); ?></a>.</p><?php endif; ?>
</div>
<div class="lg:col-span-2 space-y-10">
<?php if ( $plan ) : ?><div class="fade-up"><?php echo vs_eyebrow( __( 'Floor Plan', 'vision-studios' ) ); // phpcs:ignore ?><button type="button" data-lightbox="plan" data-src="<?php echo esc_url( $plan ); ?>" data-caption="<?php echo esc_attr( get_the_title() . ' — ' . __( 'floor plan', 'vision-studios' ) ); ?>" class="block w-full bg-white/5 p-4"><img src="<?php echo esc_url( $plan ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?> floor plan" loading="lazy" class="w-full h-auto"/></button></div><?php endif; ?>
<div class="fade-up border border-white/10 p-6"><?php echo vs_eyebrow( __( 'Contact', 'vision-studios' ) ); // phpcs:ignore ?><p class="font-display uppercase text-2xl"><a href="<?php echo esc_attr( vs_tel( $phone ) ); ?>" class="hover:text-accent"><?php echo esc_html( $phone ); ?></a></p><p class="text-white/60 text-sm mt-2"><a href="mailto:<?php echo esc_attr( $email ); ?>" class="hover:text-accent"><?php echo esc_html( $email ); ?></a></p><p class="text-white/60 text-sm mt-3 leading-relaxed"><?php echo esc_html( $address ); ?></p></div>
</div></div></section>

<?php if ( count( $gallery ) > 1 ) : ?>
<section class="bg-black py-20 border-t border-white/10"><div class="max-w-7xl mx-auto px-6 lg:px-10">
<div class="fade-up flex items-end justify-between mb-10 flex-wrap gap-4"><div><?php echo vs_eyebrow( __( 'Gallery', 'vision-studios' ) ) . vs_h2( esc_html( get_the_title() ) . ' ' . __( 'In Pictures.', 'vision-studios' ) ); // phpcs:ignore ?></div><p class="font-mono-tag text-xs uppercase tracking-[0.15em] text-white/55"><?php echo esc_html( sprintf( _n( '%d photo', '%d photos', count( $gallery ), 'vision-studios' ), count( $gallery ) ) ); ?></p></div>
<div class="grid grid-cols-2 md:grid-cols-4 gap-3">
<?php foreach ( $gallery as $i => $aid ) : ?><button type="button" data-lightbox="studio" data-src="<?php echo esc_url( vs_img_url( $aid ) ); ?>" data-caption="<?php echo esc_attr( sprintf( '%s — %d / %d', get_the_title(), $i + 1, count( $gallery ) ) ); ?>" class="fade-up img-zoom relative aspect-[4/3] overflow-hidden bg-white/5"><?php echo wp_get_attachment_image( $aid, 'vs-thumb', false, [ 'class' => 'absolute inset-0 w-full h-full object-cover grayscale hover:grayscale-0 transition-all duration-500', 'loading' => 'lazy', 'alt' => sprintf( __( '%1$s — photo %2$d of %3$d', 'vision-studios' ), get_the_title(), $i + 1, count( $gallery ) ) ] ); ?></button><?php endforeach; ?>
</div></div></section>
<?php endif; ?>

<?php if ( $uses ) : ?>
<section class="bg-black py-20 border-t border-white/10"><div class="max-w-7xl mx-auto px-6 lg:px-10">
<div class="fade-up mb-10"><?php echo vs_eyebrow( __( 'What To Shoot Here', 'vision-studios' ) ) . vs_h2( esc_html( vs_meta( $id, 'use_cases_heading', __( 'Built for a wide range of productions.', 'vision-studios' ) ) ), 'max-w-4xl text-[clamp(1.6rem,3.5vw,2.5rem)]' ); // phpcs:ignore ?></div>
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-px bg-white/10">
<?php foreach ( $uses as $i => $u ) : ?><div class="fade-up service-card bg-black p-8"><p class="font-mono-tag text-xs lg:text-[10px] text-white/50 mb-3"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></p><h3 class="font-display uppercase text-lg"><?php echo esc_html( $u[0] ); ?></h3><p class="text-white/60 text-sm leading-relaxed mt-3"><?php echo esc_html( $u[1] ?? '' ); ?></p></div><?php endforeach; ?>
</div></div></section>
<?php endif; ?>

<?php echo vs_faq_section( $faq, sprintf( __( 'About %s.', 'vision-studios' ), get_the_title() ) ); // phpcs:ignore ?>

<section id="book" class="bg-black py-24 border-t border-white/10"><div class="max-w-7xl mx-auto px-6 lg:px-10 grid lg:grid-cols-2 gap-14">
<div class="fade-up"><?php echo vs_eyebrow( sprintf( __( 'Book %s', 'vision-studios' ), get_the_title() ) ) . vs_h2( __( 'Tell Us About<br/><span class="text-accent">Your Production.</span>', 'vision-studios' ) ); // phpcs:ignore ?><p class="mt-6 text-white/60 max-w-md"><?php esc_html_e( 'Send us your dates and a short brief. We reply with availability, a crew recommendation and a quote — usually within a working day.', 'vision-studios' ); ?></p>
<?php if ( $map ) : ?><div class="mt-8 aspect-[16/10] bg-white/5 overflow-hidden"><?php echo vs_map( $map, get_the_title() ); // phpcs:ignore ?></div><?php endif; ?></div>
<?php echo vs_form( 'booking', get_the_title() ); // phpcs:ignore ?>
</div></section>

<?php
$related = $city ? array_values( array_filter( vs_city_studios( $city ), fn( $s ) => $s->ID !== $id ) ) : [];
if ( count( $related ) < 4 ) {
	$related = array_merge( $related, get_posts( [ 'post_type' => 'studio', 'posts_per_page' => 4 - count( $related ), 'post__not_in' => array_merge( [ $id ], wp_list_pluck( $related, 'ID' ) ), 'orderby' => 'rand' ] ) );
}
$related = array_slice( $related, 0, 4 );
if ( $related ) : ?>
<section class="bg-black py-20 border-t border-white/10"><div class="max-w-7xl mx-auto px-6 lg:px-10">
<div class="fade-up flex items-end justify-between mb-10 flex-wrap gap-4"><div><?php echo vs_eyebrow( __( 'More Studios', 'vision-studios' ) ) . vs_h2( __( 'Other Spaces You Might Like.', 'vision-studios' ) ); // phpcs:ignore ?></div><?php echo vs_btn( home_url( '/#studios' ), __( 'All cities', 'vision-studios' ), 'ghost' ); // phpcs:ignore ?></div>
<div class="grid grid-cols-2 md:grid-cols-4 gap-4"><?php foreach ( $related as $r ) { echo vs_studio_card( $r ); } // phpcs:ignore ?></div></div></section>
<?php endif; ?>
<?php get_footer();
