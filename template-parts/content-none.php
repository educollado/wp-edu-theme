<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?><div class="error-card" style="max-width:560px;margin:var(--space-xl) auto;text-align:center;">
  <p class="error-card__title"><?php esc_html_e( 'No hay contenido', 'edu-theme' ); ?></p>
  <p class="error-card__desc">
    <?php
    if ( is_search() ) {
      esc_html_e( 'No se encontraron resultados para tu búsqueda. Prueba con otras palabras clave.', 'edu-theme' );
    } else {
      esc_html_e( 'Todavía no hay entradas publicadas aquí. Vuelve pronto.', 'edu-theme' );
    }
    ?>
  </p>
  <?php if ( is_search() ) : ?>
    <?php get_search_form(); ?>
  <?php else : ?>
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn-primary">
      <?php esc_html_e( 'Ir al inicio', 'edu-theme' ); ?>
    </a>
  <?php endif; ?>
</div>
