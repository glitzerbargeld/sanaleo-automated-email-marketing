<?php

/**
 * Plugin Name: Sanaleo Conditional Coupons
 * Description: Adds Coupons based on Customers Purchases
 */

if (!function_exists('write_log')) {

    function write_log($log) {
        if (true === WP_DEBUG) {
            if (is_array($log) || is_object($log)) {
                error_log(print_r($log, true));
            } else {
                error_log($log);
            }
        }
    }
}

function random_str_generator ($len_of_gen_str){
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $var_size = strlen($chars);
    $random_str = '';
    for( $x = 0; $x < $len_of_gen_str; $x++ ) {  
        $random_str= $random_str . $chars[ rand( 0, $var_size - 1 ) ];  
    }
    return $random_str;
}


$dir = plugin_dir_path( __FILE__ );
require_once($dir . 'vendor/autoload.php');


function send_coupon($message, $template_name, $discount_code, $date)
{
    try {
        $mailchimp = new MailchimpTransactional\ApiClient();
        $mailchimp->setApiKey('j6ZwO6da1H1a8OfzqLJB2w');

        $response = $mailchimp->messages->sendTemplate([
            "template_name" => $template_name,
            "message" => $message,
            "send_at" => $date,
            "template_content" => [["name" => "discountcode", "content" => $discount_code]],
        ]);

        print_r($response);
    } catch (Error $e) {
        echo 'Error: ', $e->getMessage(), "\n";
    }
}


function send_promo_mail($message, $template_name, $date)
{
    try {
        $mailchimp = new MailchimpTransactional\ApiClient();
        $mailchimp->setApiKey('j6ZwO6da1H1a8OfzqLJB2w');

        $response = $mailchimp->messages->sendTemplate([
            "template_name" => $template_name,
            "message" => $message,
            "send_at" => $date,
        ]);

        print_r($response);
    } catch (Error $e) {
        echo 'Error: ', $e->getMessage(), "\n";
    }
}


function send_retargeting_mails($order_id) {


    $order = wc_get_order($order_id);
    $customer_email = $order -> get_billing_email();
    $first_name = $order->get_billing_first_name();
    $last_name  = $order->get_billing_last_name();

    $items = $order->get_items();

    foreach ( $items as $item ) {

        $product_id = $item->get_product_id();
        $menge = $item->get_meta('pa_menge');
        $has_10g = FALSE;        

        if ( has_term( 'cbd-blueten', 'product_cat', $product_id )) {

            if($menge = "2g"){
                $has_2g = TRUE; 
            }
            elseif($menge = "5g"){
                $has_5g = TRUE; 
            }
            elseif($menge = "10g"){
                $has_10g = TRUE; 
            }
     
        }

    }

    if($has_2g){

        $two_weeks = time() + (60*60*24*16);
        $two_weeks_converted = date("Y-m-d H:i:s", $two_months);
        $template = "rm-cs-bl-ten2gr";
        $subject = 'Wusstest du schon? Spare 10% beim kauf von 5g statt 2g';
        
        $message = [
            "from_email" => "angebote@sanaleo.com",
            "subject" => $subject,
            "to" => [
                [
                    "email" => $customer_email,
                    "type" => "to"
                ]
            ]
        ];

        send_promo_mail($message, $template, $two_weeks_converted);

    }

    if($has_5g){

        $one_month = time() + (60*60*24*30);
        $one_month_converted = date("Y-m-d H:i:s", $one_month);
        $template = "rm-cs-bl-ten5gr";
        $subject = 'Wusstest du schon? Spare 10% beim kauf von 10g statt 5g';
        
        $message = [
            "from_email" => "angebote@sanaleo.com",
            "subject" => $subject,
            "to" => [
                [
                    "email" => $customer_email,
                    "type" => "to"
                ]
            ]
        ];

        send_promo_mail($message, $template, $one_month_converted);

    }

    if($has_10g) {

        $rand_str = random_str_generator(4); 
        $coupon_name = $menge . '-' . $rand_str . '-' . $first_name[0] . $last_name[0];
        $coupon = array(
            'post_title' => $coupon_name,
            'post_content' => '',
            'post_status' => 'publish',
            'post_author' => 1,
            'post_type' => 'shop_coupon');
            
        $new_coupon_id = wp_insert_post( $coupon );

        update_post_meta( $new_coupon_id, 'discount_type', $discount_type );
        update_post_meta( $new_coupon_id, 'coupon_amount', $amount );
        update_post_meta( $new_coupon_id, 'individual_use', 'no' );
        update_post_meta( $new_coupon_id, 'product_ids', '' );
        update_post_meta( $new_coupon_id, 'exclude_product_ids', '' );
        update_post_meta( $new_coupon_id, 'usage_limit', '' );
        update_post_meta( $new_coupon_id, 'expiry_date', '' );
        update_post_meta( $new_coupon_id, 'apply_before_tax', 'yes' );
        update_post_meta( $new_coupon_id, 'free_shipping', 'no' );

        $two_months = time() + (60*60*24*60);
        $two_months_converted = date("Y-m-d H:i:s", $two_months);
        $template = "rm-cs-bl-ten10gr";
        $subject = 'Dein Gutschein für 10g CBD Blüten';
        
        $message = [
            "from_email" => "angebote@sanaleo.com",
            "subject" => $subject,
            "to" => [
                [
                    "email" => $customer_email,
                    "type" => "to"
                ]
            ]
        ];

        send_coupon($message, $template, $coupon_name, $two_months_converted);
    }

    if($product_id == "7150") {

        $rand_str = random_str_generator(4); 
        $coupon_name = $menge . '-' . $rand_str . '-' . $first_name[0] . $last_name[0];
        $coupon = array(
            'post_title' => $coupon_name,
            'post_content' => '',
            'post_status' => 'publish',
            'post_author' => 1,
            'post_type' => 'shop_coupon');
            
        $new_coupon_id = wp_insert_post( $coupon );

        update_post_meta( $new_coupon_id, 'discount_type', $discount_type );
        update_post_meta( $new_coupon_id, 'coupon_amount', $amount );
        update_post_meta( $new_coupon_id, 'individual_use', 'no' );
        update_post_meta( $new_coupon_id, 'product_ids', '' );
        update_post_meta( $new_coupon_id, 'exclude_product_ids', '' );
        update_post_meta( $new_coupon_id, 'usage_limit', '' );
        update_post_meta( $new_coupon_id, 'expiry_date', '' );
        update_post_meta( $new_coupon_id, 'apply_before_tax', 'yes' );
        update_post_meta( $new_coupon_id, 'free_shipping', 'no' );

        $five_weeks = time() + (60*60*24*35);
        $five_weeks_converted = date("Y-m-d H:i:s", $two_months);
        $template = "rm-cs-bl-tenset";
        $subject = 'Dein Gutschein für 10g CBD Blüten';
        
        $message = [
            "from_email" => "angebote@sanaleo.com",
            "subject" => $subject,
            "to" => [
                [
                    "email" => $customer_email,
                    "type" => "to"
                ]
            ]
        ];

        send_coupon($message, $template, $coupon_name, $five_weeks_converted);

    }
}

add_action( 'woocommerce_order_status_completed', 'send_retargeting_mails', 10, 1);





