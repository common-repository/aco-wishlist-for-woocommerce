// Deactivation Form
jQuery(document).ready(function () {

    jQuery(document).on("click", function(e) {
        let popup = document.getElementById('awwlm-survey-form');
        let overlay = document.getElementById('awwlm-survey-form-wrap');
        let openButton = document.getElementById('deactivate-aco-wishlist-for-woocommerce');
        if(e.target.id == 'awwlm-survey-form-wrap'){
            awwlmClose();
        }
        if(e.target === openButton){
            e.preventDefault();
            popup.style.display = 'block';
            overlay.style.display = 'block';
        }
        if(e.target.id == 'awwlm_skip'){
            e.preventDefault();
            let urlRedirect = document.querySelector('a#deactivate-aco-wishlist-for-woocommerce').getAttribute('href');
            window.location = urlRedirect;
        }
        if(e.target.id == 'awwlm_cancel'){
            e.preventDefault();
            awwlmClose();
        }
    });

	function awwlmClose() {
		let popup = document.getElementById('awwlm-survey-form');
        let overlay = document.getElementById('awwlm-survey-form-wrap');
		popup.style.display = 'none';
		overlay.style.display = 'none';
		jQuery('#awwlm-survey-form form')[0].reset();
		jQuery("#awwlm-survey-form form .awwlm-comments").hide();
		jQuery('#awwlm-error').html('');
	}

    jQuery("#awwlm-survey-form form").on('submit', function(e) {
        e.preventDefault();
        let valid = awwlmValidate();
		if (valid) {
            let urlRedirect = document.querySelector('a#deactivate-aco-wishlist-for-woocommerce').getAttribute('href');
            let form = jQuery(this);
            let serializeArray = form.serializeArray();
            let actionUrl = 'https://feedback.acowebs.com/plugin.php';
            jQuery.ajax({
                type: "post",
                url: actionUrl,
                data: serializeArray,
                contentType: "application/javascript",
                dataType: 'jsonp',
                beforeSend: function () {
        					jQuery('#awwlm_deactivate').prop( 'disabled', 'disabled' );
        				},
                success: function(data)
                {
                    window.location = urlRedirect;
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    window.location = urlRedirect;
                }
            });
        }
    });

    jQuery('#awwlm-survey-form .awwlm-comments textarea').on('keyup', function () {
		awwlmValidate();
	});

    jQuery("#awwlm-survey-form form input[type='radio']").on('change', function(){
        awwlmValidate();
        let val = jQuery(this).val();
        if ( val == 'I found a bug' || val == 'Plugin suddenly stopped working' || val == 'Plugin broke my site' || val == 'Other' || val == 'Plugin doesn\'t meets my requirement' ) {
            jQuery("#awwlm-survey-form form .awwlm-comments").show();
        } else {
            jQuery("#awwlm-survey-form form .awwlm-comments").hide();
        }
    });

    function awwlmValidate() {
		let error = '';
		let reason = jQuery("#awwlm-survey-form form input[name='Reason']:checked").val();
		if ( !reason ) {
			error += 'Please select your reason for deactivation';
		}
		if ( error === '' && ( reason == 'I found a bug' || reason == 'Plugin suddenly stopped working' || reason == 'Plugin broke my site' || reason == 'Other' || reason == 'Plugin doesn\'t meets my requirement' ) ) {
			let comments = jQuery('#awwlm-survey-form .awwlm-comments textarea').val();
			if (comments.length <= 0) {
				error += 'Please specify';
			}
		}
		if ( error !== '' ) {
			jQuery('#awwlm-error').html(error);
			return false;
		}
		jQuery('#awwlm-error').html('');
		return true;
	}

});
