<?php
/**
 * Plugin Name: WooCommerce Print Credit Notes
 * Plugin URI: http://blog.scrobble.me/tag/woocommerce-print-credit-notes
 * Description: This plugin provides a simple way to print credit notes. It requires the plugin "WooCommerce Print Invoices & Delivery Notes" to work.
 * Author: Adrian Moerchen
 * Author URI: http://www.scrobble.me
 * Version: 1.0
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */


add_action('after_switch_theme', 'wpcn_table_install');
add_action('woocommerce_admin_order_actions_end', 'wpcn_add_listing_actions', 100);
add_filter('woocommerce_locate_template', 'wpcn_locate_template', 10, 3);

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

function wccn_get_credit_note_number_and_date($order_id)
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
        return wccn_get_credit_note_number_and_date($order_id);
    }
    return $credit_note_number;
}

function wpcn_add_listing_actions($order)
{
    if ($order->status != 'refunded' && $order->status != 'cancelled') {
        return;
    }
    ?>
    <a href="<?php echo wp_nonce_url(admin_url('admin-ajax.php?action=generate_print_content&template_type=credit-note&order_id=' . $order->id), 'generate_print_content'); ?>"
       class="button tips print-preview-button" target="_blank"
       title="<?php esc_attr_e('Print Credit Note', 'woocommerce-print-credit-notes'); ?>"
       data-tip="<?php esc_attr_e('Print Credit Note', 'woocommerce-print-credit-notes'); ?>">
        <span><?php _e('Print Credit Note', 'woocommerce-print-credit-notes'); ?></span>
        <img
            src="<?php echo plugin_dir_url(__FILE__) . 'print-credit-note.png' ?>"
            alt="<?php esc_attr_e('Print Credit Note', 'woocommerce-print-credit-notes'); ?>" width="14">
    </a>
<?php
}

function wpcn_locate_template($template, $template_name, $template_path)
{
    if ($template_name == 'print-credit-note.php') {
        return plugin_dir_path(__FILE__) . 'templates/print-credit-note.php';
    }
    return $template;
}


