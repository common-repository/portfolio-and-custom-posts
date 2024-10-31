<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }

    function pacp_custom_template_include($template) {
        if (is_singular('products')) {
            // Use single-custom_post_type.php for single posts.
            // if (file_exists(plugin_dir_path(__FILE__) . 'templates/single-custom_post_type.php')) {
            //     return plugin_dir_path(__FILE__) . 'templates/single-custom_post_type.php';
            // }
        } elseif (is_post_type_archive('products')) {
            // Use archive-custom_post_type.php for archive pages.
            if (file_exists(plugin_dir_path(__FILE__) . 'view/frontend/templates/pacp_archive.php')) {
                return plugin_dir_path(__FILE__) . 'view/frontend/templates/pacp_archive.php';
            }
        }
        return $template;
    }
    add_filter('template_include', 'pacp_custom_template_include');

