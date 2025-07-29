<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        try {
            // Fix tax_amount in ec_order_product table
            // The tax_amount should be multiplied by qty
            DB::statement('
                UPDATE ec_order_product 
                SET tax_amount = tax_amount * qty 
                WHERE tax_amount > 0 
                AND qty > 1
                AND tax_amount < (price * qty * 0.5)
            ');

            // Fix tax_amount in ec_invoice_items table
            // First, we need to get the correct tax_amount from ec_order_product
            DB::statement('
                UPDATE ec_invoice_items ii
                INNER JOIN ec_invoices i ON ii.invoice_id = i.id
                INNER JOIN ec_orders o ON i.reference_id = o.id AND i.reference_type = ?
                INNER JOIN ec_order_product op ON o.id = op.order_id AND ii.reference_id = op.product_id
                SET ii.tax_amount = op.tax_amount,
                    ii.amount = ii.sub_total + op.tax_amount
                WHERE ii.reference_type = ?
                AND op.tax_amount > 0
            ', ['Botble\Ecommerce\Models\Order', 'Botble\Ecommerce\Models\Product']);

            // Also update invoice items where tax wasn't properly multiplied
            // This handles cases where order products weren't updated yet
            DB::statement('
                UPDATE ec_invoice_items ii
                INNER JOIN ec_invoices i ON ii.invoice_id = i.id
                INNER JOIN ec_orders o ON i.reference_id = o.id AND i.reference_type = ?
                INNER JOIN ec_order_product op ON o.id = op.order_id AND ii.reference_id = op.product_id
                SET ii.tax_amount = ii.tax_amount * ii.qty,
                    ii.amount = ii.sub_total + (ii.tax_amount * ii.qty)
                WHERE ii.reference_type = ?
                AND ii.tax_amount > 0
                AND ii.qty > 1
                AND ii.tax_amount < (ii.price * ii.qty * 0.5)
                AND op.tax_amount = op.tax_amount * op.qty
            ', ['Botble\Ecommerce\Models\Order', 'Botble\Ecommerce\Models\Product']);
        } catch (\Exception) {
            // Do nothing
        }
    }

    public function down(): void
    {
        // Revert tax_amount in ec_order_product table
        DB::statement('
            UPDATE ec_order_product 
            SET tax_amount = tax_amount / qty 
            WHERE tax_amount > 0 
            AND qty > 1
            AND tax_amount > (price * 0.5)
        ');

        // Revert tax_amount in ec_invoice_items table
        DB::statement('
            UPDATE ec_invoice_items ii
            INNER JOIN ec_invoices i ON ii.invoice_id = i.id
            INNER JOIN ec_orders o ON i.reference_id = o.id AND i.reference_type = ?
            INNER JOIN ec_order_product op ON o.id = op.order_id AND ii.reference_id = op.product_id
            SET ii.tax_amount = op.tax_amount,
                ii.amount = ii.sub_total + op.tax_amount
            WHERE ii.reference_type = ?
            AND op.tax_amount > 0
        ', ['Botble\Ecommerce\Models\Order', 'Botble\Ecommerce\Models\Product']);
    }
};