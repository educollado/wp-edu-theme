<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( is_active_sidebar( 'sidebar-blog' ) ) :
?>
  <?php dynamic_sidebar( 'sidebar-blog' ); ?>
<?php else : ?>

  <div class="sidebar-card">
    <p class="sidebar-card__title"><?php esc_html_e( 'Buscar', 'edu-theme' ); ?></p>
    <?php get_search_form(); ?>
  </div>

  <div class="sidebar-card">
    <p class="sidebar-card__title"><?php esc_html_e( 'Categorías', 'edu-theme' ); ?></p>
    <ul class="sidebar-cat-list">
      <?php
      $cats = get_categories( array( 'hide_empty' => true, 'number' => 20, 'orderby' => 'count', 'order' => 'DESC' ) );
      foreach ( $cats as $cat ) :
      ?>
        <li>
          <a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>" class="sidebar-cat-link">
            <?php echo esc_html( $cat->name ); ?>
            <span class="sidebar-cat-count"><?php echo (int) $cat->count; ?></span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>

  <div class="sidebar-card">
    <p class="sidebar-card__title"><?php esc_html_e( 'Entradas recientes', 'edu-theme' ); ?></p>
    <ul class="sidebar-posts-list">
      <?php
      $recent = get_posts( array( 'numberposts' => 5, 'post_status' => 'publish', 'post_type' => 'post', 'no_found_rows' => true ) );
      foreach ( $recent as $post ) :
        setup_postdata( $post );
      ?>
        <li class="sidebar-post-item">
          <?php if ( has_post_thumbnail() ) : ?>
            <a href="<?php the_permalink(); ?>" class="sidebar-post-thumb" tabindex="-1" aria-hidden="true">
              <?php the_post_thumbnail( 'thumbnail', array( 'alt' => '' ) ); ?>
            </a>
          <?php endif; ?>
          <div class="sidebar-post-info">
            <a href="<?php the_permalink(); ?>" class="sidebar-post-title"><?php the_title(); ?></a>
            <span class="sidebar-post-date"><?php echo esc_html( get_the_date() ); ?></span>
          </div>
        </li>
      <?php endforeach; wp_reset_postdata(); ?>
    </ul>
  </div>

<?php endif; ?>
