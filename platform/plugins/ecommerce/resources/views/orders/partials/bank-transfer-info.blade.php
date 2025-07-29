@php
    // Get the first order to work with for payment proof
    $order = null;
    if (isset($orders) && $orders instanceof \Illuminate\Support\Collection) {
        $order = $orders->where('is_finished', true)->first();
        if (!$order) {
            $order = $orders->first();
        }
    } elseif (isset($order) && $order instanceof \Botble\Ecommerce\Models\Order) {
        // Order is already available
    }
@endphp

<div class="mt-3 p-3 rounded" style="background-color: #f8f9fa; border: 1px solid #e9ecef;">
    <div class="d-flex align-items-start">
        <div class="me-3">
            <x-core::icon name="ti ti-info-circle" style="width: 24px; height: 24px; color: var(--bs-primary);" />
        </div>
        <div class="flex-grow-1">
            <div style="font-size: 14px; color: #495057;">
                {!! BaseHelper::clean($bankInfo) !!}
            </div>
            <p class="mt-2 mb-1" style="font-size: 14px; color: #737373;">{!! BaseHelper::clean(
                __('Bank transfer amount: <strong>:amount</strong>', ['amount' => format_price($orderAmount)]),
            ) !!}</p>
            <p class="mb-0" style="font-size: 14px; color: #737373;">{!! BaseHelper::clean(
                __('Bank transfer description: <strong>Payment for order :code</strong>', ['code' => str_replace('#', '', $orderCode)]),
            ) !!}</p>
        </div>
    </div>

    @if (EcommerceHelper::isPaymentProofEnabled() && $order)
        @if ($order->canBeCanceled())
            <div class="mt-4">
                <div class="d-flex align-items-start">
                    <div class="me-3">
                        <x-core::icon name="ti ti-receipt" style="width: 30px; height: 30px; color: var(--bs-primary);" />
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-2" style="font-size: 16px; font-weight: 500;">{{ __('Payment Proof') }}</h6>
                        <x-core::form method="post" :files="true" class="customer-order-upload-receipt" :url="route('customer.orders.upload-proof', $order)">
                            @if (! $order->proof_file)
                                <p class="mb-3" style="font-size: 14px; color: #737373;">{{ __('The order is currently being processed. For expedited processing, kindly upload a copy of your payment proof:') }}</p>
                            @else
                                <p class="mb-2" style="font-size: 14px; color: #737373;">{{ __('You have uploaded a copy of your payment proof.') }}</p>
                                <div class="mb-3 p-2 bg-white rounded border">
                                    <span style="font-size: 14px; color: #737373;">{{ __('View Receipt:') }}</span>
                                    <a href="{{ route('customer.orders.download-proof', $order) }}" target="_blank" class="text-decoration-none ms-2" style="color: var(--bs-primary);">
                                        <x-core::icon name="ti ti-file" style="width: 16px; height: 16px;" />
                                        {{ $order->proof_file }}
                                    </a>
                                </div>
                                <p class="mb-3" style="font-size: 14px; color: #737373; font-weight: 500;">{{ __('Or you can upload a new one, the old one will be replaced.') }}</p>
                            @endif
                            <div class="d-flex align-items-center gap-2">
                                <input type="file" name="file" id="file" class="form-control" style="flex: 1;">
                                <button type="submit" class="btn payment-checkout-btn" style="padding: 6px 15px; display: inline-flex; align-items: center; gap: 5px;">
                                    <x-core::icon name="ti ti-upload" style="width: 18px; height: 18px;" />
                                    {{ __('Upload') }}
                                </button>
                            </div>
                            <small class="d-block mt-2" style="font-size: 12px; color: #737373;">{{ __('You can upload the following file types: jpg, jpeg, png, pdf and max file size is 2MB.') }}</small>
                        </x-core::form>
                    </div>
                </div>
            </div>
        @elseif ($order->proof_file)
            <div class="mt-4">
                <div class="d-flex align-items-start">
                    <div class="me-3">
                        <x-core::icon name="ti ti-receipt" style="width: 30px; height: 30px; color: var(--bs-primary);" />
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-2" style="font-size: 16px; font-weight: 500;">{{ __('Payment Proof') }}</h6>
                        <p class="mb-2" style="font-size: 14px; color: #737373;">{{ __('You have uploaded a copy of your payment proof.') }}</p>
                        <div class="p-2 bg-white rounded border">
                            <span style="font-size: 14px; color: #737373;">{{ __('View Receipt:') }}</span>
                            <a href="{{ route('customer.orders.download-proof', $order) }}" target="_blank" class="text-decoration-none ms-2" style="color: var(--bs-primary);">
                                <x-core::icon name="ti ti-file" style="width: 16px; height: 16px;" />
                                {{ $order->proof_file }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif

</div>
