Don't store custom translations in this folder, they will be lost on plugin updates.

To use the bundled (Estonian) translations add the following filters to your child theme's functions.php:

add_filter('bw_plugin_textdomain_path', function() {
	return 'booking-weir/languages';
});

add_filter('bw_languages_path', function() {
	return plugin_dir_path(BOOKING_WEIR_FILE) . 'languages';
});
