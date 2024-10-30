<?php

namespace wsd\bw\core;

use wsd\bw\Context;
use wsd\bw\core\events\Event;
use wsd\bw\core\calendars\Calendar;
use Sabberworm\CSS\Parser;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\RuleSet\DeclarationBlock;
use Sabberworm\CSS\CSSList\AtRuleBlockList;

/**
 * Email class.
 */
final class Email {

	/** @var Context */
	protected $context;

	/**
	 * When this email is used redirect emails to the debug email in calendar settings.
	 */
	const DEBUG_EMAIL_PLACEHOLDER = 'debug@email.com';

	/**
	 * Block styles transient key.
	 */
	const STYLES_TRANSIENT = 'bw-block-styles';

	/**
	 * Block styles to include in e-mail CSS.
	 * Selectors are matched using `strpos`.
	 *
	 * @var array [whitelisted selector => [blacklisted selectors]]
	 */
	protected static $block_styles = [];

	/**
	 * Debug mode sends e-mails to debug e-mail for testing.
	 *
	 * @var boolean
	 */
	protected $debug = false;

	/**
	 * @param Context $context
	 */
	public function __construct(Context $context) {
		$this->context = $context;
	}

	/**
	 * Registers functionality through WordPress hooks.
	 */
	public function register() {
		add_action('bw_email_styles', [$this, 'print_block_styles']);
		add_action('bw_event_meta_changed', [$this, 'trigger_send_status_email'], 10, 3);
	}

	/**
	 * Send mail.
	 *
	 * @param array $mail_data [email, title, content, headers, attachments, template_data]
	 * @param Calendar $calendar Provide calendar to automatically use template data from it
	 * @return bool Mail sent
	 */
	public function send($mail_data, Calendar $calendar = null) {
		$mail_data = wp_parse_args($mail_data, [
			'email' => '',
			'title' => esc_attr(get_bloginfo('name')),
			'content' => '',
			'headers' => ['Content-Type: text/html; charset=UTF-8'],
			'attachments' => [],
			'preview' => '',
			'template_data' => [],
		]);
		$using_calendar = $calendar && $calendar instanceof Calendar;

		if($using_calendar) {
			/**
			 * Debug mode.
			 */
			if(
				$this->debug
				|| ($mail_data['email'] === self::DEBUG_EMAIL_PLACEHOLDER && $this->context->is_elevated())
			) {
				$mail_data['title'] = esc_html__('Debug', 'booking-weir') . ': ' . $mail_data['title'];
				$mail_data['email'] = $calendar->get_setting('debugEmail');
			}
		}

		if(!$mail_data['email'] || !$mail_data['content']) {
			$this->context->get('logger')->log(
				['Attempted to send mail without e-mail or content' => $mail_data],
				$using_calendar ? $calendar->get_id() : null
			);
			return false;
		}

		if($using_calendar) {
			$mail_data['template_data']['header'] = $calendar->get_setting('templateEmailHeader');
			$mail_data['template_data']['footer'] = $calendar->get_setting('templateEmailFooter');
		}

		$mail_data['content'] = $this->get_html(
			$mail_data['title'],
			$mail_data['preview'],
			$mail_data['content'],
			$mail_data['template_data']
		);

		$mails = [];
		$sent = [];
		if(strpos($mail_data['email'], ',') !== false) {
			/**
			 * Use comma separated emails.
			 */
			$mails = explode(',', $mail_data['email']);
		} elseif(is_array($mail_data['email'])) {
			/**
			 * Use array emails.
			 */
			$mails = $mail_data['email'];
		} else {
			/**
			 * Use single email.
			 */
			$mails[] = $mail_data['email'];
		}

		/**
		 * Attempt to send out mail(s).
		 */
		foreach($mails as $mail) {
			$sent[] = wp_mail(
				trim($mail),
				$mail_data['title'],
				$mail_data['content'],
				$mail_data['headers'],
				$mail_data['attachments']
			);
			$this->context->get('logger')->log([
				'send' => [
					'title' => $mail_data['title'],
					'to' => $mail,
					'sent' => end($sent),
				],
			], 'mail');
		}

		/**
		 * Returns true if sent at least 1 email.
		 */
		return count($sent) > 0 && in_array(true, $sent);
	}

	/**
	 * Send reminder e-mails for all eligible bookings in all calendars.
	 *
	 * @return int Amount of e-mails sent out successfully.
	 */
	public function send_reminder_emails() {
		$sent = 0;
		foreach($this->context->get('calendars')->get_calendars() as $calendar) {
			$offset = $calendar->get_reminder_email_offset();
			if($offset < 1) {
				continue;
			}
			foreach($calendar->get_events() as $event) {
				if($event->reminder_in() === 'pending') {
					if($this->send_reminder_email($event)) {
						$this->context->get('logger')->log($event->get_id() . ' - reminder e-mail sent', 'mail');
						$sent++;
					} else {
						$this->context->get('logger')->log($event->get_id() . ' - reminder e-mail failed to send', 'mail');
					}
				}
			}
		}
		return $sent;
	}

	/**
	 * Send reminder e-mail for a booking.
	 *
	 * @param Event $event
	 * @return bool Sent.
	 */
	public function send_reminder_email(Event $event) {
		$calendar = $event->get_calendar();
		$mail_data = apply_filters('bw_reminder_email', [
			'email' => $event->get_email(),
			'title' => esc_html_x('Booking reminder', 'Reminder e-mail title', 'booking-weir'),
			'content' => strtr($calendar->get_setting('templateReminderEmailContent'), $event->get_template_strings()),
		], $event);
		$sent = $this->send($mail_data, $calendar);
		$event->set_reminder_email_sent($sent);
		return $sent;
	}

	/**
	 * @param int $event_id
	 * @param string $key
	 * @param mixed $value
	 */
	public function trigger_send_status_email($event_id, $key, $value) {
		if($key !== 'bw_status') {
			return;
		}
		if(!$event = $this->context->get('event-factory')->create($event_id)) {
			return;
		}
		if(!$calendar = $event->get_calendar()) {
			return;
		}
		if(
			$calendar->get_setting('status' . ucfirst($event->get_status()) . 'EmailEnabled')
			&& !$event->get_status_email_sent()
		) {
			$this->send_status_email($event);
		}
	}

	/**
	 * Send e-mail for a booking status change.
	 *
	 * @param Event $event
	 * @return bool Sent.
	 */
	public function send_status_email(Event $event) {
		$status = $event->get_status();
		$calendar = $event->get_calendar();
		$mail_data = apply_filters('bw_status_email', [
			'email' => $event->get_email(),
			'title' => esc_html_x('Booking status update', 'Status e-mail title', 'booking-weir'),
			'content' => strtr($calendar->get_setting('templateStatus' . ucfirst($status) . 'EmailContent'), $event->get_template_strings()),
		], $event);
		$sent = $this->send($mail_data, $calendar);
		$event->set_status_email_sent(true);
		return $sent;
	}

	/**
	 * Returns the rendered email template.
	 * Template file can be overridden in `/child-theme/booking-weir/email.php` location.
	 *
	 * @param string $title E-mail title.
	 * @param string $preview E-mail preview text.
	 * @param string $content E-mail body.
	 * @param array $data Template data.
	 * @return string
	 */
	public function get_html($title, $preview, $content, $data) {
		ob_start();
		include $this->context->file('templates', 'email.php');
		return apply_filters('bw_mail_html', ob_get_clean(), $title, $preview, $content, $data);
	}

	/**
	 * Output block styles (does not include <style> tags).
	 */
	public function print_block_styles() {
		echo $this->get_block_styles(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Retrieves a subset of the official WordPress block editor styles as a string.
	 *
	 * @return string
	 */
	public function get_block_styles() {
		$ver = implode('-', [
			$this->context->plugin_version(),
			get_bloginfo('version')
		]);
		$cache = get_transient(self::STYLES_TRANSIENT);
		if($cache && is_array($cache) && isset($cache['ver']) && $cache['ver'] === $ver && isset($cache['value'])) {
			return $cache['value'];
		}

		if(empty(self::$block_styles)) {
			self::$block_styles = apply_filters('bw_email_block_styles', [
				':root' => [],
				'.wp-block-button' => [],
				'.wp-block-column' => [],
				'.wp-block-image' => [],
				'.wp-block-separator' => [],
				'.wp-block-spacer' => [],
				'.wp-block-table' => [
					'-background-color',
					'.is-style-stripes',
				],
				'.is-small-text' => [],
				'.is-regular-text' => [],
				'.is-large-text' => [],
				'.is-larger-text' => [],
				'.has-drop-cap' => [],
				'.has-background' => [
					'.has-background-dim',
				],
				'.has-text-color' => [
					'.wp-block-cover-image',
				],
				'.has-text-align' => [],
				'-font-size' => [],
			]);
		}

		$block_styles_css = '';
		if($file = $this->get_block_styles_file()) {
			$parser = new Parser(file_get_contents($file));
			$css = $parser->parse();

			foreach($css->getContents() as $block) {
				/**
				 * Retrieve the `Sabberworm\CSS\Property\Selector` for the block.
				 */
				switch(get_class($block)) {
					/**
					 * Combine block selectors to a comma separated string.
					 */
					case DeclarationBlock::class:
						$selectors = implode(', ', array_map(
							function($selector) {
								return (string)$selector;
							},
							$block->getSelectors()
						));
					break;
					/**
					 * Combine all selectors from the at-rule's contents into a comma separated string.
					 */
					case AtRuleBlockList::class:
						$selectors = implode(', ', array_map(
							function($contents) {
								/**
								 * Nested at-rule not supported.
								 */
								if(get_class($contents) !== DeclarationBlock::class) {
									return '';
								}
								return implode(', ', array_map(
									function($selector) {
										return (string)$selector;
									},
									$contents->getSelectors()
								));
							},
							$block->getContents()
						));
					break;
					default:
						$selectors = '';
				}
				/**
				 * Render if matches whitelist and not blacklist.
				 */
				foreach(self::$block_styles as $allowed_selector => $disallowed_selectors) {
					if(strpos($selectors, $allowed_selector) !== false) {
						$blacklisted = false;
						foreach($disallowed_selectors as $disallowed_selector) {
							if(strpos($selectors, $disallowed_selector) !== false) {
								$blacklisted = true;
							}
						}
						if(!$blacklisted) {
							$block_styles_css .= $block->render(OutputFormat::createCompact()) . "\n";
						}
						continue;
					}
				}
			}

			set_transient(self::STYLES_TRANSIENT, [
				'ver' => $ver,
				'value' => $block_styles_css,
			]);
		}
		return $block_styles_css;
	}

	/**
	 * Get the path to WordPress block library CSS file.
	 *
	 * @return string|bool
	 */
	public function get_block_styles_file() {
		if(function_exists('gutenberg_dir_path')) {
			$file = gutenberg_dir_path() . 'build/block-library/style.css';
		} else {
			global $wp_styles;
			if(!isset($wp_styles->registered['wp-block-library'])) {
				wp_enqueue_style('wp-block-library');
			}
			if(!isset($wp_styles->registered['wp-block-library'])) {
				return false;
			}
			$file = $wp_styles->registered['wp-block-library']->src;
		}

		if(strpos($file, ABSPATH) === false) {
			$file = ABSPATH . ltrim($file, '/');
		}

		if(file_exists($file) && validate_file($file) === 0) {
			return $file;
		}

		return false;
	}

	/**
	 * Set debug mode value.
	 *
	 * @param bool $value
	 */
	public function set_debug($value) {
		$this->debug = $value;
	}
}
