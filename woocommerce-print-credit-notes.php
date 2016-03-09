<?php
/**
 * Plugin Name: WooCommerce Print Credit Notes
 * Plugin URI: https://github.com/scrobbleme/woocommerce-print-credit-notes
 * Description: This plugin provides a simple way to print credit notes. It requires the plugin "WooCommerce Print Invoices & Delivery Notes" to work.
 * Author: Adrian Moerchen
 * Author URI: http://adrian.moe/rchen/
 * Version: 1.2.1
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */


register_activation_hook(__FILE__, 'wpcn_table_install');

add_action('wcdn_template_registration', 'wpcn_add_template_type', 100, 1);
add_action('admin_enqueue_scripts', 'wpcn_admin_enqueue_scripts');

add_filter('wcdn_document_title', 'wpcn_get_document_title', 100);
add_filter('wcdn_order_info_fields', 'wpcn_get_order_info_fields', 1, 2);


function wpcn_admin_enqueue_scripts()
{
    wp_enqueue_style('woocommerce-print-credit-notes-css', plugins_url('styles.css', __FILE__), false, '1.2');
}


function wpcn_table_install()
{
    global $wpdb;
    $table_name = $wpdb->prefix . "woocommerce_credit_note";
    $sql = 'CREATE TABLE ' . $table_name . ' ('
        . 'credit_note_number int(11) NOT NULL AUTO_INCREMENT,'
        . 'order_id int(11) NOT NULL,'
        . 'date timestamp NULL DEFAULT CURRENT_TIMESTAMP,'
        . 'PRIMARY KEY (credit_note_number),'
        . 'UNIQUE KEY order_id_UNIQUE (order_id)'
        . ') AUTO_INCREMENT=1;';

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    add_option("wccn_db_version", '1.0');
}

function wpcn_get_credit_note_number_and_date($order_id)
{
    global $wpdb;
    $table_name = $wpdb->prefix . "woocommerce_credit_note";
    $credit_note_number = $wpdb->get_row($wpdb->prepare('SELECT credit_note_number, date FROM ' . $table_name . ' WHERE order_id = %d;', $order_id));
    if (count($credit_note_number) == 0) {
        $wpdb->insert(
            $table_name,
            array(
                'order_id' => $order_id
            ),
            array('%d')
        );
        return wpcn_get_credit_note_number_and_date($order_id);
    }
    return $credit_note_number;
}

function wpcn_add_template_type($templates)
{
    $templates[] = array(
        'type' => 'credit-note',
        'labels' => array(
            'name' => __('Credit Note', 'woocommerce-print-credit-notes'),
            'name_plural' => __('Credit Notes', 'woocommerce-print-credit-notes'),
            'print' => __('Print Credit Note', 'woocommerce-print-credit-notes'),
            'print_plural' => __('Print Credit Notes', 'woocommerce-print-credit-notes'),
            'message' => __('Credit Note created.', 'woocommerce-print-credit-notes'),
            'message_plural' => __('Credit Note created.', 'woocommerce-print-credit-notes'),
            'setting' => __('Enable Credit Notes', 'woocommerce-print-credit-notes')
        )
    );
    return $templates;
}

function wpcn_get_document_title($title)
{
    if (wcdn_get_template_type() == 'credit-note') {
        return __('Credit Note', 'woocommerce-print-credit-notes');
    }
    return $title;
}

function wpcn_get_order_info_fields($fields, $order)
{
    $credit_note_number = wpcn_get_credit_note_number_and_date($order->id);
    if (wcdn_get_template_type() == 'credit-note') {
        $fields = array_merge(array(
            array("label" => __('Credit Note Nr.', 'woocommerce-print-credit-notes'), "value" => '#' . $credit_note_number->credit_note_number),
            array("label" => __('Credit Note Date', 'woocommerce-print-credit-notes'), "value" => date_i18n(get_option('date_format'), strtotime($credit_note_number->date)))
        ), $fields);
    }
    return $fields;
}