{{ header }}

<div class="bb-main-content">
    <table class="bb-box" cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td>
                    <table cellpadding="0" cellspacing="0">
                        <tbody>
                            <tr>
                                <td class="bb-content bb-pb-0" align="center">
                                    <table class="bb-mb-lg" cellspacing="0" cellpadding="0">
                                       <tbody>
                                           <tr>
                                               <td valign="middle" align="center">
                                                   <img src="{{ site_url }}/vendor/core/plugins/newsletter/images/newsletter.png" alt="Icon" height="160" class="bb-img-illustration" />
                                               </td>
                                           </tr>
                                       </tbody>
                                    </table>

                                    <h1 class="bb-text-center bb-m-0">{{ 'plugins/newsletter::newsletter.email_templates.subscriber_success_title' | trans }}</h1>

                                    <p class="bb-text-center bb-mt-sm bb-mb-0 bb-text-muted">{{ 'plugins/newsletter::newsletter.email_templates.subscriber_thank_you_message' | trans }}</p>
                                </td>
                            </tr>

                            <tr>
                                <td class="bb-content bb-text-muted bb-text-center">
                                    {{ 'plugins/newsletter::newsletter.email_templates.subscriber_unsubscribe_instruction' | trans({'newsletter_unsubscribe_link': newsletter_unsubscribe_link}) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</div>

{{ footer }}
