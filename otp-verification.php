<?php
/**
 * Plugin Name: Twilio OTP Verification
 * Description: A custom plugin for OTP verification using Twilio API.
 * Version: 1.0
 * Author: Neticat
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Include Twilio SDK
require_once plugin_dir_path(__FILE__) . 'lib/Twilio/autoload.php';

// Enqueue Scripts and Styles
add_action('wp_enqueue_scripts', 'twilio_otp_enqueue_scripts');
function twilio_otp_enqueue_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script(
        'twilio-otp-script',
        plugins_url('js/twilio-otp.js', __FILE__),
        ['jquery'],
        null,
        true
    );

    wp_localize_script('twilio-otp-script', 'ajax_object', [
        'ajax_url' => admin_url('admin-ajax.php'),
    ]);
}

// Shortcode for the OTP Form
add_shortcode('twilio_otp_form', 'twilio_otp_form_shortcode');
function twilio_otp_form_shortcode() {
    ob_start();
    ?>
    <style>
        #twilio-otp-form {
            max-width: 380px;
            margin: 0 auto;
            padding: 20px;
            border-radius: 5px;
        }
        #twilio-otp-form input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        #twilio-otp-form button {
            width: 225px;
            padding: 10px;
            background-color: #0073aa;
            color: #fff;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        #twilio-otp-form button:hover {
            background-color: #005177;
        }
        #twilio-otp-form .success,
        #twilio-otp-form .error {
            margin-top: 10px;
            padding: 10px;
            border-radius: 3px;
            font-size: 14px;
        }
        #twilio-otp-form .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        #twilio-otp-form .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
    <form id="twilio-otp-form">
        <div id="phone-section">
            <label for="phone">Phone Number:</label>
            <input type="text" id="phone" name="phone" placeholder="Wprowadź swój numer telefonu" required>
            <button type="button" id="send-otp">Wyślij kod</button>
        </div>
        
        <div id="otp-section" style="display: none;">
            <label for="otp">Wprowadź kod SMS:</label>
            <input type="text" id="otp" name="otp" placeholder="np. 533456" required>
            <button type="button" id="verify-otp">Sprawdź kod</button>
            <button type="button" id="change-phone">Zmień numer telefonu</button>
        </div>
        
        <div id="otp-status"></div>
    </form>
    <?php
    return ob_get_clean();
}

// AJAX Handler for Sending OTP
add_action('wp_ajax_send_otp', 'twilio_send_otp');
add_action('wp_ajax_nopriv_send_otp', 'twilio_send_otp');
function twilio_send_otp() {
    $sid    = 'SID';
    $token  = 'TOKEN';

    try {
        $twilio = new \Twilio\Rest\Client($sid, $token);
    } catch (Exception $e) {
        wp_send_json_error(['message' => 'Failed to initialize Twilio Client.']);
        wp_die();
    }

    $phone = sanitize_text_field($_POST['phone']);
    if (empty($phone) || !preg_match('/^\+?[1-9]\d{1,14}$/', $phone)) {
        wp_send_json_error(['message' => 'Invalid phone number format.']);
        wp_die();
    }

    $otp = rand(100000, 999999);
    session_start();
    $_SESSION['twilio_otp'] = $otp;

    try {
        $message = $twilio->messages->create(
            $phone,
            [
                'from' => '+17756373692',
                'body' => "Your OTP is: $otp"
            ]
        );
        wp_send_json_success(['message' => 'OTP sent successfully!']);
    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()]);
    }

    wp_die();
}

// AJAX Handler for Verifying OTP
add_action('wp_ajax_verify_otp', 'twilio_verify_otp');
add_action('wp_ajax_nopriv_verify_otp', 'twilio_verify_otp');
function twilio_verify_otp() {
    session_start();
    $otp = sanitize_text_field($_POST['otp']);
    
    if (isset($_SESSION['twilio_otp']) && $_SESSION['twilio_otp'] == $otp) {
        unset($_SESSION['twilio_otp']);
        wp_send_json_success(['message' => 'OTP verified successfully!']);
    } else {
        wp_send_json_error(['message' => 'Invalid OTP.']);
    }

    wp_die();
}
