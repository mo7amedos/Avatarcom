<?php

return [
    'statuses' => [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'completed' => 'Completed',
        'canceled' => 'Canceled',
        'partial_returned' => 'Partial returned',
        'returned' => 'Returned',
    ],
    'return_statuses' => [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'completed' => 'Completed',
        'canceled' => 'Canceled',
    ],
    'menu' => 'Orders',
    'create' => 'Create an order',
    'cancel_error' => 'The order is delivering or not completed',
    'cancel_success' => 'You do cancel the order successful',
    'return_error' => 'The order is delivering or not completed',
    'return_success' => 'Requested product(s) return successfully!',
    'incomplete_order' => 'Incomplete orders',
    'order_id' => 'Order ID',
    'product_id' => 'Product ID',
    'customer_label' => 'Customer',
    'tax_amount' => 'Tax Amount',
    'shipping_amount' => 'Shipping amount',
    'payment_method' => 'Payment method',
    'payment_status_label' => 'Payment status',
    'manage_orders' => 'Manage orders',
    'order_intro_description' => 'Once your store has orders, this is where you will process and track those orders.',
    'create_new_order' => 'Create a new order',
    'manage_incomplete_orders' => 'Manage incomplete orders',
    'incomplete_orders_intro_description' => 'Incomplete order is an order created when a customer adds a product to the cart, proceeds to fill out the purchase information but does not complete the checkout process.',
    'invoice_for_order' => 'Invoice for order',
    'created' => 'Created',
    'created_at' => 'Created at',
    'invoice' => 'Invoice',
    'return' => 'Order Return Request',
    'restock_products' => 'Restock :count product(s)?',
    'is_return' => 'Return checkbox',
    'total_refund_amount' => 'Total refund amount',
    'total_amount_can_be_refunded' => 'Total amount can be refunded',
    'refund_reason' => 'Refund reason',
    'products' => 'product(s)',
    'default' => 'Default',
    'system' => 'System',
    'new_order_from' => 'New order :order_id from :customer',
    'confirmation_email_was_sent_to_customer' => 'The email confirmation was sent to customer',
    'create_order_from_payment_page' => 'Order was created from checkout page',
    'create_order_from_admin_page' => 'Order was created from admin page',
    'order_was_verified_by' => 'Order was verified by %user_name%',
    'new_order' => 'New order :order_id',
    'payment_was_confirmed_by' => 'Payment was confirmed (amount :money) by %user_name%',
    'edit_order' => 'Edit order :code',
    'confirm_order_success' => 'Confirm order successfully!',
    'error_when_sending_email' => 'There is an error when sending email',
    'sent_confirmation_email_success' => 'Sent confirmation email successfully!',
    'order_was_sent_to_shipping_team' => 'Order was sent to shipping team',
    'by_username' => 'by %user_name%',
    'shipping_was_created_from' => 'Shipping was created from order %order_id%',
    'shipping_was_created_from_pos' => 'Shipping was created from POS order %order_id%',
    'shipping_was_canceled_success' => 'Shipping was cancelled successfully!',
    'shipping_was_canceled_by' => 'Shipping was cancelled by %user_name%',
    'update_shipping_address_success' => 'Update shipping address successfully!',
    'order_was_canceled_by' => 'Order was cancelled by %user_name%',
    'order_was_returned_by' => 'Order was returned by %user_name%',
    'confirm_payment_success' => 'Confirm payment successfully!',
    'refund_amount_invalid' => 'Refund amount must be lower or equal :price',
    'number_of_products_invalid' => 'Number of products refund is not valid!',
    'cannot_found_payment_for_this_order' => 'Cannot found payment for this order!',
    'refund_success_with_price' => 'Refund success :price',
    'refund_success' => 'Refund successfully!',
    'order_is_not_existed' => 'Order is not existed!',
    'reorder' => 'Reorder',
    'sent_email_incomplete_order_success' => 'Sent email to remind about incomplete order successfully!',
    'applied_coupon_success' => 'Applied coupon ":code" successfully!',
    'new_order_notice' => 'You have <span class="bold">:count</span> New Order(s)',
    'view_all' => 'View all',
    'view_order' => 'View order',
    'cancel_order' => 'Cancel order',
    'order_canceled' => 'Order canceled',
    'order_was_canceled_at' => 'Order was canceled at',
    'return_order' => 'Return order',
    'order_returned' => 'Order returned',
    'order_was_returned_at' => 'Order was returned at',
    'completed' => 'Completed',
    'uncompleted' => 'Uncompleted',
    'sku' => 'SKU',
    'warehouse' => 'Warehouse',
    'sub_amount' => 'Sub amount',
    'coupon_code' => 'Coupon code: ":code"',
    'shipping_fee' => 'Shipping fee',
    'tax' => 'Tax',
    'refunded_amount' => 'Refunded amount',
    'amount_received' => 'The amount actually received',
    'download_invoice' => 'Download invoice',
    'payment_proof' => 'Payment proof',
    'print_invoice' => 'Print invoice',
    'add_note' => 'Add note...',
    'note_description' => '(from customer at the checkout page)',
    'add_note_helper' => 'Note about order, ex: time or shipping instruction. This note is added by customer at the checkout page, you should not change it.',
    'admin_private_notes' => 'Private notes',
    'admin_private_notes_helper' => 'Note for admin/manager about this order. This note is added by admin/manager, customer cannot see it.',
    'order_was_confirmed' => 'Order was confirmed',
    'confirm_order' => 'Confirm order',
    'confirm' => 'Confirm',
    'order_was_canceled' => 'Order was canceled',
    'pending_payment' => 'Pending payment',
    'payment_was_accepted' => 'Payment :money was accepted',
    'payment_was_refunded' => 'Payment was refunded',
    'confirm_payment' => 'Confirm payment',
    'refund' => 'Refund',
    'all_products_are_not_delivered' => 'All products are not delivered',
    'delivery' => 'Delivery',
    'history' => 'History',
    'order_number' => 'Order number',
    'from' => 'from',
    'status' => 'Status',
    'successfully' => 'Successfully',
    'transaction_type' => 'Transaction\'s type',
    'staff' => 'Staff',
    'refund_date' => 'Refund date',
    'n_a' => 'N\A',
    'payment_date' => 'Payment date',
    'payment_gateway' => 'Payment gateway',
    'transaction_amount' => 'Transaction amount',
    'resend' => 'Resend',
    'default_store' => 'Default store',
    'update_address' => 'Update address',
    'have_an_account_already' => 'Have an account already',
    'dont_have_an_account_yet' => 'Don\'t have an account yet',
    'mark_payment_as_confirmed' => 'Mark <span>:method</span> as confirmed',
    'resend_order_confirmation' => 'Resend order confirmation',
    'resend_order_confirmation_description' => 'Confirmation email will be sent to <strong>:email</strong>?',
    'send' => 'Send',
    'update' => 'Update',
    'cancel_shipping_confirmation' => 'Cancel shipping confirmation?',
    'cancel_shipping_confirmation_description' => 'Cancel shipping confirmation?',
    'cancel_order_confirmation' => 'Cancel order confirmation?',
    'cancel_order_confirmation_description' => 'Are you sure you want to cancel this order? This action cannot rollback',
    'return_order_confirmation' => 'Return order confirmation?',
    'return_order_confirmation_description' => 'Are you sure you want to return this order? This action cannot rollback',
    'confirm_payment_confirmation_description' => 'Processed by <strong>:method</strong>. Did you receive payment outside the system? This payment won\'t be saved into system and cannot be refunded',
    'save_note' => 'Save note',
    'order_note' => 'Order note',
    'order_note_placeholder' => 'Note about order, ex: time or shipping instruction.',
    'order_amount' => 'Order amount',
    'additional_information' => 'Additional information',
    'notice_about_incomplete_order' => 'Notice about incomplete order',
    'notice_about_incomplete_order_description' => 'Remind email about uncompleted order will be send to <strong>:email</strong>?',
    'incomplete_order_description_1' => 'An incomplete order is when a potential customer places items in their shopping cart, and goes all the way through to the payment page, but then doesn\'t complete the transaction.',
    'incomplete_order_description_2' => 'If you have contacted customers and they want to continue buying, you can help them complete their order by following the link:',
    'send_an_email_to_recover_this_order' => 'Send an email to customer to recover this order',
    'see_maps' => 'See maps',
    'one_or_more_products_dont_have_enough_quantity' => 'One or more products don\'t have enough quantity!',
    'cannot_send_order_recover_to_mail' => 'The email could not be found so it can\'t send a recovery email to the customer.',
    'payment_info' => 'Payment Info',
    'payment_method_refund_automatic' => 'Your customer will be refunded using :method automatically.',
    'order' => 'Order',
    'order_information' => 'Order information',
    'create_a_new_product' => 'Create a new product',
    'out_of_stock' => 'Out of stock',
    'products_available' => 'product(s) available',
    'no_products_found' => 'No products found!',
    'note' => 'Note',
    'note_for_order' => 'Note for order...',
    'amount' => 'Amount',
    'add_discount' => 'Add discount',
    'discount' => 'Discount',
    'add_shipping_fee' => 'Add shipping fee',
    'shipping' => 'Shipping',
    'total_amount' => 'Total amount',
    'confirm_payment_and_create_order' => 'Confirm payment and create order',
    'paid' => 'Paid',
    'pay_later' => 'Pay later',
    'customer_information' => 'Customer information',
    'create_new_customer' => 'Create new customer',
    'no_customer_found' => 'No customer found!',
    'customer' => 'Customer',
    'orders' => 'order(s)',
    'shipping_address' => 'Shipping Address',
    'shipping_info' => 'Shipping information',
    'billing_address' => 'Billing Address',
    'see_on_maps' => 'See on maps',
    'name' => 'Name',
    'price' => 'Price',
    'product_name' => 'Product name',
    'total' => 'Total',
    'action' => 'Action',
    'add_product' => 'Add product',
    'enter_free_text' => 'Enter free text',
    'promotion_discount_amount' => 'Promotion amount',
    'add' => 'Add',
    'store' => 'Store',
    'please_choose_product_option' => 'Please choose product option',
    'sku_optional' => 'SKU (optional)',
    'with_storehouse_management' => 'With storehouse management?',
    'quantity' => 'Quantity',
    'allow_customer_checkout_when_this_product_out_of_stock' => 'Allow customer checkout when this product out of stock?',
    'address' => 'Address',
    'phone' => 'Phone',
    'country' => 'Country',
    'state' => 'State',
    'city' => 'City',
    'zip_code' => 'Zip code',
    'discount_based_on' => 'Discount based on',
    'or_coupon_code' => 'Or coupon code',
    'description' => 'Description',
    'how_to_select_configured_shipping' => 'How to select configured shipping?',
    'please_add_customer_information_with_the_complete_shipping_address_to_see_the_configured_shipping_rates' => 'Please add customer information with the complete shipping address to see the configured shipping rates',
    'please_products_and_customer_address_to_see_the_shipping_rates' => 'Please add products and customer information with the complete shipping address to see the configured shipping rates',
    'shipping_method_not_found' => 'Shipping method not found',
    'free_shipping' => 'Free shipping',
    'custom' => 'Custom',
    'email' => 'Email',
    'create_order' => 'Create order',
    'close' => 'Close',
    'confirm_payment_title' => 'Confirm payment is :status for this order',
    'confirm_payment_description' => 'Payment status of the order is :status. Once the order has been created, you cannot change the payment method or status',
    'select_payment_method' => 'Select payment method',
    'cash_on_delivery_cod' => 'Cash on delivery (COD)',
    'bank_transfer' => 'Bank transfer',
    'paid_amount' => 'Paid amount',
    'update_email' => 'Update email',
    'save' => 'Save',
    'cancel' => 'Cancel',
    'create_a_new_order' => 'Create a new order',
    'search_or_create_new_product' => 'Search or create a new product',
    'search_or_create_new_customer' => 'Search or create a new customer',
    'discount_description' => 'Discount description',
    'cant_select_out_of_stock_product' => 'Cannot select out of stock product!',
    'referral' => 'Referral',
    'return_order_unique' => 'Same :attribute already exists in a previous return request.',
    'total_return_amount' => 'Total return amount',
    'change_return_order_status' => 'Change return order status',
    'return_order_approve' => 'Approve',
    'return_order_reject' => 'Reject',
    'return_reason' => 'Return reason',
    'order_return_moderation' => [
        'approve_button' => 'Approve',
        'reject_button' => 'Reject',
        'approve_confirmation_title' => 'Approve return order',
        'approve_confirmation_description' => 'Once you approve this return order, the status will be changed to processing and the customer will be notified. Are you sure you want to approve this return order?',
        'reject_confirmation_title' => 'Reject return order',
        'reject_confirmation_description' => 'Once you reject this return order, the status will be changed to canceled and the customer will be notified. Are you sure you want to reject this return order?',
        'mark_as_completed_button' => 'Mark as completed',
        'mark_as_completed_confirmation_title' => 'Mark return order as completed',
        'mark_as_completed_confirmation_description' => 'Once you mark this return order as completed, the status will be changed to completed and the customer will be notified. Are you sure you want to mark this return order as completed?',
    ],
    'order_return_action' => [
        'created' => 'Created',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'mark_as_completed' => 'Mark as completed',
    ],
    'referral_data' => [
        'ip' => 'IP',
        'landing_domain' => 'Landing domain',
        'landing_page' => 'Landing page',
        'landing_params' => 'Landing params',
        'gclid' => 'Gclid',
        'fclid' => 'Fclid',
        'utm_source' => 'UTM source',
        'utm_campaign' => 'UTM campaign',
        'utm_medium' => 'UTM medium',
        'utm_term' => 'UTM term',
        'utm_content' => 'UTM content',
        'referral' => 'Referral',
        'referrer_url' => 'Referral URL',
        'referrer_domain' => 'Referral domain',
    ],
    'order_address_types' => [
        'shipping_address' => 'Shipping address',
        'billing_address' => 'Billing address',
    ],
    'order_return_reasons' => [
        'damaged' => 'Damaged product',
        'defective' => 'Defective',
        'incorrect_item' => 'Incorrect item',
        'arrived_late' => 'Arrived late',
        'not_as_described' => 'Not as described',
        'no_longer_want' => 'No longer want',
        'other' => 'Other',
    ],
    'order_return_reason' => 'Return reason',
    'notices' => [
        'update_return_order_status_error' => 'Cannot update return order status! Maybe the return order status is not valid.',
        'update_return_order_status_success' => 'Update return order status successfully!',
    ],
    'order_return' => 'Order returns',
    'edit_order_return' => 'Edit order return :code',
    'order_return_items_count' => 'Product item(s)',
    'new_order_notifications' => [
        'new_order' => 'New order',
        'new_order_with_code' => 'New order :code',
        'view' => 'View',
        'description' => ':customer ordered :quantity product(s)',
        'product' => 'product',
        'products' => 'products',
    ],
    'confirm_payment_notifications' => [
        'confirm_payment' => 'Confirm payment',
        'confirm_payment_with_code' => 'Confirm payment :code',
        'description' => 'Order :order Payment was confirmed (amount :amount) by :by',
    ],
    'update_shipping_status_notifications' => [
        'update_shipping_status' => 'Update shipping status',
        'update_shipping_status_with_code' => 'Update shipping status :code',
        'description' => 'Order :order had changed shipping status :description',
        'changed_from_to' => 'from :old_status to :new_status',
        'changed_to' => 'to :status',
    ],
    'cancel_order_notifications' => [
        'cancel_order' => 'Cancel order',
        'cancel_order_with_code' => 'Cancel order :code',
        'description' => 'Order :order was cancelled by custom :customer',
    ],
    'return_order_notifications' => [
        'return_order' => 'Return order',
        'return_order_with_code' => 'Return order :code',
        'description' => ':customer has requested return product(s)',
    ],
    'order_completed_notifications' => [
        'order_completed' => 'Order Completed',
        'order_completed_with_code' => 'Order Completed :code',
        'description' => 'Order :order has been completed',
    ],
    'tax_info' => [
        'name' => 'Tax Information',
        'update' => 'Update tax information',
        'update_success' => 'Update tax information successfully!',
        'company_name' => 'Company name',
        'company_address' => 'Company address',
        'company_tax_code' => 'Company tax code',
        'company_email' => 'Company email',
    ],
    'mark_as_completed' => [
        'name' => 'Mark as completed',
        'modal_title' => 'Mark order as completed',
        'modal_description' => 'Are you sure you want to mark this order as completed? This will change the order status to completed and cannot be undone.',
        'success' => 'Mark order as completed successfully!',
        'history' => 'Order is marked as completed by :admin at :time',
    ],
    'generate_invoice' => 'Generate invoice',
    'generated_invoice_successfully' => 'Generated invoice successfully!',
    'order_cannot_be_canceled' => 'Order is processing or completed, cannot be canceled!',
    'cancellation_reason' => 'Reason: :reason',
    'order_cancellation_reason' => 'Order cancellation reason',
    'cancellation_reasons' => [
        'change-mind' => 'Changed mind or no longer needed the product',
        'found-better-price' => 'Found a better price elsewhere',
        'out-of-stock' => 'Product out of stock',
        'shipping-delays' => 'Shipping delays',
        'incorrect-address' => 'Incorrect or incomplete shipping address',
        'customer-requested' => 'Customer requested cancellation',
        'not-as-described' => 'Product not as described',
        'payment-issues' => 'Payment issues or declined transaction',
        'unforeseen-circumstances' => 'Unforeseen circumstances or emergencies',
        'technical-issues' => 'Technical issues during the checkout process',
        'other' => 'Other',
    ],
    'requires_products_to_create_order' => 'Please select at least one product to create order',
    'transaction_id' => 'Transaction ID',
    'incomplete_order_transaction_id_placeholder' => 'You can leave this field empty if the payment method is COD or Bank transfer',
    'digital_product_downloads' => [
        'title' => 'Digital product downloads',
        'download_count' => ':count download(s)',
        'first_download' => 'First download at :time',
        'not_downloaded_yet' => 'Not downloaded yet',
    ],
    'select_one' => 'Select one',
    'confirm_delivery_error' => 'You cannot confirm delivery for this order',
    'confirm_delivery_success' => 'Order was confirmed delivery successfully!',

    'export' => [
        'total_orders' => 'Total Orders',
        'limit' => 'Number of Orders to Export',
        'limit_placeholder' => 'Enter number of orders to export (leave empty for all)',
        'all_status' => 'All Statuses',
        'start_date' => 'Start Date',
        'start_date_placeholder' => 'Select start date',
        'end_date' => 'End Date',
        'end_date_placeholder' => 'Select end date',
    ],

    'edit_email' => 'Edit email',
    'export_title' => 'Orders',
    'export_description' => 'Export orders to a CSV file',
    'download' => 'Download',
];
