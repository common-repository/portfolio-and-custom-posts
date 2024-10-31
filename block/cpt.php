<?php 
	if ( ! defined( 'ABSPATH' ) ) {
	    exit; // Exit if accessed directly
	}

	function pacp_get_PACP_post_data($data) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'pacp_post_table';
		
		$cache_key = 'pacp_post_data_' . md5($data);
		$cached_data = wp_cache_get($cache_key, 'pacp_post_data');
		if ($cached_data === false) {
			$result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE `type` = %s",$data));
			wp_cache_set($cache_key, $result, 'pacp_post_data');
		} else {
			$result = $cached_data;
		}
		return $result;
	}
	function pacp_create_PACP_post_type() {
		$result = pacp_get_PACP_post_data('post');
	    foreach ($result as $key => $value) {
	    	if ($value->status == 1) {
		    	$public = ($value->public == 1) ? true : false;
	    		$hierarchical = ($value->hierarchical == 1) ? true : false;
				$advanced_settings = json_decode($value->advanced_settings);
				$data = $advanced_settings->supports;
				
				add_filter('archive_template', function ($template) use ($value) {
	                global $post_type;
	                if ($post_type == $value->post_type) {
	                    $plugin_dir = plugin_dir_path(__FILE__).'../view/frontend/templates';
	                    return "$plugin_dir/pacp_archive.php";
	                }
	                return $template;
	            });

			    register_post_type( $value->post_type, array(
                    'labels' => array(
                        'name' => sprintf(
                            /* translators: Post type title placeholder */
                            esc_html__( 'Custom Post Type: %s', 'portfolio-and-custom-posts' ),
                            esc_html( $value->title )
                        ),
                        'singular_name' => sprintf(
                            /* translators: Post type singular name placeholder */
                            esc_html__( 'Custom Post Type: %s', 'portfolio-and-custom-posts' ),
                            esc_html( $value->singular_name )
                        ),
                    ),
                    'public'         => $public,
                    'show_in_menu'   => false,
                    'has_archive'    => $hierarchical,
                    'rewrite'        => array( 'slug' => $value->post_type ),
                    'supports'       => $data,
                ) );


			}
	    }
	}
	add_action('init', 'pacp_create_PACP_post_type');



function pacp_create_PACP_post_taxonomy() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'pacp_post_table';

    // Fetch taxonomy data from cache or database
    $taxonomies = wp_cache_get('pacp_post_taxonomies', 'pacp_post_data');
    if ($taxonomies === false) {
        // Data not found in cache, fetch from database
        $result = pacp_get_PACP_post_data('taxonomy');
        $taxonomies = [];
        foreach ($result as $key => $value) {
            if ($value->status == 1) {
                $public = ($value->public == 1) ? true : false;
                $hierarchical = ($value->hierarchical == 1) ? true : false;

                $postTypes = [];
                $postType = $value->post_type;
                if (strpos($postType, ',') !== false) {
                    $ids = explode(',', $postType);
                    foreach ($ids as $key => $values) {
                        $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE `id` = %s",$values));
                        if (!empty($result)) {
                            $result = $result[0];
                            $postTypes[] = $result->post_type;
                        }
                    }
                } else {
                    $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE `id` = %s", $postType));
                    if (!empty($result)) {
                        $result = $result[0];
                        $postTypes[] = $result->post_type;
                    } else {
                        $postTypes[] = '';
                    }
                }

                // Add taxonomy data to array
                $taxonomies[] = array(
                    'name' => $value->pacp_taxonomies,
                    'post_types' => $postTypes,
                    'label' => $value->title,
                    'hierarchical' => $hierarchical,
                    'public' => $public,
                    'rewrite' => array( 'slug' => $value->pacp_taxonomies ),
                );
            }
        }

        // Cache the fetched data
        wp_cache_set('pacp_post_taxonomies', $taxonomies, 'pacp_post_data');
    }

    // Register taxonomies
    foreach ($taxonomies as $taxonomy) {
        register_taxonomy(
            $taxonomy['name'],
            $taxonomy['post_types'],
            array(
                'label' => $taxonomy['label'],
                'hierarchical' => $taxonomy['hierarchical'],
                'public' => $taxonomy['public'],
                'show_ui' => true,
                'rewrite' => $taxonomy['rewrite'],
            )
        );
    }
}
add_action('init', 'pacp_create_PACP_post_taxonomy');

