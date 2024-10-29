function awwlm_init_wishlist_popup() {
    if (jQuery(".awwlm-add-to-wishlist-wrap").length && !jQuery("#awwlm-popup-message").length) {
        var t = jQuery("<div>").attr("id", "awwlm-message"),
        a = jQuery("<div>").attr("id", "awwlm-popup-message").html(t).hide();
        jQuery("body").prepend(a)
    }
}
/*
function awwlm_show_popup(t) {
    var a = jQuery("#awwlm-popup-message"),
    e = jQuery("#awwlm-message");
    e.html(t),
    a.css("margin-left", "-" + jQuery(a).width() + "px").fadeIn(),
    window.setTimeout(function() {
        a.fadeOut(),
        e.html("")
    },
    3e3)
}*/
function awwlm_show_popup(message) {
  var popupwrap = jQuery( '#awwlm-popup-message' ),
        msg = jQuery( '#awwlm-message' ),
        timeout = 3000;

    msg.html( message );
    popupwrap.css( 'margin-left', '-' + jQuery( popupwrap ).width() + 'px' ).fadeIn();
    window.setTimeout( function() {
        popupwrap.fadeOut();
        msg.html('');
    }, timeout );
}

function check_cookie_enabled() {
    if (navigator.cookieEnabled) return !0;
    document.cookie = "cookietest=1";
    var t = -1 !== document.cookie.indexOf("cookietest=");
    return document.cookie = "cookietest=1; expires=Thu, 01-Jan-1970 00:00:01 GMT",
    t
}
awwlm_init_wishlist_popup(),
jQuery(".awwlm_popup_login").magnificPopup({
    items: {
        src: AWWLMSettings.strings.login_msg,
        type: "inline"
    }
}),
jQuery(document).on("click", ".awwlm_add_to_wishlist", function(t) {
    t.preventDefault();
    var a = jQuery(this).attr("data-product-id"),
    e = jQuery(this).attr("data-original-product-id"),
    i = ".awwlm_add_to_wishlist_" + e,
    r = jQuery(this),
    o = r.parents(".awwlm-add-to-wishlist-wrap").siblings("form.cart[method=post]").serialize();
    data = {
        action: "awwlm_add_to_wishlist",
        productID: a,
        productOriginal: e,
        wishlistID: "default",
        frmData: o
    },
    check_cookie_enabled() ? jQuery.ajax({
        url: AWWLMSettings.ajaxurl,
        type: "POST",
        data: data,
        dataType: "json",
        beforeSend: function() {
            r.addClass("awwlm_loading")
        },
        success: function(t) {
            r.removeClass("awwlm_loading"),
            "" != t.after_added_link && 1 == t.success && jQuery(i).html("").html(JSON.parse(t.after_added_link)),
            t.success_popup && awwlm_show_popup(t.message),
            1 == t.success && "yes" == t.success_redirect && (url = t.wishlist_url, window.location.href = url),
            1 == t.success && jQuery(i).attr("data-exists", t.wishlist_id)
        }
    }) : window.alert("We are sorry, this feature works only if cookies on your browser are enabled.")
}),
jQuery(document).on("click", ".awwlm_remove_wishlist", function(t) {
    t.preventDefault();
    var a = jQuery(this).attr("data-product-id"),
    e = jQuery(this).attr("data-original-product-id"),
    i = ".awwlm_add_to_wishlist_" + e,
    r = jQuery(this).parent(".awwlm-add-button").parent(i).attr("data-exists"),
    o = jQuery(this);
    data = {
        action: "awwlm_remove_wishlist",
        productID: a,
        productOriginal: e,
        wishlistID: r
    },
    jQuery.ajax({
        url: AWWLMSettings.ajaxurl,
        type: "POST",
        data: data,
        beforeSend: function() {
            o.addClass("awwlm_loading")
        },
        success: function(t) {
            o.removeClass("awwlm_loading"),
            1 == t.success && ("" != t.after_remove_link && jQuery(i).html("").html(JSON.parse(t.after_remove_link)), t.success_popup && awwlm_show_popup(t.message), jQuery(i).attr("data-exists", 0))
        }
    })
});
var currentRequest = null;
function awwlm_variation_check() {
    jQuery(".variations_form").on("woocommerce_variation_select_change", function() {
        var t = jQuery(this).attr("data-product_id");
        jQuery(".awwlm_add_to_wishlist_" + t).find(".awwlm_add_to_wishlist ").attr("data-product-id", t),
        awwlm_check_variation(t, t, jQuery(".awwlm_add_to_wishlist_" + t).attr("data-exists"))
    }),
    jQuery(".single_variation_wrap").on("show_variation", function(t, a) {
        var e = jQuery(this).parent(".variations_form").attr("data-product_id"),
        i = a.variation_id,
        r = jQuery(".awwlm_add_to_wishlist_" + e).attr("data-exists");
        jQuery(".awwlm_add_to_wishlist_" + e).find(".awwlm_add_to_wishlist ").attr("data-product-id", i),
        jQuery(".awwlm_add_to_wishlist_" + e).find(".awwlm_add_to_wishlist ").addClass("awwlm_loading"),
        awwlm_check_variation(e, i, r)
    })
}
function awwlm_check_variation(t, a, e) {
    var i = ".awwlm_add_to_wishlist_" + t;
    data = {
        action: "awwlm_variation_wishlist",
        productID: a,
        productOriginal: t,
        wishlistID: e
    },
    dat = jQuery(".awwlm_add_to_wishlist_" + t).find(".awwlm_add_to_wishlist "),
    currentRequest = jQuery.ajax({
        url: AWWLMSettings.ajaxurl,
        type: "POST",
        data: data,
        dataType: "json",
        beforeSend: function() {
            null != currentRequest && currentRequest.abort(),
            dat.addClass("awwlm_loading")
        },
        success: function(t) {
            dat.removeClass("awwlm_loading"),
            "" != t.after_added_link && jQuery(i).html("").html(JSON.parse(t.after_added_link)),
            jQuery(i).attr("data-exists", t.wishlist_id)
        }
    })
}
awwlm_variation_check();
var $supports_html5_storage = !0,
hash_key = AWWLMSettings.hash_key;
try {
    $supports_html5_storage = "sessionStorage" in  window && null !== window.sessionStorage,
    window.sessionStorage.setItem("ti", "test"),
    window.sessionStorage.removeItem("ti"),
    window.localStorage.setItem("ti", "test"),
    window.localStorage.removeItem("ti")
} catch(t) {
    $supports_html5_storage = !1
}
function awwlm_get_wishlist_data() {
    if ($supports_html5_storage && localStorage.getItem(hash_key)) {
        var t = jQuery.parseJSON(localStorage.getItem(hash_key));
        console.log(t)
    }
}
awwlm_get_wishlist_data(),
cart_redirect_after_add = "undefined" != typeof wc_add_to_cart_params && null !== wc_add_to_cart_params ? wc_add_to_cart_params.cart_redirect_after_add : "",
jQuery(document.body).on("adding_to_cart", function(t, a, e) {
    void 0 !== a && void 0 !== e && a.closest(".awwlm-table-collection").length && (e.remove_from_wishlist_after_add_to_cart = a.closest("[data-row-id]").data("row-id"), e.wishlist_id = a.closest(".awwlm-table-collection").data("id"), "undefined" != typeof wc_add_to_cart_params && (wc_add_to_cart_params.cart_redirect_after_add = AWWLMSettings.redirect_to_cart), "undefined" != typeof awwlm_general && (awwlm_general.cart_redirect = isTrue(AWWLMSettings.redirect_to_cart)))
}),
jQuery(document).on("added_to_cart", function(t, a, e, i) {
    if (void 0 !== i && i.closest(".awwlm-table-collection").length) {
        "undefined" != typeof wc_add_to_cart_params && (wc_add_to_cart_params.cart_redirect_after_add = cart_redirect_after_add),
        "undefined" != typeof awwlm_general && (awwlm_general.cart_redirect = isTrue(cart_redirect_after_add));
        var r = i.closest("[data-row-id]");
        i.removeClass("added"),
        r.find(".added_to_cart").remove(),
        AWWLMSettings.remove_from_wishlist_after_add_to_cart && r.remove()
    }
}),
jQuery(document.body).on("added_to_cart", function(t, a, e, i) {
    var r = jQuery(".woocommerce-message");
    0 === r.length ? jQuery(".awwlm-container-wishlistlisting").prepend(AWWLMSettings.strings.added_to_cart_message) : r.fadeOut(300, function() {
        jQuery(this).replaceWith(AWWLMSettings.strings.added_to_cart_message).fadeIn()
    })
}),
jQuery(document).on("click", ".awwlm-remove", function(t) {
    var a = jQuery(this),
    e = a.closest("[data-row-id]").data("row-id"),
    i = a.closest(".awwlm-table-collection").data("id"),
    r = a.closest("[data-row-id]");
    data = {
        action: "awwlm_remove_added_wishlist_page",
        product: e,
        wishlist: i
    },
    jQuery.ajax({
        url: AWWLMSettings.ajaxurl,
        type: "POST",
        dataType: "json",
        data: data,
        beforeSend: function() {
            a.addClass("awwlm_loading")
        },
        success: function(t) {
            a.removeClass("awwlm_loading"),
            r.remove(),
            "" != t.message && jQuery(".awwlm-wishlist-message").html(t.message)
        }
    })
}),
jQuery(document).on("click", ".copy-target", function(t) {
    t.preventDefault();
    var a = jQuery("<input>");
    jQuery("body").append(a),
    a.val(jQuery(this).attr("href")).select(),
    document.execCommand("copy"),
    a.remove()
}),
jQuery(document).on("acoqvw_quickview_loaded", awwlm_variation_check),
jQuery(document.body).on("quick_view_pro:open_complete", awwlm_variation_check);



/* save for later */

jQuery(document).on( 'click', '.awwlm-save-later-btn .button1.add', function( e ) {

    e.preventDefault();
    var dat = jQuery(this);
    var product_id = dat.data('id');
    var cart_key = dat.data('key');

    jQuery('.shop_table.cart, .updating, .cart_totals').block({
        message: null,
        overlayCSS: {
            background: "#fff",
            backgroundSize: "16px 16px", opacity: .6
        }
    });

    data = {
        product_id: product_id,
        cart_key: cart_key,
        action: 'awwlm_action_add_to_savelist'
    };

    jQuery.ajax({
      url: AWWLMSettings.ajaxurl,
      type: 'POST',
      data: data,
      dataType: 'json',
      beforeSend: function () {
        jQuery('.woocommerce-message').fadeOut('fast');
        dat.addClass('loading');
      },
      success: function(response) {
        dat.removeClass('loading');
        jQuery('.shop_table.cart, .updating, .cart_totals').unblock();

        if(jQuery("#awwlm-savelater-wrap").length == 0) {
          window.location.replace(AWWLMSettings.carturl);
        }

        jQuery('#awwlm-savelater-wrap').html('').html(JSON.parse(response.save_list));
        setTimeout(function(){
          jQuery("[name='update_cart']").removeAttr('disabled').trigger('click');
        }, 300);

      }

    });

});



jQuery(document).on( 'click', '.awwlm-save-later-btn .button2.remove', function( e ) {

    e.preventDefault();
    var dat = jQuery(this);
    var product_id = dat.data('id');
    var list_id = dat.data('list-id');

    jQuery('.shop_table.cart, .updating, .cart_totals').block({
        message: null,
        overlayCSS: {
            background: "#fff",
            backgroundSize: "16px 16px", opacity: .6
        }
    });

    data = {
        product_id: product_id,
        list_id: list_id,
        action: 'awwlm_action_remove_savelist'
    };

    jQuery.ajax({
      url: AWWLMSettings.ajaxurl,
      type: 'POST',
      data: data,
      dataType: 'json',
      beforeSend: function () {
        jQuery('.woocommerce-message').fadeOut('fast');
        dat.addClass('loading');
      },
      success: function(response) {
        dat.removeClass('loading');
        jQuery('#awwlm-savelater-wrap').html('').html(JSON.parse(response.save_list));
        jQuery('.shop_table.cart, .updating, .cart_totals').unblock();
          setTimeout(function(){
            jQuery("[name='update_cart']").removeAttr('disabled').trigger('click');
          }, 300);
      }

    });

});


jQuery(document).on( 'click', '.awwlm-button.awwlm-add-to-cart', function( e ) {

    e.preventDefault();
    var dat = jQuery(this);
    var product_id = dat.data('id');
    var list_id = dat.data('list-id');

    jQuery('.shop_table.cart, .updating, .cart_totals').block({
        message: null,
        overlayCSS: {
            background: "#fff",
            backgroundSize: "16px 16px", opacity: .6
        }
    });

    data = {
        product_id: product_id,
        list_id: list_id,
        action: 'awwlm_action_addcart_savelist'
    };

    jQuery.ajax({
      url: AWWLMSettings.ajaxurl,
      type: 'POST',
      data: data,
      dataType: 'json',
      beforeSend: function () {
        jQuery('.woocommerce-message').fadeOut('fast');
        dat.addClass('loading');
      },
      success: function(response) {
        dat.removeClass('loading');
        jQuery('#awwlm-savelater-wrap').html('').html(JSON.parse(response.save_list));
        jQuery('.shop_table.cart, .updating, .cart_totals').unblock();
        if(jQuery(".cart-empty.woocommerce-info").length != 0) {
          window.location.replace(AWWLMSettings.carturl);
        }

        // jQuery( '.woocommerce-notices-wrapper').html( AWWLMSettings.strings.added_to_cart_message );
        setTimeout(function(){
          jQuery("[name='update_cart']").removeAttr('disabled').trigger('click');
        }, 300);

        // jQuery(document.body).trigger('wc_update_cart');

      }

    });

});
