<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>

<?php while ( have_posts() ) : the_post(); ?>

<div class="page-paper">
  <div class="page-layout">

    <div class="page-layout__main">
      <div class="container">
        <div class="course-hero">
          <h1 class="course-hero__title animate-up"><?php echo edu_split_title( get_the_title() ); ?></h1>
          <?php if ( has_excerpt() ) : ?>
            <p class="course-hero__subtitle animate-up animate-up--delay-1"><?php echo esc_html( get_the_excerpt() ); ?></p>
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

<?php get_footer(); ?>
