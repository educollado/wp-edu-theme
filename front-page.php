<?php
$page_on_front = (int) get_option( 'page_on_front' );

get_header();
?>

<?php if ( $page_on_front > 0 ) : ?>

  <?php
  $front_query = new WP_Query( array(
      'page_id'        => $page_on_front,
      'post_type'      => 'page',
      'post_status'    => 'publish',
      'posts_per_page' => 1,
      'no_found_rows'  => true,
  ) );
  ?>

  <?php while ( $front_query->have_posts() ) : $front_query->the_post(); ?>

  <div class="page-paper">
    <div class="page-layout">
      <div class="page-layout__main">
        <div class="container">

          <div class="course-hero">
            <h1 class="course-hero__title animate-up"><?php echo edu_split_title( get_the_title() ); ?></h1>
            <?php
            $desc = has_excerpt() ? get_the_excerpt() : get_bloginfo( 'description' );
            if ( $desc ) :
            ?>
              <p class="course-hero__subtitle animate-up animate-up--delay-1"><?php echo esc_html( $desc ); ?></p>
            <?php endif; ?>
          </div>

          <div class="page-layout__content">
            <div class="prose">
              <?php the_content(); ?>
            </div>
          </div>

        </div>
      </div>
      <aside class="page-layout__sidebar">
        <?php get_template_part( 'template-parts/sidebar', 'blog' ); ?>
      </aside>
    </div>
  </div>

  <?php endwhile; ?>
  <?php wp_reset_postdata(); ?>

<?php endif; ?>

<?php get_footer(); ?>
