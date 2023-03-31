<?php
/**
 * Plugin Name: Custom linked products for Equipo theme
 * Description: Removes the built-in Frequently Bought Together box of the Equipo theme and shows a custom (better :P) one. Requires the Advanced Custom Fields plugin.
 * Version: 1.0
 * Tested up to: ?
 * Author: marci
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Text Domain: ?
 * Domain Path: /languages
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Network: False
 */

// Remove the Equipo theme's built-in Frequently Bought Together box
add_action('woocommerce_after_single_product_summary', 'equipo_enovathemes_custom_linked_products__remove_single_product_fbt', 4);
function equipo_enovathemes_custom_linked_products__remove_single_product_fbt() {
    remove_action('woocommerce_after_single_product_summary', 'equipo_enovathemes_single_product_fbt', 5);
}

// The title above our custom linked products section
// default: "Kapcsolódó termékek"
function _equipo_enovathemes_custom_linked_products__get_section_title() {
    $terms = get_the_terms(get_the_ID(), 'product_cat');
    $terms = reset($terms);
    $category = $terms->name;

    $titleSuffix = "termékek";

    switch ($category) {
        case "Tűzőgépek":
            $titleSuffix = "kötőelemek";
            break;
        case "Tározott szegek":
        case "Tározott csavarok":
            $titleSuffix = "szerszámok";
            break;
    }

    return "Kompatibilis $titleSuffix";
}

// Add our own custom linked products section
add_action('woocommerce_after_single_product_summary', 'equipo_enovathemes_custom_linked_products', 5);
function equipo_enovathemes_custom_linked_products() {
    $fieldName = 'kapcsolodo_termekek';
    $subFieldName = 'kapcsolodo_termek';

    if (have_rows($fieldName)) {
        $currentProduct = wc_get_product(get_the_ID());
        $currentProductUrl = $currentProduct->get_permalink();
        $title = _equipo_enovathemes_custom_linked_products__get_section_title();
        $ajaxAddToCartEnabled = (get_option( 'woocommerce_enable_ajax_add_to_cart' ) === "yes");

        // style and structure is partly reused from the table on the cart page (/kosar)
        ?>
        <div class="custom-linked-products-outer">
        <h4><?= $title ?></h4>
        <div class="custom-linked-products">
        <table class="custom-linked-products-table<?= $ajaxAddToCartEnabled ? " custom-linked-products-ajax-add-to-cart" : "" ?>" cellspacing="0" cellpadding="0">
        <?php

        $tickSvg = equipo_enovathemes_svg_icon('tick.svg');

        while (have_rows($fieldName)) : the_row();
            $sku = get_sub_field($subFieldName);
            _equipo_enovathemes_custom_linked_products__display_linked_product($sku, $currentProductUrl, $tickSvg);
        endwhile;

        ?>
        </table>
        </div>
        </div>
        <?php
    }
}

function _equipo_enovathemes_custom_linked_products__display_linked_product($sku, $currentProductUrl, $tickSvg) {
    $productId = wc_get_product_id_by_sku($sku);
    $product = wc_get_product($productId);
    $productName = $product->get_name();
    $productUrl = $product->get_permalink();
    $productPriceIncludingTax = wc_price(wc_get_price_including_tax($product));
    $productPriceExcludingTax = wc_price(round(wc_get_price_excluding_tax($product)));
    ?>
    <tr>
    <td class="product-name"><a href="<?= $productUrl ?>" title="<?= $sku ?>"><?= $productName ?></a></td>
    <td class="product-price"><?= $productPriceIncludingTax ?> <span class="product-price-without-tax">(<?= $productPriceExcludingTax ?> + áfa)</span></td>
    <td>
        <div class="add-to-cart">
            <?= _equipo_enovathemes_custom_linked_products__display_add_to_cart_button($product, $currentProductUrl) ?>
            <div class="ajax-add-to-cart-loading">
                <svg viewBox="0 0 56 56">
                    <circle class="loader-path" cx="28" cy="28" r="20"/>
                </svg>
                <?= $tickSvg ?>
            </div>
            
        </div>
    </td>
    </tr>
    <?php
}


add_filter('woocommerce_add_to_cart_form_action', 'equipo_enovathemes_custom_linked_products__filter_add_to_cart_button_form_action');
function equipo_enovathemes_custom_linked_products__filter_add_to_cart_button_form_action($original) {
    global $equipo_enovathemes_custom_linked_products__filter_add_to_cart_button_form_action_override;

    $override = $equipo_enovathemes_custom_linked_products__filter_add_to_cart_button_form_action_override;

    if (isset($override)) {
        return $override;
    }

    return $original;
}

function _equipo_enovathemes_custom_linked_products__display_add_to_cart_button($productToAddToCart, $currentProductUrl) {
    global $product;
    $product = $productToAddToCart;
    global $equipo_enovathemes_custom_linked_products__filter_add_to_cart_button_form_action_override;
    $equipo_enovathemes_custom_linked_products__filter_add_to_cart_button_form_action_override = $currentProductUrl;
    woocommerce_simple_add_to_cart();
    $equipo_enovathemes_custom_linked_products__filter_add_to_cart_button_form_action_override = NULL;
}

add_action('wp_enqueue_scripts', 'equipo_enovathemes_custom_linked_products__setup_styles');
function equipo_enovathemes_custom_linked_products__setup_styles() {
    wp_enqueue_script('equipo_enovathemes_custom_linked_products', plugins_url('script.js', __FILE__));
    wp_register_style('equipo_enovathemes_custom_linked_products', plugins_url('style.css', __FILE__));
    wp_enqueue_style('equipo_enovathemes_custom_linked_products');
}
