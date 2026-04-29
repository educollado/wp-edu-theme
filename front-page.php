<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$page_on_front = (int) get_option( 'page_on_front' );
?>

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

  <?php
  $hero_bg    = get_theme_mod( 'hero_bg_image', '' );
  $hero_class = $hero_bg ? 'hero hero--img' : 'hero';
  $hero_style = $hero_bg ? ' style="background-image:url(' . esc_url( $hero_bg ) . ')"' : '';
  ?>

  <div class="page-paper">

    <section class="<?php echo esc_attr( $hero_class ); ?>"<?php echo $hero_style; ?>>
      <div class="hero-content">
        <h1 class="animate-up"><?php echo edu_split_title( get_the_title() ); ?></h1>
        <?php $sub = get_theme_mod( 'hero_sub_text', '' ); if ( $sub ) : ?>
          <p class="hero-sub animate-up animate-up--delay-1"><?php echo esc_html( $sub ); ?></p>
        <?php endif; ?>
        <?php $cred = get_theme_mod( 'hero_cred_text', '' ); if ( $cred ) : ?>
          <p class="hero-cred animate-up animate-up--delay-2"><?php echo wp_kses_post( $cred ); ?></p>
        <?php endif; ?>
      </div>
    </section>

    <div class="page-layout">
      <div class="page-layout__main">
        <div class="container">
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
