<?php 
/*-----------------------------------------------------------------------------------*/
/* This theme supports WooCommerce */
/*-----------------------------------------------------------------------------------*/

add_action( 'after_setup_theme', 'woocommerce_support' );
function woocommerce_support() {
  add_theme_support( 'woocommerce' );
}
/*-----------------------------------------------------------------------------------*/
/* WooCommerce Functions */
/*-----------------------------------------------------------------------------------*/

if (class_exists('woocommerce')) {

  // Disable WooCommerce styles
  if ( version_compare( WOOCOMMERCE_VERSION, "2.1" ) >= 0 ) {
  add_filter( 'woocommerce_enqueue_styles', '__return_false' );
  } else {
    define( 'WOOCOMMERCE_USE_CSS', false );
  }
  // Disable WooCommerce Lightbox
  update_option( 'woocommerce_enable_lightbox', false );
    
}
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);

add_action('kt_afterheader', 'kt_wc_print_notices');
function kt_wc_print_notices() {
  if (class_exists('woocommerce')) {
    if(!is_shop() and !is_woocommerce() and !is_cart() and !is_checkout() and !is_account_page() ) {
      echo '<div class="container">';
      echo do_shortcode( '[woocommerce_messages]' );
      echo '</div>';
    }
  }
}

// Set the number of columns to 3
function kad_woocommerce_cross_sells_columns( $columns ) {
  return 3;
}
add_filter( 'woocommerce_cross_sells_columns', 'kad_woocommerce_cross_sells_columns', 10, 1 );

// Limit the number of cross sells displayed to a maximum of 3
function kad_woocommerce_cross_sells_total( $limit ) {
  return 3;
}
add_filter( 'woocommerce_cross_sells_total', 'kad_woocommerce_cross_sells_total', 10, 1 );

// Redefine woocommerce_output_related_products()
function kadence_woo_related_products_limit() {
  global $product, $woocommerce;
  $related = $product->get_related();
  $args = array(
    'post_type'           => 'product',
    'no_found_rows'       => 1,
    'posts_per_page'      => 4,
    'ignore_sticky_posts'   => 1,
    //'orderby'               => $orderby,
    'post__in'              => $related,
    'post__not_in'          => array($product->id)
  );
  return $args;
}
add_filter( 'woocommerce_related_products_args', 'kadence_woo_related_products_limit' );
// Number of products per page
add_filter('loop_shop_per_page', 'kadence_wooframework_products_per_page');
if (!function_exists('kadence_wooframework_products_per_page')) {
  function kadence_wooframework_products_per_page() {
    global $virtue;
    if ( isset( $virtue['products_per_page'] ) ) {
      return $virtue['products_per_page'];
    }
  }
}

// Display product tabs?
add_action('wp_head','kadence_wooframework_tab_check');
if ( ! function_exists( 'kadence_wooframework_tab_check' ) ) {
  function kadence_wooframework_tab_check() {
    global $virtue;
    if ( isset( $virtue[ 'product_tabs' ] ) && $virtue[ 'product_tabs' ] == "0" ) {
      remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);
    }
  }
}

// Display related products?
add_action('wp_head','kadence_wooframework_related_products');
if ( ! function_exists( 'kadence_wooframework_related_products' ) ) {
  function kadence_wooframework_related_products() {
    global $virtue;
    if ( isset( $virtue[ 'related_products' ] ) && $virtue[ 'related_products' ] == "0" ) {
      remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
    }
  }
}

add_filter('add_to_cart_fragments', 'kadence_woocommerce_header_add_to_cart_fragment');
function kadence_woocommerce_header_add_to_cart_fragment( $fragments ) {
    global $woocommerce;
    ob_start(); ?>

    <a class="cart-contents" href="<?php echo esc_url($woocommerce->cart->get_cart_url()); ?>" title="<?php esc_attr_e('View your shopping cart', 'virtue'); ?>">
        <i class="icon-shopping-cart" style="padding-right:5px;"></i>  <?php _e('Your Cart', 'virtue');?> <span class="kad-cart-dash">-</span> <?php echo $woocommerce->cart->get_cart_total(); ?>
    </a>
    <?php
    $fragments['a.cart-contents'] = ob_get_clean();
    return $fragments;
}


remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
add_action( 'woocommerce_shop_loop_item_title', 'kt_woocommerce_template_loop_product_title', 10);
function kt_woocommerce_template_loop_product_title() {
  echo '<h5>'.get_the_title().'</h5>';
}

remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );


remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation', 10 );
remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 );
add_action( 'woocommerce_single_variation', 'kt_woocommerce_single_variation', 10 );
add_action( 'woocommerce_single_variation', 'kt_woocommerce_single_variation_add_to_cart_button', 20 );

if ( ! function_exists( 'kt_woocommerce_single_variation_add_to_cart_button' ) ) {
  /**
   * Output the add to cart button for variations.
   */
  function kt_woocommerce_single_variation_add_to_cart_button() {
    global $product;
    ?>
    <div class="variations_button">
      <?php woocommerce_quantity_input( array( 'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( $_POST['quantity'] ) : 1 ) ); ?>
      <button type="submit" class="kad_add_to_cart headerfont kad-btn kad-btn-primary single_add_to_cart_button"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>
      <input type="hidden" name="add-to-cart" value="<?php echo absint( $product->id ); ?>" />
      <input type="hidden" name="product_id" value="<?php echo absint( $product->id ); ?>" />
      <input type="hidden" name="variation_id" class="variation_id" value="" />
    </div>
    <?php
  }
}

if ( ! function_exists( 'kt_woocommerce_single_variation' ) ) {
  /**
   * Output placeholders for the single variation.
   */
  function kt_woocommerce_single_variation() {
    echo '<div class="single_variation headerfont"></div>';
  }
}
