<?php

add_action('rest_api_init', 'register_import_products_route');

function register_import_products_route()
{
  register_rest_route(
    'test/v1',
    '/import-products',
    [
      'methods' => WP_REST_Server::CREATABLE,
      'callback' => 'import_products_callback',
      // 'permission_callback' => '__return_true',
      'permission_callback' => 'import_products_permission_callback',
    ]
  );
}

function import_products_permission_callback()
{
  return current_user_can('manage_woocommerce');
}

function import_products_callback(WP_REST_Request $request)
{
  $payload = $request->get_json_params();

  if (!is_array($payload)) {
    return new WP_REST_Response(
      [
        'created' => 0,
        'updated' => 0,
        'skipped' => 0,
        'meta' => [
          'success' => false,
          'message' => 'Body must be a JSON array of products.',
        ],
      ],
      400
    );
  }

  if (!class_exists('WooCommerce')) {
    return new WP_REST_Response(
      [
        'created' => 0,
        'updated' => 0,
        'skipped' => 0,
        'meta' => [
          'success' => false,
          'message' => 'WooCommerce is not active.',
        ],
      ],
      500
    );
  }

  if (!function_exists('pll_set_post_language') || !function_exists('pll_save_post_translations')) {
    return new WP_REST_Response(
      [
        'created' => 0,
        'updated' => 0,
        'skipped' => 0,
        'meta' => [
          'success' => false,
          'message' => 'Polylang is not active or required functions are unavailable.',
        ],
      ],
      500
    );
  }

  $created = 0;
  $updated = 0;
  $skipped = 0;
  $log = [];

  foreach ($payload as $item) {
    try {
      $result = import_single_product($item);

      if ($result['status'] === 'created') {
        $created++;
      } elseif ($result['status'] === 'updated') {
        $updated++;
      } else {
        $skipped++;
      }

      $log[] = [
        'sku' => $item['sku'] ?? '',
        'status' => $result['status'],
        'note' => $result['note'],
      ];
    } catch (Throwable $e) {
      $skipped++;

      $log[] = [
        'sku' => $item['sku'] ?? '',
        'status' => 'skipped',
        'note' => $e->getMessage(),
      ];
    }
  }

  return new WP_REST_Response(
    [
      'created' => $created,
      'updated' => $updated,
      'skipped' => $skipped,
      'meta' => [
        'success' => true,
        'total' => count($payload),
        'processed_at' => current_time('mysql'),
        'items' => $log,
      ],
    ],
    200
  );
}

function import_single_product(array $item)
{
  $sku = isset($item['sku']) ? wc_clean($item['sku']) : '';

  if ($sku === '') {
    return [
      'status' => 'skipped',
      'note' => 'SKU is required.',
    ];
  }

  $name = isset($item['name']) ? sanitize_text_field($item['name']) : '';

  if ($name === '') {
    return [
      'status' => 'skipped',
      'note' => 'Product name is required.',
    ];
  }

  $ua_product_id = wc_get_product_id_by_sku($sku);

  if (!$ua_product_id) {
    $ua_product = new WC_Product_Simple();
    $ua_product->set_sku($sku);
    $status = 'created';
  } else {
    $ua_product = wc_get_product($ua_product_id);

    if (!$ua_product) {
      return [
        'status' => 'skipped',
        'note' => 'Failed to load existing product by SKU.',
      ];
    }

    $status = 'updated';
  }

  fill_product_fields($ua_product, $item, 'uk');
  $ua_product_id = $ua_product->save();

  pll_set_post_language($ua_product_id, 'uk');

  $en_product_id = add_en_translation($ua_product_id, $item);

  pll_save_post_translations(
    [
      'uk' => $ua_product_id,
      'en' => $en_product_id,
    ]
  );

  return [
    'status' => $status,
    'note' => sprintf(
      'UA product ID: %d, EN product ID: %d',
      $ua_product_id,
      $en_product_id
    ),
  ];
}

function fill_product_fields(WC_Product_Simple $product, array $item, string $lang)
{
  $name = '';
  if ($lang === 'en' && !empty($item['translations']['en']['name'])) {
    $name = sanitize_text_field($item['translations']['en']['name']);
  } elseif (!empty($item['name'])) {
    $name = sanitize_text_field($item['name']);
  }

  if ($name !== '') {
    $product->set_name($name);
  }

  if (isset($item['price']) && $item['price'] !== '' && $item['price'] !== null) {
    $price = wc_format_decimal($item['price']);
    $product->set_regular_price($price);
  }

  if (array_key_exists('stock', $item)) {
    $stock = $item['stock'];

    $product->set_manage_stock(true);
    $product->set_stock_quantity($stock);
    $product->set_stock_status($stock > 0 ? 'instock' : 'outofstock');
  }

  $product->set_status('publish');
}

function add_en_translation(int $ua_product_id, array $item)
{
  $translations = function_exists('pll_get_post_translations')
    ? pll_get_post_translations($ua_product_id)
    : [];

  $existing_en_id = !empty($translations['en']) ? $translations['en'] : 0;

  if ($existing_en_id > 0) {
    $en_product = wc_get_product($existing_en_id);

    if ($en_product) {
      fill_product_fields($en_product, $item, 'en');

      $en_product->save();

      pll_set_post_language($existing_en_id, 'en');

      return $existing_en_id;
    }
  }

  $en_product = new WC_Product_Simple();
  fill_product_fields($en_product, $item, 'en');

  $en_product_id = $en_product->save();

  pll_set_post_language($en_product_id, 'en');

  return $en_product_id;
}


add_action('rest_api_init', function () {
  register_rest_route('test/v1', '/ping', [
    'methods' => 'GET',
    'callback' => function () {
      return ['status' => 'ok'];
    },
  ]);
});
