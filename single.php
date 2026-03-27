<?php get_header(); ?>

<?php while ( have_posts() ) : the_post(); ?>

<div class="page-paper">
  <div class="page-layout">

    <div class="page-layout__main">
      <div class="container">

        <div class="course-hero">
          <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="course-hero__back">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
            <?php esc_html_e( 'Volver al blog', 'edu-theme' ); ?>
          </a>

          <p class="course-hero__eyebrow animate-up">
            <?php
            $cats = get_the_category();
            if ( $cats ) {
              echo esc_html( $cats[0]->name );
              echo ' &middot; ';
            }
            echo esc_html( get_the_date() );
            ?>
          </p>

          <h1 class="course-hero__title animate-up animate-up--delay-1"><?php echo edu_split_title( get_the_title() ); ?></h1>

          <?php if ( has_excerpt() ) : ?>
            <p class="course-hero__subtitle animate-up animate-up--delay-2"><?php echo esc_html( get_the_excerpt() ); ?></p>
          <?php endif; ?>

          <?php
          $tags = get_the_tags();
          if ( $tags ) :
          ?>
            <div class="course-hero__badges animate-up animate-up--delay-3">
              <?php foreach ( $tags as $tag ) : ?>
                <a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>" class="tag"><?php echo esc_html( $tag->name ); ?></a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <?php if ( has_post_thumbnail() ) : ?>
            <div class="course-hero__featured-img animate-up animate-up--delay-4">
              <?php the_post_thumbnail( 'edu-hero', array( 'alt' => get_the_title() ) ); ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="page-layout__content">
          <div class="prose">
            <?php the_content(); ?>
          </div>

          <nav class="module-nav" aria-label="<?php esc_attr_e( 'Navegación entre entradas', 'edu-theme' ); ?>">
            <div class="module-nav__inner">
              <?php $prev = get_previous_post(); if ( $prev ) : ?>
                <a href="<?php echo esc_url( get_permalink( $prev ) ); ?>" class="module-nav__btn">
                  <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                  <span>
                    <span class="module-nav__label"><?php esc_html_e( 'Anterior', 'edu-theme' ); ?></span>
                    <span class="module-nav__title"><?php echo esc_html( get_the_title( $prev ) ); ?></span>
                  </span>
                </a>
              <?php endif; ?>
              <?php $next = get_next_post(); if ( $next ) : ?>
                <a href="<?php echo esc_url( get_permalink( $next ) ); ?>" class="module-nav__btn module-nav__btn--next">
                  <span>
                    <span class="module-nav__label"><?php esc_html_e( 'Siguiente', 'edu-theme' ); ?></span>
                    <span class="module-nav__title"><?php echo esc_html( get_the_title( $next ) ); ?></span>
                  </span>
                  <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
              <?php endif; ?>
            </div>
          </nav>

          <?php comments_template(); ?>
        </div>

      </div>
    </div>

    <aside class="page-layout__sidebar animate-up animate-up--delay-2">
      <div class="sidebar-card">
        <p class="sidebar-card__title"><?php esc_html_e( 'Información', 'edu-theme' ); ?></p>
        <div class="sidebar-info">
          <div class="sidebar-info__item">
            <span class="sidebar-info__key"><?php esc_html_e( 'Publicado', 'edu-theme' ); ?></span>
            <span class="sidebar-info__value"><?php echo esc_html( get_the_date() ); ?></span>
          </div>
          <?php
          $cats = get_the_category();
          if ( $cats ) :
          ?>
            <div class="sidebar-info__item">
              <span class="sidebar-info__key"><?php esc_html_e( 'Categoría', 'edu-theme' ); ?></span>
              <span class="sidebar-info__value">
                <?php
                $cat_links = array();
                foreach ( $cats as $cat ) {
                  $cat_links[] = '<a href="' . esc_url( get_category_link( $cat->term_id ) ) . '" style="color:inherit;text-decoration:none;">' . esc_html( $cat->name ) . '</a>';
                }
                echo implode( ', ', $cat_links );
                ?>
              </span>
            </div>
          <?php endif; ?>
        </div>

        <?php
        $tags = get_the_tags();
        if ( $tags ) :
        ?>
          <div class="sidebar-tags">
            <?php foreach ( $tags as $tag ) : ?>
              <a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>" class="tag"><?php echo esc_html( $tag->name ); ?></a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <?php get_template_part( 'template-parts/sidebar', 'blog' ); ?>
    </aside>

  </div>
</div>

<?php endwhile; ?>

<?php get_footer(); ?>
