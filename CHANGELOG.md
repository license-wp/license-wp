### 1.1.0: September 30, 2026
* Added: API products get a "Requires PHP" field and an optional legacy release (version, package, requirements). Sites whose WordPress or PHP is too old for the current release get the legacy release, or no update.
* Added: Update checks send requires, requires_php and tested, and the plugin information sends requires_php.
* Added: The activation and update APIs also accept POST, so clients can keep the licence key and email out of URLs.
* Added: The plugin declares compatibility with WooCommerce's HPOS order storage.
* Changed: Renewing an expired licence no longer gets the 30% discount. The discount of an early renewal is filterable with license_wp_renewal_discount.
* Changed: The licence tables get indexes on the columns they are searched by, added on the first admin page load after the update.
* Removed: The PHP 5.3 check and its wp-update-php dependency.
* Removed: The Grunt build tooling. The built CSS and JS stay in the repository.
* Security: The licence and activation lists no longer put the licence key filter and sort column into SQL unescaped.
* Security: Bulk actions and the add and edit licence forms check the capability and the nonce.
* Security: Renewal and upgrade links stop when the licence, owner or email check fails, and upgrades only go to the offered options.
* Security: Deactivating a site from My Account, the upgrade form and its cart link need a nonce.
* Security: The lost licence form gives the same answer for every address and sends at most one email per address per 10 minutes.
* Security: Templates, emails, admin screens, notices and the plugin information escape licence keys, names, emails and URLs.
* Security: Licence keys come from a cryptographically secure random source.
* Security: The download log stores a validated IP address instead of the raw X-Forwarded-For header.
* Security: Parsedown is updated from 1.5.4 to 1.8.0.
* Fixed: The autoloader loads from the plugin folder, not from the working directory, which caused fatal errors in WP-CLI.
* Fixed: Licences without an expiry date no longer count as expired, are stored without a date, and can be renewed.
* Fixed: Checking whether a licence expired no longer moves its expiry date.
* Fixed: An upgrade order upgrades the licence it was bought for instead of reading one character of its key.
* Fixed: Orders are read, saved and deleted through WooCommerce, so licences keep working with HPOS order storage.
* Fixed: Download, renewal and deactivate links work for activation emails with a plus sign or an apostrophe.
* Fixed: The plugin information shows the last updated date of the API product.
* Fixed: The upgrade form only looks up a licence key in its own shortcode or form, not on every page with a license_key parameter.
* Fixed: The upgrade link no longer uses the deprecated get_page_by_title() and is left out when there is no upgrade page.
* Fixed: API requests without a request type, instance or plugin name no longer raise PHP warnings, and activating without a website is refused.

### 1.0.0: August xx, 2015
* Initial release