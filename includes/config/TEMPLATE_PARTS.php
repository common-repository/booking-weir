<?php

namespace wsd\bw\config;

$template_email_header = <<<HTML
<!-- wp:heading {"textAlign":"center","fontSize":"large"} -->
<h2 class="has-text-align-center has-large-font-size">Company name</h2>
<!-- /wp:heading -->
HTML;

$template_email_footer = <<<HTML
<!-- wp:table {"hasFixedLayout":true} -->
<figure class="wp-block-table"><table class="has-fixed-layout"><tbody><tr><td>2123&nbsp;Big&nbsp;Indian<br>New&nbsp;Landia,&nbsp;PA&nbsp;70322</td><td class="has-text-align-center" data-align="center">info@website.com<br>504-561-7848</td><td class="has-text-align-right" data-align="right">World Bank ABC<br>Account 3395349</td></tr></tbody></table></figure>
<!-- /wp:table -->
HTML;

$template_invoice_email_content = <<<HTML
<!-- wp:paragraph -->
<p>Hello%bw_space_first_name%,</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>thank you for booking with us!</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>You can view the status of your booking from <a rel="noreferrer noopener" aria-label="here (opens in a new tab)" href="%bw_booking_link%" target="_blank">here</a>.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>%bw_payment_instructions%</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>If you need additional info please feel free to contact us.</p>
<!-- /wp:paragraph -->
HTML;

$template_status_confirmed_email_content = <<<HTML
<!-- wp:paragraph -->
<p>Hello%bw_space_first_name%,</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>your <a rel="noreferrer noopener" aria-label="booking (opens in a new tab)" href="%bw_booking_link%" target="_blank">booking</a> on %bw_date% has been confirmed.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Hope to see you there!</p>
<!-- /wp:paragraph -->
HTML;

$template_reminder_email_content = <<<HTML
<!-- wp:paragraph -->
<p>Hello%bw_space_first_name%,</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>thank you for booking with us!</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>This is a reminder that your booking is due to begin at %bw_date%, hope to see you there!</p>
<!-- /wp:paragraph -->
HTML;

$template_invoice_pdf_content = '';

$template_invoice_pdf_footer = <<<HTML
<!-- wp:table {"hasFixedLayout":true} -->
<figure class="wp-block-table"><table class="has-fixed-layout"><tbody><tr><td>2123&nbsp;Big&nbsp;Indian<br>New&nbsp;Landia,&nbsp;PA&nbsp;70322</td><td>info@website.com<br>504-561-7848</td><td>World Bank ABC<br>Account 3395349</td></tr></tbody></table></figure>
<!-- /wp:table -->
HTML;
