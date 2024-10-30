<?php
/**
 * Logs template.
 *
 * @var Logger $this
 */
defined('ABSPATH') || exit;

$logs = $this->get_log_files();
$log = $this->get_selected_log($logs);
?>
<div class="sui-root">
	<div class="ui logs segment">
		<h1 class="ui left floated header"><?php esc_html_e('Logs', 'booking-weir'); ?></h1>
		<?php if(count($logs) > 0): ?>
		<div class="ui compact paddingless right floated menu">
			<div class="ui simple dropdown item">
				<?php echo $log ? esc_html($log) : esc_html__('Select', 'booking-weir'); ?>
				<div class="left menu">
					<?php foreach($logs as $id => $file): ?>
					<a class="<?php echo $log === $file ? 'active ' : ''; ?>item" href="<?php echo esc_url(add_query_arg(self::VIEW, $id)); ?>">
						<?php echo esc_html($file); ?>
					</a>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php endif; ?>
		<div class="ui clearing divider"></div>
		<?php if(!$this->logging_is_enabled()): ?>
			<div class="ui warning icon message">
				<i class="warning sign icon"></i>
				<div class="content">
					<div class="header"><?php esc_html_e('Logging is not enabled by default', 'booking-weir'); ?></div>
					<p>
						<?php printf(
							esc_html__('To enable logging add %1$s to your %2$s file.', 'booking-weir'),
							"<code>define('BOOKING_WEIR_ENABLE_LOGS', true);</code>",
							'<code>wp-config.php</code>'
						); ?>
						<br>
						<?php esc_html_e('Logs may contain personal data so you need to ensure the logs directory is not publicly accessible.', 'booking-weir'); ?>
						<br>
						<?php printf(
							esc_html__('You can set a custom log directory by adding %1$s to your %2$s file.', 'booking-weir'),
							"<code>define('BOOKING_WEIR_LOGS_DIR', '/path/to/logs');</code>",
							'<code>wp-config.php</code>'
						); ?>
						<br>
						<?php esc_html_e('The directory needs to be writable and ideally outside of the web server root.', 'booking-weir'); ?>
					</p>
				</div>
			</div>
		<?php endif; ?>
		<?php if($log): ?>
		<div class="ui secondary paddingless segment">
			<pre id="bw-log" class="bw-log"><?php echo esc_html($this->get_contents($log)); ?></pre>
		</div>
		<?php endif; ?>
		<?php if(count($logs) < 1): ?>
			<div class="ui info message">
				<?php esc_html_e('There are no logs at the moment.', 'booking-weir'); ?>
			</div>
		<?php endif; ?>
		<?php if($this->logging_is_enabled()): ?>
			<div class="ui positive icon message">
				<i class="check icon"></i>
				<div class="content">
					<div class="header"><?php esc_html_e('Logging is enabled', 'booking-weir'); ?></div>
					<p><?php printf(
						esc_html__('To disable logging remove %1$s from your %2$s file.', 'booking-weir'),
						"<code>define('BOOKING_WEIR_ENABLE_LOGS', true);</code>",
						'<code>wp-config.php</code>'
					); ?></p>
				</div>
			</div>
			<?php if(!$this->logs_dir_is_writable()): ?>
			<div class="ui negative message">
				<i class="exclamation icon"></i>
				<?php esc_html_e('Logs directory is not writable.', 'booking-weir'); ?>
			</div>
			<?php endif; ?>
		<?php endif; ?>
		<div class="ui info message">
			<i class="info icon"></i>
			<?php printf(
				esc_html__('Logs directory: %s', 'booking-weir'),
				'<code>' . esc_html($this->get_logs_dir()) . '</code>'
			); ?>
		</div>
	</div>
</div>
<style>
	.bw-log {
		padding: 1em;
		max-height: 70vh;
		overflow-y: scroll;
		margin: 0;
		white-space: break-spaces;
	}
</style>
<script>
	document.addEventListener('DOMContentLoaded', function() {
		var container = document.getElementById('bw-log');
		if(container) {
			container.scrollTop = container.scrollHeight;
		}
	});
</script>
