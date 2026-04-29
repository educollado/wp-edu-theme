<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?><article class="post-card">
  <div class="post-card__bar"></div>

  <?php if ( has_post_thumbnail() ) : ?>
    <div class="post-card__thumb">
      <a href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
        <?php the_post_thumbnail( 'edu-card', array( 'alt' => '' ) ); ?>
      </a>
    </div>
  <?php endif; ?>

  <div class="post-card__body">
    <div class="post-card__meta">
      <?php
      $cats = get_the_category();
      if ( $cats ) :
      ?>
        <span class="post-card__category"><?php echo esc_html( $cats[0]->name ); ?></span>
      <?php endif; ?>
      <span class="post-card__date"><?php echo esc_html( get_the_date() ); ?></span>
    </div>

    <h2 class="post-card__title">
      <a href="<?php the_permalink(); ?>" style="text-decoration:none;color:inherit;"><?php the_title(); ?></a>
    </h2>

    <?php $exc = get_the_excerpt(); if ( $exc ) : ?>
      <p class="post-card__excerpt"><?php echo esc_html( $exc ); ?></p>
    <?php endif; ?>
  </div>

  <div class="post-card__footer">
    <a href="<?php the_permalink(); ?>" class="post-card__cta">
      <?php esc_html_e( 'Leer entrada', 'edu-theme' ); ?> &rarr;
    </a>
  </div>
</article>
