# BRS Auto Apply Coupon

Automatically applies a WooCommerce coupon during a user-defined date and time window.  
Provides an admin settings page to configure the coupon code, start/end datetime, and the success message displayed to customers.  
All other coupon rules (categories, limits, amounts, usage restrictions) are controlled entirely within the WooCommerce coupon.

## Features
- Auto-apply coupon during a defined date/time window  
- Admin settings page under WooCommerce  
- Customizable success message  
- Clean separation of logic (admin and handler classes)  
- Requires WooCommerce  
- Safely self-deactivates if WooCommerce is not active  

## Requirements
- WordPress 6+  
- WooCommerce 8+  
- PHP 7.4+ recommended  

## Installation
1. Upload the plugin folder to `/wp-content/plugins/brs-auto-apply-coupon/`
2. Activate the plugin in **Plugins → Installed Plugins**
3. Ensure WooCommerce is active
4. Navigate to **WooCommerce → Auto Coupon Settings** to configure

## Usage
1. Create a WooCommerce coupon  
2. Enter the coupon code in the plugin settings  
3. Set the start and end date/time  
4. Enter a custom success message  
5. Save changes  

The coupon will be automatically applied to the customer’s cart within the active date window.

## Development
The plugin is organized as follows:

