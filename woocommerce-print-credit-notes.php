<?php
/**
 * Plugin Name: WooCommerce Print Credit Notes
 * Plugin URI: http://git.githost.de/wordpress/woocommerce-print-credit-notes
 * Description: This plugin provides a simple way to print credit notes. It requires the plugin "WooCommerce Print Invoices & Delivery Notes" to work.
 * Author: Adrian Moerchen
 * Author URI: http://www.scrobble.me
 * Version: 1.1
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */


register_activation_hook(__FILE__, 'wpcn_table_install');
add_action('woocommerce_init', 'wpcn_add_template_type', 100);
add_action('woocommerce_admin_order_actions_end', 'wpcn_add_listing_actions', 100);
add_filter('wcdn_document_title', 'wpcn_get_document_title', 100);
add_filter('wcdn_order_info_fields', 'wpcn_get_order_info_fields', 1, 2);

function wpcn_table_install() {
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

function wpcn_get_credit_note_number_and_date($order_id) {
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

function wpcn_add_template_type() {
    global $wcdn;
    $wcdn->print->template_types = array_merge($wcdn->print->template_types, array('credit-note'));
}

function wpcn_add_listing_actions($order) {

    if ($order->status != 'refunded' && $order->status != 'cancelled') {
        return;
    }
    ?>

    <a href="<?php echo wcdn_get_print_link($order->id, 'credit-note'); ?>"
       class="button tips print-preview-button credit-note" target="_blank"
       alt="<?php esc_attr_e('Print Credit Note', 'woocommerce-print-credit-notes'); ?>"
       data-tip="<?php esc_attr_e('Print Credit Note', 'woocommerce-print-credit-notes'); ?>">
        <span><?php _e('Print Credit Note', 'woocommerce-print-credit-notes'); ?></span>
        <img style="width: 18px; padding-top: 3px; padding-left: 1px;"
             src="<?php echo plugin_dir_url(__FILE__) . 'print-credit-note.png' ?>"
             alt="<?php esc_attr_e('Print Credit Note', 'woocommerce-print-credit-notes'); ?>" width="14">
    </a>
<?php
}

function wpcn_get_document_title($title) {
    if (wcdn_get_template_type() == 'credit-note') {
        return __('Credit Note', 'woocommerce-print-credit-notes');
    }
    return $title;
}

function wpcn_get_order_info_fields($fields, $order) {
    $credit_note_number = wpcn_get_credit_note_number_and_date($order->id);
    if (wcdn_get_template_type() == 'credit-note') {
        $fields = array_merge(array(
            array("label" => __('Credit Note Nr.', 'woocommerce-print-credit-notes'), "value" => '#' . $credit_note_number->credit_note_number),
            array("label" => __('Credit Note Date', 'woocommerce-print-credit-notes'), "value" => date_i18n(get_option('date_format'), strtotime($credit_note_number->date)))
        ), $fields);
    }
    return $fields;
}