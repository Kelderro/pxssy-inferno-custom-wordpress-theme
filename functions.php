<?php
/**
 * Enqueue scripts and styles.
 */
function pxssyinferno_scripts() {
  wp_enqueue_style( 'bootstrap-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css');
}
add_action( 'wp_enqueue_scripts', 'pxssyinferno_scripts' );

/**
 * Remove the storefront side bar if the current page is not
 * a shop page or a product category page.
 */
function remove_storefront_sidebar() {
    if ( !is_shop() && !is_product_category() ) {
    	remove_action( 'storefront_sidebar', 'storefront_get_sidebar', 10 );
    } 
}
add_action( 'get_header', 'remove_storefront_sidebar' );

/**
 * Add Custom Fonts
 */
function enqueue_custom_fonts() {
	if(!is_admin()) {
		wp_register_style( 'eater', 'https://fonts.googleapis.com/css2?family=Eater&display=swap' );
		wp_enqueue_style( 'eater' );
	}
}
add_action( 'wp_enqueue_scripts', 'enqueue_custom_fonts' );

/**
 * Move the primary navigation to another place in the header by changing the priority
 */
function child_theme_init() {
  remove_action( 'storefront_header', 'storefront_primary_navigation', 50 );
  add_action( 'storefront_header', 'storefront_primary_navigation', 39 );
}
//add_action( 'init', 'child_theme_init' );

/**
 * Remove any widget that will not be used
 */
function unregister_unwanted_parent_sidebars() {
  // Remove the side
  unregister_sidebar('footer-4');
  unregister_sidebar('header-1');
}
add_action( 'widgets_init', 'unregister_unwanted_parent_sidebars', 11);

/**
 * Override the store front credit function to place a custom Copyright message
 */
function storefront_credit() {?>
  <div class="copyright">
    <p>
      <?php echo esc_html( '&copy; ' . date( 'Y' ) . ', ' . get_bloginfo( 'name' )); ?>
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
function storefront_handheld_footer_bar() {
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

		if ( did_action( 'woocommerce_blocks_enqueue_cart_block_scripts_after' ) || did_action( 'woocommerce_blocks_enqueue_checkout_block_scripts_after' ) ) {
			return;
		}

		if ( wc_get_page_id( 'myaccount' ) === -1 ) {
			unset( $links['my-account'] );
		}

		if ( wc_get_page_id( 'cart' ) === -1 ) {
			unset( $links['cart'] );
		}

		$links = apply_filters( 'storefront_handheld_footer_bar_links', $links );
		?>
		<div class="storefront-handheld-footer-bar">
			<ul class="columns-<?php echo count( $links ); ?>">
				<?php foreach ( $links as $key => $link ) : ?>
					<li class="<?php echo esc_attr( $key ); ?>">
						<?php
						if ( $link['callback'] ) {
							call_user_func( $link['callback'], $key, $link );
						}
						?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}

/**
 * Render for a custom chat option in the handheld footer.
 * Clicking the button will open the Tidio Chat.
 */
function storefront_handheld_footer_bar_chat_link() {
    echo '<a class="footer-chat" href="javascript:;" onclick="tidioChatApi.display(true);tidioChatApi.open()">Chat</a>';
}