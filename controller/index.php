<?php 
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

add_action('wp_ajax_pacp_pacpCategoryfilter', 'pacp_categoryfilter_filter_function');
add_action('wp_ajax_nopriv_pacp_pacpCategoryfilter', 'pacp_categoryfilter_filter_function');

function pacp_categoryfilter_filter_function(){

    if (!isset($_POST['pacp_filter_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['pacp_filter_nonce'])), 'pacp_filter')) {
        wp_die('Nonce verification failed');
    } 

    $post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : '';

    $taxonomies = get_object_taxonomies($post_type, 'objects');
    $_data = [];
    foreach ($taxonomies as $key => $taxonomy) {
        $pattern = '/[^a-zA-Z0-9]/';
        $filter_name = preg_replace($pattern, '', $key);
        $name = isset($_POST[$filter_name]) ? sanitize_text_field($_POST[$filter_name]) : '';
        if (isset($name) && !empty($name) && $name != '') {
           $_data[$key] = $name;
        }
    }


    if (!empty($_data)) {

        $collection = [];
        foreach ($_data as $key => $category_ids) {

            $args = array(
            'post_type' => $post_type,
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'post_date',
            'order' => 'DESC',
            'tax_query' => array(
                    array(
                        'taxonomy' => $key,
                        'field' => 'term_id',
                        'terms' => $category_ids,
                        'operator' => 'IN',
                    ),
                ),
            );
            $query = new WP_Query($args);

            $result = array();
            if ($query->have_posts()) {
                // $results_total[] = $query->found_posts;
                while ($query->have_posts()) {
                    $query->the_post();
                    $title = get_the_title();
                    $url = get_permalink();
                    $image = get_the_post_thumbnail_url();
                    $excerpt = wp_trim_words(get_the_excerpt(), 20);
                    $result[] = array(
                        'title' => $title,
                        'url' => $url,
                        'img' => $image,
                        'excerpt' => $excerpt,
                    );
                }
            }
            $collection['data'] = $result;
        }

        $collections = array_values(array_merge_recursive(...array_values($collection)));  
        
        $collection['total'] = count($collections);

        if (!empty($collection)) {
            echo wp_json_encode($collection);
        }else{
            $result[] = ['html'=>'<p>No Products found</p>'];
            echo wp_json_encode($result);
        }
        
    }else{
        $args = array(
            'post_type' => $post_type,
            'posts_per_page' => 12,
            'post_status' => 'publish',
            'orderby' => 'post_date',
            'order' => 'DESC',
        );
        $query = new WP_Query($args);

        $collection = [];
        $result = array();
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $title = get_the_title();
                $image = get_the_post_thumbnail_url();
                $url = get_permalink();
                $excerpt = wp_trim_words(get_the_excerpt(), 20);
                $result[] = array(
                    'title' => $title,
                    'url' => $url,
                    'img' => $image,
                    'excerpt' => $excerpt,
                );
            }
            
        }

        $args = array('posts_per_page' => -1,'post_type' => $post_type,'post_status' => 'publish');
        $loop = new WP_Query($args);
        $total = $loop->found_posts;

        $collection['total'] = $total;
        $collection['data'] = $result;

        wp_reset_postdata(); 
        echo wp_json_encode($collection);
    }
    die();
}

add_action('wp_ajax_pacp_pacpload_more', 'pacp_pacpload_more_function');
add_action('wp_ajax_nopriv_pacp_pacpload_more', 'pacp_pacpload_more_function');

function pacp_pacpload_more_function(){
    if (!isset($_POST['pacp_load_more_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['pacp_load_more_nonce'])), 'pacp_load_more_nonce')) {
        wp_die('Nonce verification failed');
    }

    
    $post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : '';
    $data_id = isset($_POST['data_id']) ? sanitize_text_field($_POST['data_id']) : '';
    
    $args = array(
        'post_type' => $post_type,
        'posts_per_page' => $data_id,
        'post_status' => 'publish',
        'orderby' => 'post_date',
        'order' => 'DESC',
    );

    $query = new WP_Query($args);
    $collection = [];
    $result = array();
    if ($query->have_posts()) {
        $collection['total'] = $query->found_posts;
        while ($query->have_posts()) {
            $query->the_post();
            $title = get_the_title();
            $image = get_the_post_thumbnail_url();
            $url = get_permalink();
            $excerpt = wp_trim_words(get_the_excerpt(), 20);
            $result[] = array(
                'title' => $title,
                'url' => $url,
                'img' => $image,
                'excerpt' => $excerpt,
            );
        }
    }
    $collection['data'] = $result;
    $collection['count'] = $data_id;

    wp_reset_postdata(); 
    echo wp_json_encode($collection);
    die();
}
