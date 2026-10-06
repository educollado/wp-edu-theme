<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?><footer class="footer">
  <div class="footer__inner">
    <?php
    wp_nav_menu( array(
      'theme_location' => 'footer',
      'menu_class'     => 'footer__links',
      'container'      => false,
      'fallback_cb'    => false,
      'depth'          => 1,
    ) );
    ?>
    <p class="footer__signature">Hecho en Europa con <span aria-hidden="true">&#10084;&#65039;</span> por Eduardo Collado</p>

    <?php if ( is_active_sidebar( 'sidebar-footer' ) ) : ?>
      <div class="footer__widgets">
        <?php dynamic_sidebar( 'sidebar-footer' ); ?>
      </div>
    <?php endif; ?>
  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
