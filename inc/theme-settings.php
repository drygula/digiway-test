<?php
add_action('after_setup_theme', 'digiway_test_setup');

function digiway_test_setup(): void
{
  register_nav_menus(
    [
      'primary' => __('Primary Menu', 'digiway-test'),
    ]
  );
}

add_action('wp_enqueue_scripts', 'digiway_test_enqueue_styles', 20);

function digiway_test_enqueue_styles(): void
{
  $parent_style = 'parent-style';

  wp_enqueue_style(
    $parent_style,
    get_template_directory_uri() . '/style.css',
    [],
    wp_get_theme(get_template())->get('Version')
  );

  wp_enqueue_style(
    'digiway-child-style',
    get_stylesheet_uri(),
    [$parent_style],
    wp_get_theme()->get('Version')
  );
}
