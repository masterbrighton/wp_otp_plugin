jQuery(document).ready(function ($) {
    // Validate phone number
    function isValidPhone(phone) {
        const phoneRegex = /^\+?[1-9]\d{1,14}$/; // E.164 format
        return phoneRegex.test(phone);
    }

    // Send OTP
    $('#send-otp').on('click', function () {
        const phone = $('#phone').val();
        if (!phone || !isValidPhone(phone)) {
            $('#otp-status').html('<div class="error">Please enter a valid phone number.</div>');
            return;
        }
        $('#otp-status').html('');

        $.post(ajax_object.ajax_url, {
            action: 'send_otp',
            phone: phone
        }, function (response) {
            if (response.success) {
                $('#otp-status').html('<div class="success">' + response.data.message + '</div>');
                $('#phone-section').hide();
                $('#otp-section').show();
            } else {
                $('#otp-status').html('<div class="error">' + response.data.message + '</div>');
            }
        }).fail(function () {
            $('#otp-status').html('<div class="error">An error occurred. Please try again later.</div>');
        });
    });

    // Verify OTP
    $('#verify-otp').on('click', function () {
        const otp = $('#otp').val();
        if (!otp) {
            $('#otp-status').html('<div class="error">Please enter the OTP.</div>');
            return;
        }
        $('#otp-status').html('');

        $.post(ajax_object.ajax_url, {
            action: 'verify_otp',
            otp: otp
        }, function (response) {
            if (response.success) {
                $('#otp-status').html('<div class="success">' + response.data.message + '</div>');
            } else {
                $('#otp-status').html('<div class="error">' + response.data.message + '</div>');
            }
        }).fail(function () {
            $('#otp-status').html('<div class="error">An error occurred. Please try again later.</div>');
        });
    });

    // Change Phone
    $('#change-phone').on('click', function () {
        $('#otp-section').hide();
        $('#phone-section').show();
        $('#otp-status').html('');
    });
});
