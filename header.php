<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<nav class="nav" id="site-nav">
  <div class="nav__inner">
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="nav__brand">
      <span class="nav__brand-text">eduardo<span>collado.com</span></span>
    </a>

    <?php if ( has_nav_menu( 'primary' ) ) : ?>
      <?php
      wp_nav_menu( array(
        'theme_location' => 'primary',
        'menu_class'     => 'nav__links',
        'container'      => false,
        'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s</ul>',
        'fallback_cb'    => false,
        'walker'         => new Edu_Nav_Walker(),
      ) );
      ?>
    <?php else : ?>
      <ul class="nav__links">
        <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Inicio</a></li>
        <li><a href="<?php echo esc_url( home_url( '/categoria/podcast/' ) ); ?>">Podcasts</a></li>
        <li><a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>">Blog</a></li>
        <li class="nav__item--dropdown">
          <a href="https://formacion.eduardocollado.com">Formación</a>
          <ul class="nav__dropdown">
            <li><a href="https://formacion.eduardocollado.com">Ver todos los cursos</a></li>
          </ul>
        </li>
        <li><a href="<?php echo esc_url( home_url( '/sobre-mi/' ) ); ?>">Sobre mí</a></li>
      </ul>
    <?php endif; ?>

    <button class="nav__toggle" aria-label="<?php esc_attr_e( 'Abrir menú', 'edu-theme' ); ?>" aria-expanded="false" aria-controls="site-nav">
      <span></span>
      <span></span>
      <span></span>
    </button>
  </div>
</nav>

