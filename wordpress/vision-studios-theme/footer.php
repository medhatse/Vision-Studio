</main>
<?php $vs_email = vs_opt( 'email' ); ?>
<footer class="bg-black border-t border-white/10 pt-16 pb-8">
<div class="max-w-7xl mx-auto px-6 lg:px-10 grid sm:grid-cols-2 lg:grid-cols-4 gap-12">
<div><img src="<?php echo esc_url( VS_URI . '/assets/img/logo-full.png' ); ?>" alt="Vision Studios" class="h-9 w-auto mb-4"/><p class="text-white/50 text-sm max-w-xs"><?php esc_html_e( 'Your professional TV and film production studio space in London, Dublin, Paris and Istanbul. 25+ years of experience across four capital cities.', 'vision-studios' ); ?></p></div>
<div><p class="font-mono-tag text-xs lg:text-[11px] uppercase tracking-[0.2em] text-white/55 mb-4"><?php esc_html_e( 'Studios', 'vision-studios' ); ?></p><ul class="space-y-2 text-sm text-white/70">
<?php foreach ( vs_cities() as $c ) : ?><li><a href="<?php echo esc_url( get_term_link( $c ) ); ?>" class="hover:text-accent"><?php echo esc_html( $c->name ); ?></a></li><?php endforeach; ?>
</ul></div>
<div><p class="font-mono-tag text-xs lg:text-[11px] uppercase tracking-[0.2em] text-white/55 mb-4"><?php esc_html_e( 'Company', 'vision-studios' ); ?></p><ul class="space-y-2 text-sm text-white/70">
<li><a href="<?php echo esc_url( home_url( '/#services' ) ); ?>" class="hover:text-accent"><?php esc_html_e( 'Services', 'vision-studios' ); ?></a></li>
<li><a href="<?php echo esc_url( vs_page_url( 'gallery' ) ); ?>" class="hover:text-accent"><?php esc_html_e( 'Gallery', 'vision-studios' ); ?></a></li>
<li><a href="<?php echo esc_url( vs_news_url() ); ?>" class="hover:text-accent"><?php esc_html_e( 'News', 'vision-studios' ); ?></a></li>
<li><a href="<?php echo esc_url( vs_page_url( 'about' ) ); ?>" class="hover:text-accent"><?php esc_html_e( 'About Us', 'vision-studios' ); ?></a></li>
<li><a href="<?php echo esc_url( vs_page_url( 'contact' ) ); ?>" class="hover:text-accent"><?php esc_html_e( 'Contact', 'vision-studios' ); ?></a></li>
</ul></div>
<div><p class="font-mono-tag text-xs lg:text-[11px] uppercase tracking-[0.2em] text-white/55 mb-4"><?php esc_html_e( 'Get In Touch', 'vision-studios' ); ?></p><ul class="space-y-2 text-sm text-white/70">
<li><!--email_off--><a href="mailto:<?php echo esc_attr( $vs_email ); ?>" class="hover:text-accent"><?php echo esc_html( $vs_email ); ?></a><!--/email_off--></li>
<?php foreach ( [ 'phone_uk' => __( 'United Kingdom', 'vision-studios' ), 'phone_eu' => __( 'Europe', 'vision-studios' ), 'phone_tr' => __( 'Turkey', 'vision-studios' ) ] as $k => $label ) : if ( $p = vs_opt( $k ) ) : ?>
<li><a href="<?php echo esc_attr( vs_tel( $p ) ); ?>" class="hover:text-accent"><?php echo esc_html( $label . ' · ' . $p ); ?></a></li>
<?php endif; endforeach; ?>
<li class="text-white/55 pt-1"><?php echo esc_html( vs_opt( 'hq_address' ) ); ?></li><li class="text-white/55"><?php echo esc_html( vs_opt( 'opening_hours' ) ); ?></li></ul>
<div class="flex gap-3 mt-4">
<?php foreach ( [ 'instagram' => 'ig', 'linkedin' => 'in' ] as $k => $short ) : if ( $u = vs_opt( $k ) ) : ?><a href="<?php echo esc_url( $u ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( ucfirst( $k ) ); ?>" class="w-9 h-9 flex items-center justify-center border border-white/20 text-xs lg:text-[10px] font-mono-tag uppercase hover:border-accent hover:text-accent"><?php echo esc_html( $short ); ?></a><?php endif; endforeach; ?>
</div></div></div>
<div class="max-w-7xl mx-auto px-6 lg:px-10 mt-12 pt-6 border-t border-white/10 flex flex-wrap justify-between gap-4 text-xs text-white/50"><p>© <?php echo esc_html( date_i18n( 'Y' ) ); ?> Vision Studios. <?php esc_html_e( 'All rights reserved.', 'vision-studios' ); ?><?php foreach ( [ 'privacy-policy' => __( 'Privacy', 'vision-studios' ), 'terms' => __( 'Terms of hire', 'vision-studios' ) ] as $vs_slug => $vs_label ) : $vs_pg = get_page_by_path( $vs_slug ); if ( $vs_pg && 'publish' === $vs_pg->post_status ) : ?> · <a href="<?php echo esc_url( get_permalink( $vs_pg ) ); ?>" class="hover:text-accent"><?php echo esc_html( $vs_label ); ?></a><?php endif; endforeach; ?></p><p>London · Dublin · Paris · Istanbul</p></div>
</footer>
<?php echo vs_lightbox(); // phpcs:ignore ?>
<?php wp_footer(); ?>
</body></html>
