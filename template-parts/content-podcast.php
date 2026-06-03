<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?><article class="post-card post-card--podcast">
  <div class="post-card__bar"></div>

  <?php $player_html = is_category( 'podcast' ) ? edu_get_audio_player_html() : ''; ?>

  <?php if ( has_post_thumbnail() ) : ?>
    <div class="post-card__thumb">
      <a href="<?php echo esc_url( get_permalink() ); ?>" tabindex="-1" aria-hidden="true">
        <?php the_post_thumbnail( 'edu-card', array( 'alt' => '' ) ); ?>
      </a>
    </div>
  <?php endif; ?>

  <div class="post-card__body">
    <div class="post-card__meta">
      <span class="post-card__category">Podcast</span>
      <span class="post-card__date"><?php echo esc_html( get_the_date() ); ?></span>
    </div>

    <h2 class="post-card__title">
      <a href="<?php echo esc_url( get_permalink() ); ?>" style="text-decoration:none;color:inherit;"><?php echo esc_html( get_the_title() ); ?></a>
    </h2>

    <?php if ( $player_html ) : ?>
      <div class="post-card__player">
        <?php echo wp_kses_post( $player_html ); ?>
      </div>
    <?php endif; ?>

    <?php $exc = wp_strip_all_tags( get_the_excerpt() ); if ( $exc ) : ?>
      <p class="post-card__excerpt"><?php echo esc_html( $exc ); ?></p>
    <?php endif; ?>
  </div>

  <div class="post-card__footer">
    <a href="<?php echo esc_url( get_permalink() ); ?>" class="post-card__cta">
      <?php esc_html_e( 'Escuchar episodio', 'edu-theme' ); ?> &rarr;
    </a>
  </div>
</article>
