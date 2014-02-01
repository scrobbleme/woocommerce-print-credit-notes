<!DOCTYPE html>
<html class="<?php echo wcdn_get_template_type(); ?>">
<head>
    <meta charset="utf-8">
    <title><?php _e('Credit Note', 'woocommerce-print-credit-notes') ?></title>
    <?php wcdn_head(); ?>
    <link rel="stylesheet" href="<?php wcdn_stylesheet_url('style.css'); ?>" type="text/css" media="screen,print"/>
</head>
<body>
<div id="container">
    <?php wcdn_navigation(); ?>
    <div id="content">
        <div id="page">
            <div id="letter-header">
                <div
                    class="heading"><?php if (wcdn_get_company_logo_id()) : ?><?php wcdn_company_logo(); ?><?php else : ?><?php wcdn_template_title(); ?><?php endif; ?></div>
                <div class="company-info">
                    <div class="company-name"><?php wcdn_company_name(); ?></div>
                    <div class="company-address"><?php wcdn_company_info(); ?></div>
                </div>
            </div>
            <!-- #letter-header -->

            <div id="order-listing">
                <h3><?php _e('Recipient', 'woocommerce-delivery-notes'); ?></h3>

                <div class="shipping-info">
                    <?php wcdn_billing_address(); ?>
                </div>
                <!-- .shipping-info -->
            </div>
            <!-- #order-listing -->

            <ul id="order-info">
                <?php if (wcdn_get_company_logo_id()) : ?>
                    <?php
                    $credit_note_number = wccn_get_credit_note_number_and_date($_GET['order_id']);
                    ?>
                    <li>
                        <h3 class="order-number-label"><?php printf(__('Credit Note Nr. %d', 'woocommerce-print-credit-notes'), $credit_note_number->credit_note_number) ?></h3>

                        <h3 class="order-number-label"><?php printf(__('Date: %s', 'woocommerce-print-credit-notes'), date_i18n(get_option('date_format'), strtotime($credit_note_number->date))) ?></h3>
                    </li>
                <?php endif; ?>
                <li>
                    <h3 class="order-date-label"><?php _e('Order Date', 'woocommerce-delivery-notes'); ?></h3>
                    <span class="order-date"><?php wcdn_order_date(); ?></span>
                </li>
                <li>
                    <h3 class="order-number-label"><?php _e('Order Number', 'woocommerce-delivery-notes'); ?></h3>
                    <span class="order-number"><?php wcdn_order_number(); ?></span>
                </li>
                <li>
                    <h3 class="order-payment-label"><?php _e('Payment Method', 'woocommerce-delivery-notes'); ?></h3>
                    <span class="order-payment"><?php wcdn_payment_method(); ?></span>
                </li>
                <li>
                    <h3 class="order-telephone-label"><?php _e('Email', 'woocommerce-delivery-notes'); ?></h3>
                    <span class="order-payment"><?php wcdn_billing_email(); ?></span>
                </li>
                <li>
                    <h3 class="order-email-label"><?php _e('Phone', 'woocommerce-delivery-notes'); ?></h3>
                    <span class="order-payment"><?php wcdn_billing_phone(); ?></span>
                </li>
            </ul>
            <!-- #order-info -->

            <div id="order-items">
                <table>
                    <thead>
                    <tr>
                        <th class="product-label"><?php _e('Product', 'woocommerce-delivery-notes'); ?></th>
                        <th class="quantity-label"><?php _e('Quantity', 'woocommerce-delivery-notes'); ?></th>
                        <th class="totals-label"><?php _e('Totals', 'woocommerce-delivery-notes'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $items = wcdn_get_order_items();
                    if (sizeof($items) > 0) : foreach ($items as $item) : ?>
                        <tr>
                        <td class="description"><?php echo $item['name']; ?>
                            <?php echo $item['meta']; ?>
                            <dl class="meta">
                                <?php if (!empty($item['sku'])) : ?>
                                    <dt><?php _e('SKU:', 'woocommerce-delivery-notes'); ?></dt>
                                    <dd><?php echo $item['sku']; ?></dd><?php endif; ?>
                                <?php if (!empty($item['weight'])) : ?>
                                    <dt><?php _e('Weight:', 'woocommerce-delivery-notes'); ?></dt>
                                    <dd><?php echo $item['weight']; ?><?php echo get_option('woocommerce_weight_unit'); ?></dd><?php endif; ?>
                            </dl>
                        </td>
                        <td class="quantity"><?php echo $item['quantity']; ?></td>
                        <td class="price"><?php echo $item['price']; ?></td>
                        </tr><?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- #order-items -->

            <div id="order-summary">
                <table>
                    <tfoot>
                    <?php foreach (wcdn_get_order_totals() as $total) : ?>
                        <tr>
                            <th class="description"><?php echo $total['label']; ?></th>
                            <td class="price"><?php echo $total['value']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tfoot>
                </table>
            </div>
            <!-- #order-summery -->

            <div id="order-notes">
                <?php
                $wccn_totals = wcdn_get_order_totals();
                ?>
                <div
                    class="notes-personal"><?php printf(__('The total of %s was approved for payment.', 'woocommerce-print-credit-notes'), $wccn_totals['order_total']['value']) ?></div>
            </div>
            <!-- #order-notes -->

            <?php if (wcdn_get_policies_conditions() || wcdn_get_footer_imprint()) : ?>
                <div id="letter-footer">
                    <div class="imprint"><?php wcdn_footer_imprint(); ?></div>
                </div><!-- #letter-footer -->
            <?php endif; ?>
        </div>
        <!-- #page -->
    </div>
    <!-- #content -->
</div>
<!-- #container -->
</body>
</html>