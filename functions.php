<?php

/**
 * Enqueue scripts and styles.
 */
function pxssyinferno_scripts(): void
{
    wp_enqueue_style('bootstrap-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css');
}
add_action('wp_enqueue_scripts', 'pxssyinferno_scripts');

/**
 * Current server is unable to execute image regeneration and being responsive at the
 * same time. The following line disable image regeneration. Please be aware that
 * to get the images regenerated you will need to use a plugin that can do it.
 * Long term vision: Use a S3 bucket for image storage.
 */
add_filter('woocommerce_background_image_regeneration', '__return_false');

/**
 * Remove the storefront side bar if the current page is not
 * a shop page or a product category page.
 */
function remove_storefront_sidebar(): void
{
    if (!is_shop() && !is_product_category()) {
        remove_action('storefront_sidebar', 'storefront_get_sidebar', 10);
    }
}
add_action('get_header', 'remove_storefront_sidebar');

/**
 * Add Custom Fonts
 */
function enqueue_custom_fonts(): void
{
    if (!is_admin()) {
        wp_register_style('eater', 'https://fonts.googleapis.com/css2?family=Eater&display=swap');
        wp_enqueue_style('eater');
    }
}
add_action('wp_enqueue_scripts', 'enqueue_custom_fonts');

/**
 * Default menu of storefront is not sufficient. Hide for example the search
 * from the header and push everything on a single line. The action
 * storefront_primary_navigation is not removed as the mobile menu is
 * depending on that one.
 */
function custom_header_layout(): void
{
    remove_action('storefront_header', 'storefront_product_search', 40);
    remove_action('storefront_header', 'storefront_primary_navigation_wrapper', 42);
    remove_action('storefront_header', 'storefront_primary_navigation', 1);
    remove_action('storefront_header', 'storefront_header_cart', 60);
    remove_action('storefront_header', 'storefront_primary_navigation_wrapper_close', 68);
    add_action('storefront_header', 'storefront_header_cart', 40);
}
add_action('init', 'custom_header_layout');

/**
 * Remove any widget that will not be used
 */
function unregister_unwanted_parent_sidebars(): void
{
    // Remove the side widget that we do not use
    unregister_sidebar('footer-4');
    unregister_sidebar('header-1');
}
add_action('widgets_init', 'unregister_unwanted_parent_sidebars', 11);

/**
 * Override the store front credit function to place a custom Copyright message
 */
function storefront_credit(): void
{
    ?>
  <div class="copyright">
    <p>
      <?php echo esc_html('&copy; ' . date('Y') . ', ' . get_bloginfo('name')); ?>
    </p>
  </div>
    <?php
}

/**
 * On a device with a small screen a footer bar is rendered. By default this bar
 * helps visitors to navigate to the account, search and basket pages. Visitors
 * can open a Tidio chat by clicking on an absolute positioned button in the content
 * area of the website. Moving this button to the footer bar will free up content
 * space. That is why the storefront_handheld_footer_bar is overwritten.
 */
function storefront_handheld_footer_bar(): void
{
    // The account option has been removed from the
    // links array to make place for the chat option.
    $links = array(
      'search'     => array(
        'priority' => 10,
        'callback' => 'storefront_handheld_footer_bar_search',
      ),
      'chat'       => array(
        'priority' => 20,
        'callback' => 'storefront_handheld_footer_bar_chat_link',
      ),
      'cart'       => array(
        'priority' => 30,
        'callback' => 'storefront_handheld_footer_bar_cart_link',
      )
    );

    if (did_action('woocommerce_blocks_enqueue_cart_block_scripts_after')
        || did_action('woocommerce_blocks_enqueue_checkout_block_scripts_after')
    ) {
        return;
    }

    if (wc_get_page_id('cart') === -1) {
        unset($links['cart']);
    }

    $links = apply_filters('storefront_handheld_footer_bar_links', $links);
    ?>
    <div class="storefront-handheld-footer-bar">
      <ul class="columns-<?php echo count($links); ?>">
        <?php foreach ($links as $key => $link) : ?>
          <li class="<?php echo esc_attr($key); ?>">
            <?php
            if ($link['callback']) {
                call_user_func($link['callback'], $key, $link);
            }
            ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php
}

/**
 * Override the handheld search bar as the orignal source
 * includes an empty href which has a negative impact on
 * the SEO score of the site.
 */
function storefront_handheld_footer_bar_search(): void
{
    echo '<a href="javascript:;">' . esc_attr__('Search', 'storefront') . '</a>';
    storefront_product_search();
}

/**
 * Render for a custom chat option in the handheld footer.
 * Clicking the button will open the Tidio Chat.
 */
function storefront_handheld_footer_bar_chat_link(): void
{
    echo '<a class="footer-chat" onclick="tidioChatApi.display(true);tidioChatApi.open()">Chat</a>';
}
