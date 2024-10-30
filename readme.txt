=== Booking Weir ===
Contributors: websevendev, freemius
Tags: booking, calendar, events, appointment, reservation, scheduling, woocommerce
Requires at least: 5.6
Tested up to: 5.9.0
Requires PHP: 7.0
Stable tag: 1.0.11
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display an interactive calendar that allows users to select a time for their booking.

== Description ==

Booking Weir allows you to display a simple calendar where users can select a suitable time for their booking.

== Demo ==

#[Demos](https://chap.website/booking-weir/demos)

== Use cases ==

1. **Allow users to select any time of their choice to book** - Specify your opening hour, closing hour, how much time to leave between events and the user can select anything that fits the criteria.
2. **Allow users to book in predefined time slots** - Add bookable time slots into the calendar, the user can click on it to book it.
3. **Create bookable events** - Add an event in the calendar that can then be booked by multiple users (like selling tickets for an event).
4. **Allow users to book services with predefined duration and price** - Users can select a service and then place it in the calendar to a time of their choosing (premium feature).
5. **Use with WooCommerce** - Add a WooCommerce product that displays a booking calendar, allowing the user to select a time and add it to cart. The booking can then be finalized using the WooCommerce checkout process.
6. **Use as a simple calendar displaying events** - If you just need a calendar that can display events then this plugin can be used as well.

== Free features ==

1. **Unlimited calendars**
2. **Invoice and reminder e-mails**
3. **Generate PDF invoices**
4. **PayPal payment**
5. **Stripe payment**
6. **WooCommerce integration** - Attach the booking calendar to a WC product, allowing the use of all WC features such as taxes, coupons and payment methods.

== Premium features ==

1. **Advanced pricing** - Modify prices based on date, time, duration, coupons and more.
2. **Extras** - Allow selecting additional free or paid services along with the booking.
3. **Services** - Allow users to book services with predefined duration and price.
4. **Custom fields** - Add additional fields to the booking form.
5. **Repeating events** - Configure events to recur automatically.
6. **Related events** - Define parent - child relations to share events between calendars.

== Getting started ==

1. Navigate to **Booking -> Calendars**.
2. Add your first calendar.
3. Configure the **Settings** of your calendar.
4. Add the calendar to any page by using a shortcode or a block.

== Installation ==

1. Install and activate the plugin.


== Screenshots ==

1. Booking calendar on the front end
2. Booking Weir back end

== Changelog ==

= 1.0.11 =
* Added min-width to Extra name.
* Added links for services.
* Freemius SDK update.

= 1.0.10 =
* Switch all validation to `bw_validate_event` JS filter.
* Add validation for event start and end being on the same day.

= 1.0.9 =
* Add `bw_validate_event` JS filter.
* Fix being unable to clear some setting fields.
* Add availability setting for services (client-side).
* Fix core blocks sometimes not registered in editor.
* Add "Duration between" price rule.

= 1.0.8 =
* Fix integer value used for service display price on the front end instead of float.
* Round service display price to 2 decimal places.
* Filter out disabled extras when determining if the calendar has visible extras.
* Add class names to booking modals.
* Show tax class settings for Booking products.

= 1.0.7 =
* Update packages.
* Fix broken drag-resizing of events.
* Fix client side UTC offset detection.
* Don't send reminder when event status is "Awaiting".
* Add `bw_event_reminder_in` filter.
* Add `bw_mail_html` filter.

= 1.0.6 =
* Fix block editor crash with WP 5.9.
* Add option to disable invoice e-mail.
* Add option to send e-mail when status is changed to "Confirmed".
* About and Logs pages, adding/duplicating/deleting/importing/exporting calendars now require admin capabilities.
* Add `bw_after_add_booking_to_cart` hook.
* Add `bw_service_price_text` JS filter.
* Added price rule that can match a selected extra.
* Event post type strings now have context.
* Multiple `Add X` strings replaced with just `Add`.
* Calendar block now supports `alignwide` and `alignfull`.
* Fix booking info modal cutoff on mobile.
* "Per hour" extra renamed to "Duration".
* Extra name strings now have context.
* Extras can now be visible for only specific services.
* Change Service duration and price labels to not jump around as much when editing.
* Added experimental "white label" mode.
* Added experimental services booking mode without calendar.

= 1.0.5 =
* Don't open booking/info modal automatically after adding to cart.
* Allow to add title and content to slots.
* Use WordPress date format in invoice template.
* Add event color coding legend to back end.
* Fix icon color in highlighted events.
* Include `load_plugin_textdomain` in free version.

= 1.0.4 =
* Include `.pot` file in the free plugin.
* Added option to customize the price text of WooCommerce Booking products.
* Avoid adding notice-related JavaScript on AMP pages.
* Removed check for payment methods when using WooCommerce.
* Added functionality to link to events and slots.
* Fixed an issue with WC product price overriding booking price.
* Fixed incorrect price display in WC mini cart.
* Added bookable event name/service name to WC product meta.

= 1.0.3 =
* Added bookable events.
* Added Stripe payment method.
* Don't display payment method and type inputs when the booking price is 0.
* Set initial status to "Awaiting" instead of "Pending payment" when the booking price is 0.
* Reduce Service and Event info modal size.

= 1.0.2 =
* Update for WordPress 5.8 compatibility.
* Added services.

= 1.0.1 =
* Added file input field.
* Added actions `bw_before_create_booking` and `bw_after_create_booking`.

= 1.0.0 =
* Initial release.
