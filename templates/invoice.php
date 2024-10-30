<?php
/**
 * PDF invoice template.
 * Copy this template to `/wp-content/themes/your-child-theme/booking-weir/invoice.php` to override it.
 *
 * @var wsd\bw\core\Pdf $this
 * @var wsd\bw\core\events\Event $event
 */
defined('ABSPATH') || exit;

$calendar = $event->get_calendar();
$booking_price = $event->get_payment_amount();
$tax_percent = (int)$calendar->get_setting('tax');
$tax = $tax_percent > 0 ? number_format(($booking_price * $tax_percent) / 100, 2) : 0;
$price = number_format($booking_price - $tax, 2);
$total_price = number_format($booking_price, 2);
?>
<html>
	<head>
		<style>
			body {
				font-family: sans-serif;
				font-size: 10pt;
			}
			p {
				margin: 0pt;
			}
			table.items {
				border: 0.1mm solid #dededf;
			}
			td {
				vertical-align: top;
			}
			.items td {
				border-left: 0.1mm solid #dededf;
				border-right: 0.1mm solid #dededf;
			}
			table thead td {
				background-color: #f9fafb;
				text-align: center;
				border: 0.1mm solid #dededf;
				font-weight: bold;
			}
			.items td.blanktotal {
				border: 0.1mm solid #dededf;
				background-color: #fff;
				border: 0mm none #dededf;
				border-top: 0.1mm solid #dededf;
				border-right: 0.1mm solid #dededf;
			}
			.items td.totals {
				text-align: right;
				border: 0.1mm solid #dededf;
			}
			.items td.cost {
				text-align: "." center;
			}

			table.compact td {
				padding-top: 0.25em;
				padding-bottom: 0.25em;
			}
			<?php do_action('bw_pdf_styles'); ?>
		</style>
	</head>
	<body>
		<!--mpdf
		<htmlpageheader name="bw-invoice-header">
			<table width="100%">
				<tr>
					<td width="70%" style="color:#0000BB; ">
						<span style="font-weight:bold;font-size:14pt">
							<?php echo wp_kses_post($calendar->get_setting('invoicePdfHeader')); ?>
						</span>
					</td>
					<td width="30%" style="text-align:right">
						<?php echo esc_html_x('Invoice number', 'PDF invoice', 'booking-weir'); ?><br />
						<span style="font-weight:bold;font-size:12pt">
							<?php echo esc_html($event->get_billing_id()); ?>
						</span>
					</td>
				</tr>
			</table>
		</htmlpageheader>
		<htmlpagefooter name="bw-invoice-footer">
			<?php
				echo wp_kses_post(
					str_replace(
						'<table',
						'<table style="width:100%;border-top:1px solid #dededf;font-size:9pt;padding-top:3mm"',
						$this->fixed_table($calendar->get_setting('templateInvoicePdfFooter'))
					)
				);
			?>
		</htmlpagefooter>
		<sethtmlpageheader name="bw-invoice-header" value="on" show-this-page="1" />
		<sethtmlpagefooter name="bw-invoice-footer" value="on" />
		mpdf-->
		<table width="100%" cellpadding="10">
			<tr>
				<td width="70%" style="padding:0">
					<table class="compact">
						<tbody>
							<tr>
								<td width="25%"><?php echo esc_html_x('Invoice number:', 'PDF invoice', 'booking-weir'); ?></td>
								<td>
									<?php echo esc_html($event->get_billing_id()); ?>
								</td>
							</tr>
							<tr>
								<td><?php echo esc_html_x('Invoice date:', 'PDF invoice', 'booking-weir'); ?></td>
								<td><?php echo esc_html(date_i18n(get_option('date_format'))); ?></td>
							</tr>
							<?php if($due = apply_filters('bw_invoice_due_date', '+3 days')): ?>
							<tr>
								<td><?php echo esc_html_x('Due date:', 'PDF invoice', 'booking-weir'); ?></td>
								<td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($due))); ?></td>
							</tr>
							<?php endif; ?>
							<tr>
								<td><?php echo esc_html_x('Invoice from:', 'PDF invoice', 'booking-weir'); ?></td>
								<td><?php echo wp_kses_post($calendar->get_setting('invoicePdfFrom')); ?></td>
							</tr>
						</tbody>
					</table>
				</td>
				<td width="30%" style="text-align:right;">
					<?php printf(esc_html_x('Invoice to: %s', 'PDF invoice', 'booking-weir'), esc_html($event->get_name())); ?>
				</td>
			</tr>
		</table>
		<br />
		<table class="items" width="100%" style="font-size: 9pt; border-collapse: collapse; " cellpadding="8">
			<thead>
				<tr>
					<td width="60%" style="text-align:left"><?php echo esc_html_x('Product/service', 'PDF invoice', 'booking-weir'); ?></td>
					<td width="10%"><?php echo esc_html_x('Amount', 'PDF invoice', 'booking-weir'); ?></td>
					<td width="15%" style="text-align:right"><?php echo esc_html_x('Price', 'PDF invoice', 'booking-weir'); ?></td>
					<td width="15%" style="text-align:right"><?php echo esc_html_x('Total', 'PDF invoice', 'booking-weir'); ?></td>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<?php echo esc_html_x('Booking', 'PDF invoice', 'booking-weir') . ' (' . esc_html($calendar->get_name()) . ') ' . wp_kses_post($event->get_date_formatted()); ?>
					</td>
					<td style="text-align:center">1</td>
					<td class="cost" style="text-align:right">
						<?php echo esc_html($price); ?>
					</td>
					<td class="cost" style="text-align:right">
						<?php echo esc_html($price); ?>
					</td>
				</tr>
				<?php if($tax > 0): ?>
				<tr>
					<td class="blanktotal" colspan="2" rowspan="3" style="border-top:2px solid #dededf"></td>
					<td class="totals"><?php echo esc_html_x('Subtotal:', 'PDF invoice', 'booking-weir'); ?></td>
					<td class="totals cost" style="text-align:right">
						<?php echo esc_html($price); ?>
					</td>
				</tr>
				<tr>
					<td class="totals"><?php echo esc_html_x('Tax:', 'PDF invoice', 'booking-weir'); ?></td>
					<td class="totals cost" style="text-align:right">
						<?php echo esc_html($tax); ?>
					</td>
				</tr>
				<?php endif; ?>
				<tr style="border-top:2px solid #dededf">
					<?php if($tax <= 0): ?>
					<td class="blanktotal" colspan="2" rowspan="3"></td>
					<?php endif; ?>
					<td class="totals"><b><?php echo esc_html_x('Total:', 'PDF invoice', 'booking-weir'); ?></b></td>
					<td class="totals cost" style="text-align:right">
						<b><?php echo esc_html($total_price); ?></b>
					</td>
				</tr>
			</tbody>
		</table>
		<?php if($spelled_out = $event->get_spelled_out_payment_amount()): ?>
			<?php printf(esc_html_x('Amount spelled out: %s', 'PDF invoice', 'booking-weir'), esc_html($spelled_out)); ?>
		<?php endif; ?>
		<?php echo wp_kses_post($calendar->get_setting('templateInvoicePdfContent')); ?>
	</body>
</html>
