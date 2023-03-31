jQuery(document).ready(function ($) {
    "use strict";

    // TODO prevent the loading animation becoming stuck

    if (!wc_add_to_cart_params || !wc_add_to_cart_params.wc_ajax_url) {
        return;
    }

    var addToCartUrl = wc_add_to_cart_params.wc_ajax_url.replace('%%endpoint%%', 'add_to_cart');
    var getRefreshedFragmentsUrl = woocommerce_params.wc_ajax_url.replace("%%endpoint%%", "get_refreshed_fragments");

    function resetLoadingIndicatorAnimation(loadingIndicator) {
        loadingIndicator.removeClass('active').removeClass('load-complete');
        loadingIndicator.find('.tick').css({
            'opacity': 0,
            'transform': ''
        });
    }

    function startLoadingIndicatorAnimation(loadingIndicator) {
        resetLoadingIndicatorAnimation(loadingIndicator);
        loadingIndicator.addClass('active');
    }

    function endLoadingIndicatorAnimation(loadingIndicator, success) {
        if (!success) {
            resetLoadingIndicatorAnimation(loadingIndicator);
            return;
        }
        loadingIndicator.addClass('load-complete');
        gsap.fromTo(
            loadingIndicator.find('.tick'),
            {
                opacity: 0,
            },
            {
                opacity: 1,
                duration: 0.2
            }
        );
        gsap.fromTo(
            loadingIndicator.find('.tick'),
            {
                scale: 1,
            },
            {
                scale: 1.15,
                ease: "elastic.out",
                duration: 0.8
            }
        );
        setTimeout(function(){
            loadingIndicator.removeClass('active').removeClass('load-complete');
        }, 500);
    }

    function reloadCart(loadingIndicator) {
        $.post(getRefreshedFragmentsUrl, function (response, status) {
            endLoadingIndicatorAnimation(loadingIndicator, true);
            $(".woocommerce.widget_shopping_cart").html(response.fragments["div.widget_shopping_cart_content"]);
            if (response.fragments) {
                jQuery.each(response.fragments, function (key, value) {
                    jQuery(key).replaceWith(value);
                });
            }
            jQuery("body").trigger("wc_fragments_refreshed");
        });
    }

    $('.custom-linked-products-table.custom-linked-products-ajax-add-to-cart .single_add_to_cart_button').click(function (e) {
        e.preventDefault();
        var id = $(this).attr('value');
        var data = {
            product_id: id,
            quantity: $(this).parent().find('.quantity input').val()
        };
        var loadingIndicator = $(this).parent().next();
        startLoadingIndicatorAnimation(loadingIndicator);
        $.post(addToCartUrl, data, function (response) {
            if (!response) {
                return;
            }
            if (response.error) {
                endLoadingIndicatorAnimation(loadingIndicator, false);
                alert("Nem sikerült a kosárba helyezni a kívánt terméket!");
                console.log("add-to-cart error");
                return;
            }

            console.log("add-to-cart success");

            reloadCart(loadingIndicator);
        });

    });
});