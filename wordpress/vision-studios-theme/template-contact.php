<?php
/**
 * Template Name: Contact
 */
get_header();
the_post();
$email   = vs_opt( 'email' );
$regions = array_filter( [ __( 'United Kingdom', 'vision-studios' ) => vs_opt( 'phone_uk' ), __( 'Europe', 'vision-studios' ) => vs_opt( 'phone_eu' ), __( 'Turkey', 'vision-studios' ) => vs_opt( 'phone_tr' ) ] );
$cities  = vs_cities();
echo vs_page_hero( __( 'Contact<br/><span class="text-accent">Vision Studios.</span>', 'vision-studios' ), get_the_excerpt(), (string) ( get_the_post_thumbnail_url( null, 'vs-wide' ) ?: vs_city_image( $cities[0] ?? null, 'vs-wide' ) ), __( 'Get In Touch', 'vision-studios' ) ); // phpcs:ignore
?>
<section class="bg-black py-20"><div class="max-w-7xl mx-auto px-6 lg:px-10">
<?php if ( get_the_content() ) : ?><div class="fade-up prose-vs max-w-3xl mb-12"><?php the_content(); ?></div><?php endif; ?>
<div class="grid md:grid-cols-3 gap-4">
<?php foreach ( $regions as $label => $phone ) : ?><div class="fade-up border border-white/10 p-6"><p class="font-mono-tag text-[10px] uppercase tracking-[0.2em] text-accent mb-3"><?php echo esc_html( $label ); ?></p><p class="font-display uppercase text-2xl"><a href="<?php echo esc_attr( vs_tel( $phone ) ); ?>" class="hover:text-accent"><?php echo esc_html( $phone ); ?></a></p><p class="text-white/60 text-sm mt-2"><a href="mailto:<?php echo esc_attr( $email ); ?>" class="hover:text-accent"><?php echo esc_html( $email ); ?></a></p></div><?php endforeach; ?>
</div></div></section>
<section class="bg-black py-20 border-t border-white/10"><div class="max-w-7xl mx-auto px-6 lg:px-10 grid lg:grid-cols-2 gap-14">
<div class="fade-up"><?php echo vs_eyebrow( __( 'Send A Message', 'vision-studios' ) ) . vs_h2( __( 'Start A<br/><span class="text-accent">Conversation.</span>', 'vision-studios' ) ); // phpcs:ignore ?><p class="text-white/60 mt-6 max-w-md"><?php esc_html_e( 'Tell us about your production and where you would like to shoot. We reply within a working day.', 'vision-studios' ); ?></p>
<div class="mt-10"><?php echo vs_eyebrow( __( 'Our Locations', 'vision-studios' ) ); // phpcs:ignore ?>
<?php foreach ( $cities as $c ) : $p = vs_term_meta( $c->term_id, 'phone' ); ?><div class="fade-up py-5 border-t border-white/10 grid sm:grid-cols-[8rem_1fr_auto] gap-3 items-start"><h3 class="font-display uppercase text-xl"><a href="<?php echo esc_url( get_term_link( $c ) ); ?>" class="hover:text-accent"><?php echo esc_html( $c->name ); ?></a></h3><p class="text-white/60 text-sm"><?php echo esc_html( vs_term_meta( $c->term_id, 'address' ) ); ?></p><?php if ( $p ) : ?><a href="<?php echo esc_attr( vs_tel( $p ) ); ?>" class="font-mono-tag text-xs text-white/70 hover:text-accent whitespace-nowrap"><?php echo esc_html( $p ); ?></a><?php endif; ?></div><?php endforeach; ?>
</div>
<div class="mt-8 flex gap-3"><?php foreach ( [ 'instagram' => 'Instagram', 'linkedin' => 'LinkedIn' ] as $k => $l ) : if ( $u = vs_opt( $k ) ) : ?><a href="<?php echo esc_url( $u ); ?>" target="_blank" rel="noopener" class="nav-link font-mono-tag text-xs uppercase tracking-[0.15em] text-white/70"><?php echo esc_html( $l ); ?></a><?php endif; endforeach; ?></div></div>
<?php echo vs_form( 'contact' ); // phpcs:ignore ?>
</div></section>
<?php get_footer();
