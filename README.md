# CART-DISCOUNT WordPress Plugin

## Overview

CART-DISCOUNT is a WordPress plugin designed to enhance the functionality of the WooCommerce shopping cart. It adds a duplicate cart item from the parent item when the parent item's quantity is greater than or equal to 5 and removes the duplicate item when the parent item's quantity is less than 5.

## Plugin Details

- **Plugin Name:** CART-DISCOUNT
- **Plugin URI:** [https://github.com/nymul-islam-moon/CART-DISCOUNT](https://github.com/nymul-islam-moon/CART-DISCOUNT)
- **Description:** This plugin adds a duplicate cart item from the parent item when the parent item's quantity is >= 5 and removes the duplicate item when the parent item's quantity < 5.
- **Version:** 1.0.0
- **Requires at least:** WordPress 5.2
- **Requires PHP:** 7.2
- **Author:** Nymul Islam Moon
- **Author URI:** [https://github.com/nymul-islam-moon](https://github.com/nymul-islam-moon)
- **Text Domain:** wp-rest-plugin
- **License:** GPL v2 or later
- **License URI:** [http://www.gnu.org/licenses/gpl-2.0.txt](http://www.gnu.org/licenses/gpl-2.0.txt)
- **Update URI:** [https://github.com/nymul-islam-moon/CART-DISCOUNT](https://github.com/nymul-islam-moon/CART-DISCOUNT)

## Installation

1. Download the ZIP file from the [GitHub repository](https://github.com/nymul-islam-moon/CART-DISCOUNT).
2. Extract the ZIP file to your WordPress plugins directory.
3. Activate the CART-DISCOUNT plugin through the WordPress admin interface.

## Usage

Once the plugin is activated, it will automatically add and remove duplicate cart items based on the specified conditions. No additional configuration is required.

## Contributing

If you'd like to contribute to the development of this plugin, please follow the guidelines in the [CONTRIBUTING.md](CONTRIBUTING.md) file.

## Issues

If you encounter any issues with the plugin, please report them on the [GitHub Issues](https://github.com/nymul-islam-moon/CART-DISCOUNT/issues) page.

## License

This plugin is licensed under the GPL v2 or later. See the [LICENSE](LICENSE) file for details.

---

**Note:** This README template is a starting point. Feel free to customize it based on your specific requirements and preferences.

Method 1
/your-plugin-name
|-- admin
|   |-- assets
|   |   |-- css
|   |   |-- js
|   |   |-- images
|   |-- includes
|   |   |-- AdminClass.php
|   |-- views
|   |   |-- admin-page.php
|   |-- AdminController.php
|-- public
|   |-- assets
|   |   |-- css
|   |   |-- js
|   |   |-- images
|   |-- includes
|   |   |-- PublicClass.php
|   |-- views
|   |   |-- public-page.php
|   |-- PublicController.php
|-- assets
|   |-- css
|   |-- js
|   |-- images
|-- classes
|   |-- PluginClass.php
|-- languages
|-- vendor
|-- your-plugin-name.php