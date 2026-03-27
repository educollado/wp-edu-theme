<?php get_header(); ?>

<div class="page-paper">
  <div class="page-layout">

    <div class="page-layout__main">
      <div class="container">
        <?php $archive_description = get_the_archive_description(); ?>

        <div class="course-hero">
          <h1 class="course-hero__title animate-up">
            <?php
            if ( is_category() ) {
              echo edu_split_title( single_cat_title( '', false ) );
            } elseif ( is_tag() ) {
              echo edu_split_title( '#' . single_tag_title( '', false ) );
            } elseif ( is_author() ) {
              echo edu_split_title( get_the_author() );
            } elseif ( is_year() ) {
              echo edu_split_title( get_the_time( 'Y' ) );
            } elseif ( is_month() ) {
              echo edu_split_title( get_the_time( 'F Y' ) );
            } elseif ( is_day() ) {
              echo edu_split_title( get_the_time( 'j F Y' ) );
            } else {
              esc_html_e( 'Archivo', 'edu-theme' );
            }
            ?>
          </h1>

          <?php if ( $archive_description ) : ?>
            <div class="course-hero__subtitle prose animate-up animate-up--delay-1">
              <?php
              echo wp_kses_post( $archive_description );
              ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="posts-section posts-section--archive">
          <?php if ( have_posts() ) : ?>

            <div class="edu-post-listing">
              <?php
              $archive_index = 0;
              while ( have_posts() ) : the_post();
                $post_id        = get_the_ID();
                $is_podcast     = edu_is_podcast( $post_id );
                $player_html    = edu_get_audio_player_html( $post_id );
                $cats           = get_the_category( $post_id );
                $category_label = $cats ? $cats[0]->name : __( 'Entrada', 'edu-theme' );
                $cta_text       = $player_html ? __( 'Escuchar', 'edu-theme' ) : __( 'Leer', 'edu-theme' );
                $excerpt        = get_the_excerpt();
                $permalink      = get_permalink( $post_id );
                $img_size       = $archive_index === 0 ? 'edu-hero' : 'edu-card';
                $thumb          = has_post_thumbnail() ? get_the_post_thumbnail( $post_id, $img_size, array( 'alt' => '' ) ) : '';

                if ( $archive_index === 0 ) :
              ?>
              <article class="edu-post-listing__featured<?php echo $is_podcast ? ' is-podcast' : ''; ?>">
                <?php if ( $thumb ) : ?>
                  <a href="<?php echo esc_url( $permalink ); ?>" class="edu-post-listing__img" tabindex="-1" aria-hidden="true">
                    <?php echo $thumb; ?>
                  </a>
                <?php endif; ?>
                <div class="edu-post-listing__body">
                  <div class="edu-post-listing__meta">
                    <span class="edu-post-listing__cat<?php echo $is_podcast ? ' is-podcast' : ''; ?>"><?php echo esc_html( $category_label ); ?></span>
                    <span class="edu-post-listing__date"><?php echo esc_html( get_the_date() ); ?></span>
                  </div>
                  <h3 class="edu-post-listing__title edu-post-listing__title--featured">
                    <a href="<?php echo esc_url( $permalink ); ?>"><?php the_title(); ?></a>
                  </h3>
                  <?php if ( $excerpt ) : ?>
                    <p class="edu-post-listing__excerpt"><?php echo esc_html( $excerpt ); ?></p>
                  <?php endif; ?>
                  <?php if ( $player_html ) : ?>
                    <div class="edu-post-listing__player"><?php echo $player_html; ?></div>
                  <?php endif; ?>
                  <a href="<?php echo esc_url( $permalink ); ?>" class="edu-post-listing__cta"><?php echo esc_html( $cta_text ); ?> &rarr;</a>
                </div>
              </article>
              <div class="edu-post-listing__grid">

              <?php else : ?>

              <article class="edu-post-listing__item<?php echo $is_podcast ? ' is-podcast' : ''; ?>">
                <?php if ( $thumb ) : ?>
                  <a href="<?php echo esc_url( $permalink ); ?>" class="edu-post-listing__img" tabindex="-1" aria-hidden="true">
                    <?php echo $thumb; ?>
                  </a>
                <?php endif; ?>
                <div class="edu-post-listing__body">
                  <div class="edu-post-listing__meta">
                    <span class="edu-post-listing__cat<?php echo $is_podcast ? ' is-podcast' : ''; ?>"><?php echo esc_html( $category_label ); ?></span>
                    <span class="edu-post-listing__date"><?php echo esc_html( get_the_date() ); ?></span>
                  </div>
                  <h3 class="edu-post-listing__title">
                    <a href="<?php echo esc_url( $permalink ); ?>"><?php the_title(); ?></a>
                  </h3>
                  <?php if ( $excerpt ) : ?>
                    <p class="edu-post-listing__excerpt edu-post-listing__excerpt--small"><?php echo esc_html( $excerpt ); ?></p>
                  <?php endif; ?>
                  <?php if ( $player_html ) : ?>
                    <div class="edu-post-listing__player edu-post-listing__player--small"><?php echo $player_html; ?></div>
                  <?php endif; ?>
                  <a href="<?php echo esc_url( $permalink ); ?>" class="edu-post-listing__cta"><?php echo esc_html( $cta_text ); ?> &rarr;</a>
                </div>
              </article>

              <?php endif; ?>
              <?php $archive_index++; endwhile; ?>

              <?php if ( $archive_index > 1 ) : ?>
              </div><!-- .edu-post-listing__grid -->
              <?php endif; ?>
            </div><!-- .edu-post-listing -->

            <div class="pagination">
              <?php
              the_posts_pagination( array(
                'mid_size'  => 2,
                'prev_text' => '&larr;',
                'next_text' => '&rarr;',
              ) );
              ?>
            </div>

          <?php else : ?>
            <?php get_template_part( 'template-parts/content', 'none' ); ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <aside class="page-layout__sidebar">
      <?php get_template_part( 'template-parts/sidebar', 'blog' ); ?>
    </aside>

  </div>
</div>

<?php get_footer(); ?>
