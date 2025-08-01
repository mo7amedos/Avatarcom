{{ header }}

<div class="bb-main-content">
    <table class="bb-box" cellpadding="0" cellspacing="0">
        <tbody>
        <tr>
            <td class="bb-content bb-pb-0" align="center">
                <table class="bb-icon bb-icon-lg bb-bg-blue" cellspacing="0" cellpadding="0">
                    <tbody>
                        <tr>
                            <td valign="middle" align="center">
                                <img src="{{ 'shopping-cart' | icon_url }}" class="bb-va-middle" width="40" height="40" alt="Icon" />
                            </td>
                        </tr>
                    </tbody>
                </table>
                <h1 class="bb-text-center bb-m-0 bb-mt-md">{{ 'plugins/ecommerce::email-templates.order_recover_title' | trans }}</h1>
            </td>
        </tr>
        <tr>
            <td class="bb-content">
                <p>{{ 'plugins/ecommerce::email-templates.order_recover_greeting' | trans({'customer_name': customer_name}) }}</p>
                <div>{{ 'plugins/ecommerce::email-templates.order_recover_message' | trans }}</div>
            </td>
        </tr>
        <tr>
            <td class="bb-content bb-text-center bb-pt-0 bb-pb-xl">
                <table cellspacing="0" cellpadding="0">
                    <tbody>
                    <tr>
                        <td align="center">
                            <table cellpadding="0" cellspacing="0" border="0" class="bb-bg-blue bb-rounded bb-w-auto">
                                <tbody>
                                    <tr>
                                        <td align="center" valign="top" class="lh-1">
                                            <a href="{{ site_url }}/checkout/{{ order_token }}/recover" class="bb-btn bb-bg-blue bb-border-blue">
                                                <span class="btn-span">{{ 'plugins/ecommerce::email-templates.order_recover_button' | trans }}</span>
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr>
            <td class="bb-content bb-pt-0">
                <h4>{{ 'plugins/ecommerce::email-templates.order_recover_order_summary' | trans }}</h4>
                {{ product_list }}

                {% if order_note %}
                <div>{{ 'plugins/ecommerce::email-templates.order_recover_note' | trans }}: {{ order_note }}</div>
                {% endif %}
            </td>
        </tr>
        </tbody>
    </table>
</div>

{{ footer }}
