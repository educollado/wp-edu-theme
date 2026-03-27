<?php
get_header();
global $wp_query;
$wp_query->set_404();
status_header( 404 );
?>

<div class="page-paper">
  <div class="error-wrap">
    <div class="error-card animate-up">
      <p class="error-card__code">404</p>
      <h1 class="error-card__title"><?php esc_html_e( 'Página no encontrada', 'edu-theme' ); ?></h1>
      <p class="error-card__desc">
        <?php esc_html_e( 'La página que buscas no existe o ha sido movida. Puede que la URL esté mal escrita o el contenido haya cambiado de ubicación.', 'edu-theme' ); ?>
      </p>
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn-primary">
        &larr; <?php esc_html_e( 'Volver al inicio', 'edu-theme' ); ?>
      </a>
    </div>
  </div>
</div>

<?php get_footer(); ?>
