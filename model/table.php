<?php
   if ( ! defined( 'ABSPATH' ) ) {
       exit; // Exit if accessed directly
   }

   function pacp_portfolio_and_custom_posts_uninstall() {
       global $wpdb;
       $table_name = $wpdb->prefix . 'pacp_post_table';
       $table_setting = $wpdb->prefix . 'pacp_post_setting';
       
       // Check if the table exists in cache
       $cached_table_exists = wp_cache_get('pacp_table_exists');
       if ($cached_table_exists === false) {
           // Table existence not found in cache, fetch from database
           $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE $table_name"));

           // Cache the table existence for future use
           wp_cache_set('pacp_table_exists', $table_exists);
       } else {
           $table_exists = $cached_table_exists;
       }

       if ($table_exists == $table_name) {
           // Table exists, drop it
           $wpdb->query($wpdb->prepare("DROP TABLE IF EXISTS $table_name"));

           // Clear table existence cache
           wp_cache_delete('pacp_table_exists');
       }

       // Drop the second table
       $wpdb->query($wpdb->prepare("DROP TABLE IF EXISTS $table_setting"));
   }


    function pacp_create_post_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pacp_post_table';

     
        $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE $table_name"));

        
        if ( $table_exists != $table_name ) {
            $charset_collate = $wpdb->get_charset_collate();

             $sql = "CREATE TABLE $table_name (
                `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `type` varchar(220) NOT NULL,
                `status` INT DEFAULT 0,
                `title` varchar(220) DEFAULT NULL,
                `singular_name` varchar(220) DEFAULT NULL,
                `post_type` varchar(220) NOT NULL,
                `pacp_taxonomies` varchar(220) NOT NULL,
                `advanced_settings` text DEFAULT NULL,
                `public` INT DEFAULT 0,
                `hierarchical` INT DEFAULT 0,
                `created_at` timestamp,
                `updated_at` timestamp
             ) $charset_collate;";

             require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
             dbDelta($sql);
        }

        $table_setting = $wpdb->prefix . 'pacp_post_setting';
    
        $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE $table_setting"));

        if ($table_exists !== $table_setting ) {
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE $table_setting (
                `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `setting` varchar(220) NOT NULL,
                `data` INT DEFAULT 12
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);

        
            $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_setting WHERE `setting` = %s", 'per_page'));
            if (empty($result)) {
                $wpdb->insert($table_setting, array('setting' => 'per_page', 'data' => 12), array('%s', '%d'));
            }

        }
   }
add_action('after_setup_theme', 'pacp_create_post_table');