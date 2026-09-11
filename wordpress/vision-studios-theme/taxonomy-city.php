<?php
/**
 * City page: every studio in the city, services, contact.
 */
get_header();
$term    = get_queried_object();
$cid     = $term->term_id;
$studios = vs_city_studios( $term );
$hero    = vs_city_image_id( $term );
$country = vs_term_meta( $cid, 'country' );
$phone   = vs_term_meta( $cid, 'phone', vs_opt( 'phone_uk' ) );
$address = vs_term_meta( $cid, 'address' );
$heading = vs_term_meta( $cid, 'heading', sprintf( __( 'TV & Production studios in %s', 'vision-studios' ), $term->name ) );
$heading = preg_replace( '/^Our /', '', $heading );
$parts   = preg_split( '/\s+in\s+/', $heading, 2 );
$others  = array_filter( vs_cities(), fn( $c ) => $c->term_id !== $cid );
$email   = vs_opt( 'email' );
?>
<section class="relative min-h-[70vh] flex flex-col justify-end overflow-hidden">
<?php echo vs_hero_img( $hero, sprintf( __( 'Vision Studios %s', 'vision-studios' ), $term->name ) ); // phpcs:ignore ?>
<div class="absolute inset-0 bg-gradient-to-t from-black via-black/70 to-black/30"></div><div class="grain-overlay"></div>
<div class="relative z-10 max-w-7xl w-full mx-auto px-6 lg:px-10 pt-40 pb-16">
<div class="flex items-center justify-between font-mono-tag text-xs lg:text-[11px] uppercase tracking-[0.2em] text-white/60 mb-6"><span><?php echo esc_html( vs_term_meta( $cid, 'tagline' ) ); ?></span><span class="hidden sm:inline text-accent"><?php echo esc_html( $country ); ?></span><span class="hidden sm:inline"><?php echo esc_html( vs_term_meta( $cid, 'coords' ) ); ?></span></div>
<h1 class="font-display uppercase leading-[0.95] text-[clamp(2.4rem,8vw,5.5rem)]"><?php echo esc_html( $parts[0] ); ?><?php if ( isset( $parts[1] ) ) : ?><br/><span class="text-white/55"><?php esc_html_e( 'in', 'vision-studios' ); ?> </span><?php echo esc_html( $parts[1] ); ?><?php endif; ?></h1>
<p class="mt-6 max-w-xl text-white/70"><?php echo esc_html( sprintf( _n( '%d studio', '%d studios', count( $studios ), 'vision-studios' ), count( $studios ) ) . ( $address ? ' · ' . $address : '' ) ); ?></p>

</div></section>
<?php echo vs_breadcrumbs(); // phpcs:ignore ?>

<section class="bg-black py-16 lg:py-24"><div class="max-w-7xl mx-auto px-6 lg:px-10">
<?php if ( $term->description ) : ?><div class="fade-up prose-vs max-w-3xl mb-16"><?php echo wpautop( wp_kses_post( $term->description ) ); ?></div><?php endif; ?>
<div class="fade-up mb-4"><?php echo vs_eyebrow( sprintf( __( 'Our Studios In %s', 'vision-studios' ), $term->name ) ) . vs_h2( __( 'Choose Your Space.', 'vision-studios' ), 'max-w-3xl' ); // phpcs:ignore ?></div>
<?php foreach ( $studios as $i => $s ) : $img = vs_img_url( vs_studio_gallery_ids( $s->ID )[0] ?? 0, 'vs-wide' ); ?>
<article class="fade-up grid md:grid-cols-2 gap-8 items-center py-12 <?php echo $i ? 'border-t border-white/10' : ''; ?>">
<a href="<?php echo esc_url( get_permalink( $s ) ); ?>" class="group img-zoom relative aspect-[16/10] block overflow-hidden bg-white/5 <?php echo $i % 2 ? 'md:order-2' : ''; ?>"><?php echo wp_get_attachment_image( vs_studio_gallery_ids( $s->ID )[0] ?? 0, 'vs-wide', false, [ 'alt' => $s->post_title . ', ' . $term->name, 'loading' => 'lazy', 'class' => 'absolute inset-0 w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500' ] ); ?></a>
<div><p class="font-mono-tag text-xs lg:text-[10px] uppercase tracking-[0.2em] text-accent mb-2"><?php echo esc_html( vs_meta( $s->ID, 'tagline' ) ); ?></p><h3 class="font-display uppercase text-3xl leading-none"><a href="<?php echo esc_url( get_permalink( $s ) ); ?>" class="hover:text-accent"><?php echo esc_html( $s->post_title ); ?></a></h3>
<p class="text-white/60 text-sm mt-4 leading-relaxed"><?php echo esc_html( $s->post_excerpt ?: wp_trim_words( wp_strip_all_tags( $s->post_content ), 40 ) ); ?></p>
<ul class="mt-5 grid sm:grid-cols-2 gap-x-6 gap-y-1 font-mono-tag text-xs lg:text-[11px] text-white/50"><?php foreach ( array_slice( vs_lines( vs_meta( $s->ID, 'highlights' ) ), 0, 4 ) as $h ) : ?><li class="flex gap-2"><span class="text-accent">—</span><?php echo esc_html( $h ); ?></li><?php endforeach; ?></ul>
<div class="mt-6"><?php echo vs_btn( get_permalink( $s ), __( 'Studio details & booking', 'vision-studios' ), 'ghost' ); // phpcs:ignore ?></div></div></article>
<?php endforeach; ?>
</div></section>

<?php $services = vs_services( 'service' ); if ( $services ) : ?>
<section class="bg-black py-24 border-t border-white/10"><div class="max-w-7xl mx-auto px-6 lg:px-10">
<div class="fade-up mb-12"><?php echo vs_eyebrow( __( 'Our Services', 'vision-studios' ) ) . vs_h2( __( 'Everything Around The Studio.', 'vision-studios' ), 'max-w-3xl' ); // phpcs:ignore ?></div>
<div class="grid md:grid-cols-3 gap-px bg-white/10">
<?php foreach ( array_slice( $services, 0, 3 ) as $i => $s ) : ?><div class="fade-up service-card bg-black p-8"><p class="font-mono-tag text-xs lg:text-[10px] text-white/50 mb-3"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></p><h3 class="font-display uppercase text-lg"><?php echo esc_html( $s->post_title ); ?></h3><p class="text-white/60 text-sm leading-relaxed mt-3"><?php echo esc_html( wp_strip_all_tags( $s->post_content ?: $s->post_excerpt ) ); ?></p></div><?php endforeach; ?>
</div></div></section>
<?php endif; ?>

<section class="bg-black py-16 border-t border-white/10"><div class="max-w-7xl mx-auto px-6 lg:px-10 grid lg:grid-cols-2 gap-10">
<div class="fade-up"><?php echo vs_eyebrow( __( 'Get In Touch', 'vision-studios' ) . ' · ' . $term->name ); // phpcs:ignore ?><p class="font-display uppercase text-2xl"><a href="<?php echo esc_attr( vs_tel( $phone ) ); ?>" class="hover:text-accent"><?php echo esc_html( $phone ); ?></a></p><p class="text-white/60 text-sm mt-3"><?php echo esc_html( $address ); ?></p><p class="text-white/60 text-sm mt-1"><a href="mailto:<?php echo esc_attr( $email ); ?>" class="hover:text-accent"><?php echo esc_html( $email ); ?></a></p>
<div class="mt-6 flex items-center gap-4 flex-wrap"><span class="font-mono-tag text-xs lg:text-[10px] uppercase tracking-[0.2em] text-white/50"><?php esc_html_e( 'Other cities', 'vision-studios' ); ?></span><?php foreach ( $others as $o ) : ?><a href="<?php echo esc_url( get_term_link( $o ) ); ?>" class="nav-link font-mono-tag text-xs uppercase tracking-[0.15em] text-white/70 hover:text-accent"><?php echo esc_html( $o->name ); ?></a><?php endforeach; ?></div></div>
<?php if ( $mq = vs_term_meta( $cid, 'map_query', $address ) ) : ?><div class="fade-up aspect-[16/9] bg-white/5 overflow-hidden"><?php echo vs_map( $mq, 'Vision Studios ' . $term->name ); // phpcs:ignore ?></div><?php endif; ?>
</div></section>
<?php echo vs_faq_section( vs_faq_items( vs_term_meta( $cid, 'faq' ) ), sprintf( __( 'Filming In %s.', 'vision-studios' ), $term->name ) ); // phpcs:ignore ?>
<?php echo vs_cta_band( sprintf( __( 'Book A Studio<br/><span class="text-accent">In %s.</span>', 'vision-studios' ), esc_html( $term->name ) ) ); // phpcs:ignore ?>
<?php get_footer();
