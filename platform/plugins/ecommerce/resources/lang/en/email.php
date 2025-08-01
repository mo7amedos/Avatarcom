<?php

return [
    'name' => 'Ecommerce',
    'description' => 'Config email templates for Ecommerce',
    'welcome_title' => 'Welcome',
    'welcome_description' => 'Send email to user when they registered an account on our site',
    'welcome_subject' => 'Welcome to {{ site_title }}!',
    'customer_new_order_title' => 'Order confirmation',
    'customer_new_order_description' => 'Send email confirmation to customer when an order placed',

    'order_cancellation_title' => 'Order cancellation',
    'customer_order_cancellation_description' => 'Send to customer when they cancels an order',
    'admin_order_cancellation_title' => 'Order cancellation (by Admin)',
    'admin_order_cancellation_description' => 'Send to customer when admin cancels an order',
    'order_cancellation_to_admin_title' => 'Order cancellation (will be sent to admins)',
    'order_cancellation_to_admin_description' => 'Send to admin when customer cancels an order',
    'order_cancellation_to_admin_subject' => 'Order {{ order_id }} has been cancelled by customer',

    'delivery_confirmation_title' => 'Delivering confirmation',
    'delivery_confirmation_description' => 'Send to customer when order is delivering',

    'order_delivered_title' => 'Order delivered',
    'order_delivered_description' => 'Send to customer when order is delivered',

    'admin_new_order_title' => 'Notice about new order',
    'admin_new_order_description' => 'Send to administrators when an order placed',

    'order_confirmation_title' => 'Order confirmation',
    'order_confirmation_description' => 'Send to customer when they order was confirmed by admins',

    'payment_confirmation_title' => 'Payment confirmation',
    'payment_confirmation_description' => 'Send to customer when their payment was confirmed',

    'order_recover_title' => 'Incomplete order',
    'order_recover_description' => 'Send to custom to remind them about incomplete orders',
    'view_order' => 'View order',
    'link_go_to_our_shop' => 'or <a href=":link">Go to our shop</a>',
    'order_number' => 'Order number: <strong>:order_id</strong>',
    'order_information' => 'Order information:',

    'order_return_request_title' => 'Order return request',
    'order_return_request_description' => 'Send to customer when they return order',
    'confirm_email_title' => 'Confirm email',
    'confirm_email_description' => 'Send email to user when they register an account to verify their email',
    'confirm_email_subject' => 'Confirm your email address',
    'verify_link' => 'Verify email link',
    'customer_name' => 'Customer name',
    'password_reminder_title' => 'Reset password',
    'password_reminder_description' => 'Send email to user when they request to reset password',
    'password_reminder_subject' => 'Reset your password',
    'reset_link' => 'Reset password link',
    'customer_new_order_subject' => 'New order(s) at {{ site_title }}',
    'customer_order_cancellation_subject' => 'Your order has been cancelled {{ order_id }}',
    'admin_order_cancellation_subject' => 'Your order has been cancelled {{ order_id }}',
    'delivery_confirmation_subject' => 'Your order is delivering {{ order_id }}',
    'order_delivery_notes' => 'Order delivery notes',
    'order_delivered_subject' => 'Your order has been delivered {{ order_id }}',
    'admin_new_order_subject' => 'New order(s) at {{ site_title }}',
    'order_confirmation_subject' => 'Your order has been confirmed {{ order_id }}',
    'payment_confirmation_subject' => 'Your payment has been confirmed {{ order_id }}',
    'order_recover_subject' => 'You have incomplete order(s) at {{ site_title }}',
    'order_return_request_subject' => 'Your order return request {{ order_id }}',
    'list_order_products' => 'List of products',
    'invoice_payment_created_title' => 'Invoice payment created',
    'invoice_payment_created_description' => 'Send to customer when an invoice payment was created',
    'invoice_payment_created_subject' => 'Payment received from {{ customer_name }} on {{ site_title }}',
    'invoice_code' => 'Invoice code',
    'invoice_link' => 'Invoice link',
    'review_products_title' => 'Review Products',
    'review_products_description' => 'Send a notification to the customer to review the products when the order is completed',
    'review_products_subject' => 'Order completed, you can review the products now',
    'download_digital_products_title' => 'Download digital products',
    'download_digital_products_description' => 'Send email digital product downloads when guest makes a purchase',
    'download_digital_products_subject' => 'Download digital products which you have purchased',
    'digital_product_license_codes_title' => 'Digital product license codes',
    'digital_product_license_codes_description' => 'Send email with license codes for digital products without downloadable files',
    'digital_product_license_codes_subject' => 'Your license codes for digital products',
    'digital_product_list' => 'Digital product list',
    'digital_products' => 'Digital products',
    'customer_deletion_request_confirmation_title' => 'Account deletion confirmation',
    'customer_deletion_request_confirmation_description' => 'Send confirmation email to user when they request to delete their account',
    'customer_deletion_request_confirmation_subject' => 'Confirm your account deletion request',
    'customer_deletion_request_completed_title' => 'Account deletion completed',
    'customer_deletion_request_completed_description' => 'Send email to user when their account was deleted',
    'customer_deletion_request_completed_subject' => 'Your account has been deleted',
    'order_return_status_updated_title' => 'Order return request status updated',
    'order_return_status_updated_description' => 'Send to customer when their order return request status was updated',
    'order_return_status_updated_subject' => 'Your order return request {{ order_id }} status was changed to {{ status }}',
    'payment_proof_upload_notification_title' => 'Payment Proof Upload Notification',
    'payment_proof_upload_notification_description' => 'Notify admin when customer uploads payment proof',
    'payment_proof_upload_notification_subject' => 'Payment proof uploaded by {{ customer_name }} for order {{ order_id }}',
    'product_file_updated_title' => 'Product File Updated',
    'product_file_updated_description' => 'Notify customer when the product files are updated',
    'product_file_updated_subject' => 'Product files updated for order {{ order_id }}',
];
