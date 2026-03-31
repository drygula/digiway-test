<?php

add_shortcode('digiway_menu', 'digiway_menu_shortcode');

function digiway_menu_shortcode()
{
  ob_start();

  wp_nav_menu(
    [
      'theme_location' => 'primary',
      'container'      => 'nav',
      'container_class' => 'wp-block-navigation is-layout-flex',
      'menu_class'     => 'wp-block-navigation__container',
      'fallback_cb'    => false,
      'depth'          => 2,
    ]
  );

  return (string) ob_get_clean();
}
