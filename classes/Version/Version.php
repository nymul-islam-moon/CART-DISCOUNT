<?php

namespace Cart\Discount\Version;

class Version
{
    public function __construct()
    {
        add_action('admin_init', [$this, 'check_php_version']);
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
    public function check_php_version()
    {
        if (version_compare(phpversion(), '7.2.0', '<')) {
            wp_die('This plugin requires PHP version 7.2.0 or higher. Please upgrade your PHP version.');
        }
    }
}