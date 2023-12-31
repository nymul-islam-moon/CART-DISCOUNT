<?php

namespace Cart\Discount\Version;

class Version
{
    /**
     * Plugin Version
     *
     * The version number of the plugin.
     *
     * @since 1.0.0
     */
    const VERSION = '1.0.0';

    /**
     * Minimum PHP Version
     *
     * The minimum PHP version required by the plugin.
     *
     * @since 1.0.0
     */
    const PHP_VERSION = '7.2.0';

    /**
     * Database Version
     *
     * The version number of the plugin's database schema.
     *
     * @since 1.0.0
     */
    const DB_VERSION = '1.0.0';

    /**
     * CSS Version
     *
     * The version number for the plugin's CSS files.
     *
     * @since 1.0.0
     */
    const CSS_VERSION = '1.0.0';

    /**
     * JavaScript Version
     *
     * The version number for the plugin's JavaScript files.
     *
     * @since 1.0.0
     */
    const JS_VERSION = '1.0.0';

    public function __construct()
    {
        $this->init();
    }

    /**
     * Initialize the WordPress Plugin.
     *
     * This method is called during the plugin initialization process.
     * It performs essential tasks such as checking the plugin version
     * and verifying the PHP version compatibility.
     *
     * @since 1.0.0
     */
    public function init() {
        $this->check_version();
        $this->check_php_version();
    }


    /**
     * Check Plugin Version and Update if Necessary.
     *
     * This method checks the stored version of the plugin against the current version.
     * If no version is stored, it adds the current version to the options. If a version is already stored,
     * it updates the stored version to the current one.
     *
     * @since 1.0.0
     */
    private function check_version() {
        // Check if the plugin version is not stored
        if (!get_option('cart_discount_version')) {
            // Add the current plugin version to the options
            add_option('cart_discount_version', self::VERSION);
        } else {
            // Update the stored plugin version to the current one
            update_option('cart_discount_version', self::VERSION);
        }
    }


    /**
     * Check PHP Version Requirement.
     *
     * This function checks if the installed PHP version meets the minimum requirement.
     * If the PHP version is below the required version, it terminates the script and
     * displays an error message prompting the user to upgrade.
     *
     * @since 1.0.0
     */
    private function check_php_version()
    {
        if ( version_compare( phpversion(), self::PHP_VERSION, '<' ) ) {
            wp_die( 'This plugin requires PHP version ' . self::PHP_VERSION . ' or higher. Please upgrade your PHP version.' );
        }
    }
}