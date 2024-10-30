<?php
/**
 * About page template.
 *
 * @var About $this
 */
defined('ABSPATH') || exit;
?>
<div class="sui-root">
	<div class="ui about segment">
		<h1 class="ui header"><?php esc_html_e('About', 'booking-weir'); ?></h1>
		<table class="ui definition table">
			<tbody>
			<?php foreach($this->get_info() as $info): ?>
				<tr>
					<td class="collapsing"><?php echo esc_html($info['title']); ?></td>
					<?php
						switch($info['type'] ?? 'default') {
							case 'bool':
								printf('<td>%s</td>', (bool)$info['value'] ? '<span class="dashicons dashicons-yes"></span>' : '<span class="dashicons dashicons-warning"></span>');
							break;
							case 'code':
								printf('<td><code>%s</code></td>', esc_html($info['value']));
							break;
							default:
								printf('<td>%s</td>', esc_html($info['value']));
						}
					?>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<div class="ui huge header">
			<?php esc_html_e('History', 'booking-weir'); ?>
			<div class="sub header">
				<?php esc_html_e('Restore an older version of calendars configuration.', 'booking-weir'); ?>
			</div>
		</div>
		<table class="ui table">
			<thead>
				<tr>
					<th><?php esc_html_e('Time', 'booking-weir'); ?></th>
					<th><?php esc_html_e('Calendars', 'booking-weir'); ?></th>
					<th><?php esc_html_e('Actions', 'booking-weir'); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach(array_reverse($this->context->get('calendars')->get_history(), true) as $time => $calendars): ?>
				<tr>
					<td><?php echo esc_html(date_i18n(get_option('date_format') . ' H:i:s', $time)); ?></td>
					<td><?php echo count($calendars); ?></td>
					<td>
						<a class="ui compact right labeled icon button" href="<?php echo esc_url(wp_nonce_url(add_query_arg(self::RESTORE_QUERY_VAR, $time), self::RESTORE_ACTION)); ?>">
							<?php esc_html_e('Roll back', 'booking-weir'); ?>
							<i class="history icon"></i>
						</a>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
