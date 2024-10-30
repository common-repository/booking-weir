<?php

namespace wsd\bw\config;

use wsd\bw\core\Email;
use wsd\bw\util\datetime;

include 'TEMPLATE_PARTS.php';

$settings = [
	[
		'id' => 'openingHour',
		'category' => 'calendar',
		'label' => __('Opening hour', 'booking-weir'),
		'description' => __('Earliest time that an event can be booked.', 'booking-weir'),
		'type' => 'number',
		'default' => 9,
		'public' => true,
		'props' => [
			'min' => 0,
			'max' => 24,
			'step' => 1,
		],
	],
	[
		'id' => 'closingHour',
		'category' => 'calendar',
		'label' => __('Closing hour', 'booking-weir'),
		'description' => __("Time after which events can't be booked.", 'booking-weir'),
		'type' => 'number',
		'default' => 17,
		'public' => true,
		'props' => [
			'min' => 0,
			'max' => 24,
			'step' => 1,
		],
	],
	[
		'id' => 'weekend',
		'category' => 'calendar',
		'label' => __('Weekend', 'booking-weir'),
		'description' => __('Show Saturday and Sunday in the calendar week view.', 'booking-weir'),
		'type' => 'toggle',
		'default' => true,
		'public' => true,
	],
	[
		'id' => 'step',
		'category' => 'calendar',
		'label' => __('Step', 'booking-weir'),
		'description' => __('Determines the selectable time increments in minutes.', 'booking-weir'),
		'type' => 'number',
		'default' => 30,
		'public' => true,
		'props' => [
			'min' => 5,
			'max' => 360,
			'step' => 5,
		],
	],
	[
		'id' => 'space',
		'category' => 'calendar',
		'label' => __('Space', 'booking-weir'),
		'description' => __('Amount of minutes that should be left between consecutive events.', 'booking-weir'),
		'type' => 'number',
		'default' => 0,
		'public' => true,
		'props' => [
			'min' => 0,
			'max' => 600,
			'step' => 1,
		],
	],
	[
		'id' => 'height',
		'category' => 'calendar',
		'label' => __('Height', 'booking-weir'),
		'description' => __("Calendar's height in pixels.", 'booking-weir'),
		'type' => 'number',
		'default' => 750,
		'public' => true,
		'props' => [
			'min' => 320,
			'max' => 2160,
			'step' => 10,
		],
	],
	[
		'id' => 'mobile',
		'category' => 'calendar',
		'label' => __('Mobile', 'booking-weir'),
		'description' => __('Available width required for the calendar to display a whole week instead of a single day.', 'booking-weir'),
		'type' => 'number',
		'default' => 800,
		'public' => true,
		'props' => [
			'min' => 0,
			'max' => 2160,
			'step' => 10,
		],
	],
	[
		'id' => 'classes',
		'category' => 'calendar',
		'label' => __('Classes', 'booking-weir'),
		'description' => __("CSS classes for the calendar's root container element.", 'booking-weir'),
		'type' => 'text',
		'default' => '',
		'public' => true,
		'props' => [
			'placeholder' => 'alignwide alignfull custom-class-name...'
		],
	],
	[
		'id' => 'product',
		'category' => 'calendar',
		'label' => __('Product', 'booking-weir'),
		'description' => __('A WooCommerce product that represents bookings in this calendar.', 'booking-weir'),
		'type' => 'product',
		'default' => 0,
		'public' => true,
	],
	[
		'id' => 'productPriceText',
		'category' => 'calendar',
		'label' => __('Product price', 'booking-weir'),
		'description' => __('Custom price text for the WooCommerce product associated with the calendar (default: calendar\'s price per hour).', 'booking-weir'),
		'type' => 'text',
		'default' => '',
		'public' => false,
		'wc' => true,
		'props' => [
			'placeholder' => __('Override price text...', 'booking-weir'),
		],
	],
	[
		'id' => 'url',
		'category' => 'calendar',
		'label' => __('URL', 'booking-weir'),
		'description' => __('Link to the page where this calendar is displayed on the front end.', 'booking-weir'),
		'type' => 'url',
		'default' => '',
		'public' => true,
	],
	[
		'id' => 'price',
		'category' => 'pricing',
		'label' => __('Price per hour', 'booking-weir'),
		'description' => __('Price per hour for bookings in this calendar.', 'booking-weir'),
		'type' => 'number',
		'default' => 30,
		'public' => true,
		'props' => [
			'min' => 1,
			'max' => 1000,
			'step' => 1,
		],
	],
	[
		'id' => 'currency',
		'category' => 'pricing',
		'label' => __('Currency prefix', 'booking-weir'),
		'description' => __('Symbol to display before currency.', 'booking-weir'),
		'type' => 'text',
		'default' => '€',
		'public' => true,
	],
	[
		'id' => 'currencySuffix',
		'category' => 'pricing',
		'label' => __('Currency suffix', 'booking-weir'),
		'description' => __('Symbol to display after currency.', 'booking-weir'),
		'type' => 'text',
		'default' => '',
		'public' => true,
	],
	[
		'id' => 'currencySingular',
		'category' => 'pricing',
		'label' => __('Currency singular', 'booking-weir'),
		'description' => __('Currency spelled out in singular form.', 'booking-weir'),
		'type' => 'text',
		'default' => _x('euro', 'Default currency singular', 'booking-weir'),
		'public' => true,
	],
	[
		'id' => 'currencyPlural',
		'category' => 'pricing',
		'label' => __('Currency plural', 'booking-weir'),
		'description' => __('Currency spelled out in plural form.', 'booking-weir'),
		'type' => 'text',
		'default' => _x('euros', 'Default currency plural', 'booking-weir'),
		'public' => true,
	],
	[
		'id' => 'tax',
		'category' => 'pricing',
		'label' => __('Tax', 'booking-weir'),
		'description' => __('Percentage to subtract from the final price and declare as tax in the invoice.', 'booking-weir'),
		'type' => 'number',
		'default' => 0,
		'public' => false,
		'props' => [
			'min' => 0,
			'max' => 100,
			'step' => 1,
		],
	],
	[
		'id' => 'locale',
		'category' => 'locale',
		'label' => __('Locale', 'booking-weir'),
		'description' => __('Locale to use when internationalizing date related text.', 'booking-weir'),
		'type' => 'locale',
		'default' => datetime\get_default_locale(),
		'public' => true,
	],
	[
		'id' => 'timezone',
		'category' => 'locale',
		'label' => __('Timezone', 'booking-weir'),
		'description' => __('The timezone in which the events in this calendar are booked.', 'booking-weir'),
		'type' => 'timezone',
		'default' => datetime\get_default_timezone(),
		'public' => true,
	],
	[
		'id' => 'timezoneWarning',
		'category' => 'locale',
		'label' => __('Timezone warning', 'booking-weir'),
		'description' => __('Show a warning message when the browser and calendar timezone is different.', 'booking-weir'),
		'type' => 'toggle',
		'default' => true,
		'public' => true,
	],
	[
		'id' => 'slots',
		'category' => 'booking',
		'label' => __('Slots', 'booking-weir'),
		'description' => __('Allow only booking times in predefined slots. You can add slots by creating events of type "Slot" in the calendar. Creating a regular "Event" and making it "Bookable" also counts as a slot.', 'booking-weir'),
		'type' => 'toggle',
		'default' => false,
		'public' => true,
	],
	[
		'id' => 'services',
		'category' => 'booking',
		'label' => __('Services', 'booking-weir'),
		'description' => __('Allow only booking services. Available services can be configured from the "Services" page.', 'booking-weir'),
		'type' => 'toggle',
		'default' => false,
		'public' => true,
		'premium' => true, // Hide option for free since it's useless.
	],
	[
		'id' => 'future',
		'category' => 'booking',
		'label' => __('Future bookings', 'booking-weir'),
		'description' => __('Prevent booking more than n days in advance. Setting this value to 1 will only allow booking on the current day, 2 will allow booking for today and tomorrow, and so on. Setting this value to 0 will allow booking into any time in the future.', 'booking-weir'),
		'type' => 'number',
		'default' => 0,
		'public' => true,
		'props' => [
			'min' => 0,
			'max' => 365,
			'step' => 1,
		],
	],
	[
		'id' => 'minDuration',
		'category' => 'booking',
		'label' => __('Minimum booking duration', 'booking-weir'),
		'description' => __('Prevent creating bookings shorter than this value (in minutes). When this value is 0 then the "Calendar -> Step" setting acts as the minimum booking duration.', 'booking-weir'),
		'type' => 'number',
		'default' => 0,
		'public' => true,
		'props' => [
			'min' => 0,
			'max' => 1440,
			'step' => 10,
		],
	],
	[
		'id' => 'maxDuration',
		'category' => 'booking',
		'label' => __('Maximum booking duration', 'booking-weir'),
		'description' => __('Prevent creating bookings longer than this value (in minutes). Setting this value to 0 will allow creating a booking of any duration limited by opening and closing hour settings.', 'booking-weir'),
		'type' => 'number',
		'default' => 0,
		'public' => true,
		'props' => [
			'min' => 0,
			'max' => 1440,
			'step' => 10,
		],
	],
	[
		'id' => 'noticeEmails',
		'category' => 'email',
		'label' => __('Notice emails', 'booking-weir'),
		'description' => __('Admin e-mails to notify when a new booking is made.', 'booking-weir'),
		'type' => 'emails',
		'default' => '',
		'public' => false,
	],
	[
		'id' => 'templateEmailHeader',
		'category' => 'email',
		'label' => __('E-mail header', 'booking-weir'),
		'description' => __('Content before main text in e-mails.', 'booking-weir'),
		'type' => 'html',
		'default' => $template_email_header ?: '', // @phpstan-ignore-line
		'public' => false,
	],
	[
		'id' => 'templateEmailFooter',
		'category' => 'email',
		'label' => __('E-mail footer', 'booking-weir'),
		'description' => __('Footer content for e-mails.', 'booking-weir'),
		'type' => 'html',
		'default' => $template_email_footer ?: '', // @phpstan-ignore-line
		'public' => false,
	],
	[
		'id' => 'invoiceEmailEnabled',
		'category' => 'email',
		'label' => __('Send invoice e-mail', 'booking-weir'),
		'description' => __('Send an e-mail to the booker when a booking has been successfully created.', 'booking-weir'),
		'type' => 'toggle',
		'default' => true,
		'public' => false,
	],
	[
		'id' => 'templateInvoiceEmailContent',
		'category' => 'email',
		'label' => __('Invoice e-mail content', 'booking-weir'),
		'description' => __('E-mail that has a PDF invoice attached.', 'booking-weir'),
		'type' => 'html-email',
		'default' => $template_invoice_email_content ?: '', // @phpstan-ignore-line
		'public' => false,
	],
	[
		'id' => 'statusConfirmedEmailEnabled',
		'category' => 'email',
		'label' => __('Send confirmation e-mail', 'booking-weir'),
		'description' => __('Send an e-mail to the booker when booking status is changed to "Confirmed".', 'booking-weir'),
		'type' => 'toggle',
		'default' => false,
		'public' => false,
	],
	[
		'id' => 'templateStatusConfirmedEmailContent',
		'category' => 'email',
		'label' => __('Confirmation e-mail content', 'booking-weir'),
		'description' => __('E-mail that is sent when booking status is changed to "Confirmed".', 'booking-weir'),
		'type' => 'html-email',
		'default' => $template_status_confirmed_email_content ?: '', // @phpstan-ignore-line
		'public' => false,
	],
	[
		'id' => 'reminderEmailOffset',
		'category' => 'email',
		'label' => __('Reminder e-mail', 'booking-weir'),
		'description' => __('How many hours before booking start time to send a reminder e-mail. No reminder is sent when the value is 0, but you can still send reminder e-mails manually.', 'booking-weir'),
		'type' => 'number',
		'default' => 0,
		'public' => false,
		'props' => [
			'min' => 0,
			'max' => 744,
			'step' => 1,
		],
	],
	[
		'id' => 'templateReminderEmailContent',
		'category' => 'email',
		'label' => __('Reminder e-mail content', 'booking-weir'),
		'description' => __('E-mail that reminds about booking.', 'booking-weir'),
		'type' => 'html-email',
		'default' => $template_reminder_email_content ?: '', // @phpstan-ignore-line
		'public' => false,
	],
	[
		'id' => 'debugEmail',
		'category' => 'email',
		'label' => __('Debug email', 'booking-weir'),
		'description' => sprintf(__('E-mail address to use when client e-mail is set to %s. When you make a test booking then this e-mail will receive the invoice e-mail.', 'booking-weir'), Email::DEBUG_EMAIL_PLACEHOLDER),
		'type' => 'email',
		'default' => '',
		'public' => false,
	],
	[
		'id' => 'invoicePdfEnabled',
		'category' => 'pdf',
		'label' => __('PDF invoices', 'booking-weir'),
		'description' => __('Generate PDF invoices for bookings and attach them to invoice e-mails.', 'booking-weir'),
		'type' => 'toggle',
		'default' => true,
		'public' => false,
	],
	[
		'id' => 'invoicePdfRegenerate',
		'category' => 'pdf',
		'label' => __('Automatic regeneration', 'booking-weir'),
		'description' => __('Automatically regenerate the PDF invoice when the booking data changes.', 'booking-weir'),
		'type' => 'toggle',
		'default' => true,
		'public' => false,
	],
	[
		'id' => 'invoicePdfDelete',
		'category' => 'pdf',
		'label' => __('Automatic deletion', 'booking-weir'),
		'description' => __('Delete PDF invoices from the filesystem when the associated booking is deleted.', 'booking-weir'),
		'type' => 'toggle',
		'default' => true,
		'public' => false,
	],
	[
		'id' => 'invoicePdfNoPayment',
		'category' => 'pdf',
		'label' => __('Send invoice without payment method', 'booking-weir'),
		'description' => __('When no payment methods are enabled an "invoice" e-mail is still sent confirming that the booking is received. Enable this option to still include the PDF invoice with this e-mail.', 'booking-weir'),
		'type' => 'toggle',
		'default' => false,
		'public' => false,
	],
	[
		'id' => 'invoicePdfTitle',
		'category' => 'pdf',
		'label' => __('PDF invoice title', 'booking-weir'),
		'description' => __('PDF document title displayed in the title bar of a user agent.', 'booking-weir'),
		'type' => 'text',
		'default' => __('Company name — Invoice', 'booking-weir'),
		'public' => false,
	],
	[
		'id' => 'invoicePdfAuthor',
		'category' => 'pdf',
		'label' => __('PDF invoice author', 'booking-weir'),
		'description' => __('Author of the document, stored in the PDF metadata.', 'booking-weir'),
		'type' => 'text',
		'default' => 'Company name',
		'public' => false,
	],
	[
		'id' => 'invoicePdfHeader',
		'category' => 'pdf',
		'label' => __('PDF invoice header', 'booking-weir'),
		'description' => __('Big header displayed in the top left corner of the PDF document.', 'booking-weir'),
		'type' => 'text',
		'default' => __('Invoice', 'booking-weir'),
		'public' => false,
	],
	[
		'id' => 'invoicePdfFrom',
		'category' => 'pdf',
		'label' => __('PDF invoice from', 'booking-weir'),
		'description' => __('Info about the invoice issuer, displayed in the PDF as "Invoice from: Your text".', 'booking-weir'),
		'type' => 'text',
		'default' => __('Company name, account...', 'booking-weir'),
		'public' => false,
	],
	[
		'id' => 'templateInvoicePdfContent',
		'category' => 'pdf',
		'label' => __('PDF invoice content', 'booking-weir'),
		'description' => __('Additional content for invoice PDF.', 'booking-weir'),
		'type' => 'html',
		'default' => $template_invoice_pdf_content ?: '', // @phpstan-ignore-line
		'public' => false,
	],
	[
		'id' => 'templateInvoicePdfFooter',
		'category' => 'pdf',
		'label' => __('PDF invoice footer', 'booking-weir'),
		'description' => __('Footer content for invoice PDF.', 'booking-weir'),
		'type' => 'html',
		'default' => $template_invoice_pdf_footer ?: '', // @phpstan-ignore-line
		'public' => false,
	],
];

return apply_filters('bw_default_settings_schema', $settings);