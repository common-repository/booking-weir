<?php
/**
 * E-mail template.
 * Copy this template to `/wp-content/themes/your-child-theme/booking-weir/email.php` to override it.
 *
 * @var string $title   E-mail title.
 * @var string $preview E-mail excerpt, visible in the mail client as preview text, invisible in the mail content itself.
 * @var string $content E-mail content.
 * @var array  $data    Template data.
 * 			   $data['header'] string Calendar setting `templateEmailHeader`.
 * 			   $data['footer'] string Calendar setting `templateEmailFooter`.
 */
defined('ABSPATH') || exit;
?>
<!doctype html>
<html>
	<head>
		<meta name="viewport" content="width=device-width" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>
		<?php
			printf(
				apply_filters('bw_email_title', '%1$s%2$s%3$s', $title, $data), // E-mail title: blog name, separator ` - ` (if title exists), e-mail title
				esc_attr(get_bloginfo('name')),
				isset($title) && !empty($title) ? ' - ' : '',
				esc_attr($title)
			);
		?>
		</title>
		<style>
			<?php
				/**
				 * @see https://github.com/leemunroe/responsive-html-email-template
				 */
			?>
			img {
				-ms-interpolation-mode: bicubic;
				max-width: 100%;
			}

			body {
				background-color: #f6f6f6;
				font-family: sans-serif;
				-webkit-font-smoothing: antialiased;
				font-size: 14px;
				line-height: 1.4;
				margin: 0;
				padding: 0;
				-ms-text-size-adjust: 100%;
				-webkit-text-size-adjust: 100%;
			}

			table {
				border-collapse: separate;
				mso-table-lspace: 0pt;
				mso-table-rspace: 0pt;
				width: 100%;
			}
			table td {
				vertical-align: top;
			}

			ul {
				padding-left: 0;
			}

			.body {
				background-color: #f6f6f6;
				width: 100%;
			}

			.container {
				display: block;
				margin: 0 auto !important;
				max-width: 580px;
				padding: 10px;
				width: 580px;
			}

			.content {
				box-sizing: border-box;
				display: block;
				margin: 0 auto;
				max-width: 580px;
				padding: 10px;
			}

			.main {
				background: #ffffff;
				border-radius: 3px;
				width: 100%;
			}

			.wrapper {
				box-sizing: border-box;
				padding: 20px;
			}

			.content-block {
				padding-bottom: 10px;
				padding-top: 10px;
			}

			.footer {
				clear: both;
				margin-top: 10px;
				width: 100%;
			}

			h1,
			h2,
			h3,
			h4 {
				font-weight: 400;
				line-height: 1.4;
				margin: 0;
				margin-bottom: 16px;
			}

			h1 {
				font-size: 35px;
			}

			p,
			ul,
			ol {
				font-weight: normal;
				margin: 0;
				margin-bottom: 15px;
			}
			p li,
			ul li,
			ol li {
				list-style-position: inside;
				margin-left: 5px;
			}

			a {
				color: #3498db;
				text-decoration: underline;
			}

			figure {
				margin: 1em 0;
			}

			.btn {
				box-sizing: border-box;
				background-color: #fff;
				border: solid 1px #3498db;
				border-radius: 5px;
				box-sizing: border-box;
				color: #3498db;
				cursor: pointer;
				display: inline-block;
				font-size: 14px;
				font-weight: bold;
				margin: 0 6px 6px 0;
				padding: 12px 25px;
				text-decoration: none;
			}

			.btn.btn-primary {
				background-color: #3498db;
				border-color: #3498db;
				color: #fff;
			}

			.last { margin-bottom: 0; }
			.first { margin-top: 0; }
			.align-center { text-align: center !important; }
			.align-right { text-align: right !important; }
			.align-left { text-align: left !important; }
			.clear { clear: both; }
			.mt0 { margin-top: 0; }
			.mb0 { margin-bottom: 0; }

			.preheader {
				color: transparent;
				display: none;
				height: 0;
				max-height: 0;
				max-width: 0;
				opacity: 0;
				overflow: hidden;
				mso-hide: all;
				visibility: hidden;
				width: 0;
			}

			hr {
				border: 0;
				border-bottom: 1px solid #f6f6f6;
				margin: 20px 0;
			}

			@media only screen and (max-width: 620px) {
				table[class=body] h1 {
					font-size: 28px !important;
					margin-bottom: 10px !important;
				}
				table[class=body] p,
				table[class=body] ul,
				table[class=body] ol,
				table[class=body] td,
				table[class=body] span,
				table[class=body] a {
					font-size: 16px !important;
				}
				table[class=body] .wrapper,
				table[class=body] .article {
					padding: 10px !important;
				}
				table[class=body] .content {
					padding: 0 !important;
				}
				table[class=body] .container {
					padding: 0 !important;
					width: 100% !important;
				}
				table[class=body] .main {
					border-left-width: 0 !important;
					border-radius: 0 !important;
					border-right-width: 0 !important;
				}
				table[class=body] .img-responsive {
					height: auto !important;
					max-width: 100% !important;
					width: auto !important;
				}
			}

			@media all {
				.ExternalClass {
					width: 100%;
				}
				.ExternalClass,
				.ExternalClass p,
				.ExternalClass span,
				.ExternalClass font,
				.ExternalClass td,
				.ExternalClass div {
					line-height: 100%;
				}
				.apple-link a {
					color: inherit !important;
					font-family: inherit !important;
					font-size: inherit !important;
					font-weight: inherit !important;
					line-height: inherit !important;
					text-decoration: none !important;
				}
				.btn-primary:hover {
					background-color: #34495e !important;
					border-color: #34495e !important;
				}
			}
		</style>
		<style><?php do_action('bw_email_styles'); ?></style>
	</head>
	<body>
		<span class="preheader">
			<?php
				if(isset($preview)) {
					echo wp_kses_post($preview);
				}
			?>
		</span>
		<table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body">
			<tr>
				<td>&nbsp;</td>
				<td class="container">
					<div class="content">
						<table role="presentation" class="main">
							<tr>
								<td class="wrapper">
									<table role="presentation" border="0" cellpadding="0" cellspacing="0">
										<tr>
											<td>
												<?php if(isset($data['header'])): ?>
												<div class="header">
													<?php echo wp_kses_post(do_blocks($data['header'])); ?>
												</div>
												<?php endif; ?>
												<?php echo wp_kses_post(do_blocks($content)); ?>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
						<?php if(isset($data['footer'])): ?>
						<div class="footer">
							<?php echo wp_kses_post(do_blocks($data['footer'])); ?>
						</div>
						<?php endif; ?>
					</div>
				</td>
				<td>&nbsp;</td>
			</tr>
		</table>
	</body>
</html>
