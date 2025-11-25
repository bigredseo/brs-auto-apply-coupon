# Changelog

## 1.0.4
### Added
- Added validation to prevent auto-applying invalid coupons that block checkout.
- Added customer notice when coupon cannot be applied due to product restrictions.
- Added mixed-cart detection to notify customers when some cart items are excluded from coupon discounts.
- Improved stability by preventing repeated re-application loops during validation.
- Added WooCommerce session–based notice tracking to prevent duplicate notices on checkout.
- Added logic to clear stale notice flags when coupon becomes valid again.
- Improved handling of excluded-only carts by preventing WooCommerce’s native invalid-coupon error from appearing.

---

## 1.0.3
### Added
- Display current server time and timezone on settings screen.
- Improved formatting (MM/DD/YYYY, no seconds) for readability.

### Changed
- Settings page now uses a unified notice box to present time information more clearly.

---

## 1.0.2
### Added
- Added "Settings" link to the plugin row on the Plugins screen.
- Integrated Plugin Update Checker (v5.6) to enable GitHub-based automatic updates.
- Added author URL to the plugin header so "Big Red SEO" links to https://www.bigredseo.com.

### Updated
- Improved metadata in the main plugin file to reflect correct author URL.
- Updated Plugin Update Checker integration to use namespaced v5.6 classes.
- Corrected GitHub repository URL to include `.git` suffix.
- Added conditional handling for VCS API initialization to avoid fatal errors.

---

## 1.0.1
### Fixed
- Corrected GitHub Actions release workflow to properly auto-detect the plugin main file and avoid matching other plugin headers.

---

## 1.0.0
### Added
- Initial release of **BRS Auto Apply Coupon**
- Automatic coupon application based on date/time window
- Admin settings page for coupon code, start/end datetime, and custom message
- WooCommerce dependency checks with self-deactivation
- Modular structure with separated admin and handler classes
