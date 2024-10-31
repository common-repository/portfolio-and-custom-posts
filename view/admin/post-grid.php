<?php 
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Include necessary WordPress files
require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');

// Create an instance of your custom list table class
$products_table = new PACP_Custom_Post_List_Table();
    
class PACP_Custom_Post_List_Table extends WP_Posts_List_Table {
    // Constructor
    public function __construct() {
        parent::__construct(array(
            'singular' => 'post',
            'plural'   => 'posts',
            'ajax'     => false,
        ));
    }

    public function pacp_extra_tablenav($which) {
        if ($which == 'top') {
            $terms = get_terms(array('taxonomy' => 'category','hide_empty' => false));
            ?>
            <div class="alignleft actions">
                <select name="filter_category">
                    <option value=""><?php echo esc_html('All Categories'); ?></option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo esc_attr($category->slug); ?>">
                            <?php echo esc_html($category->name); ?>  
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php submit_button('Filter', 'button', 'filter_category', false); ?>
            </div>
            <?php
        }
    }

 
    public function pacp_prepare_items() {
        // Verify nonce
        if ( isset( $_REQUEST['_wpnonce'] ) && !empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])), 'my_action_nonce' ) ) {
            // Nonce is verified, proceed with processing form data
            $category_filter = isset( $_REQUEST['filter_category'] ) ? sanitize_text_field( $_REQUEST['filter_category'] ) : '';
            $args = array(
                'post_type'      => 'post',
                'posts_per_page' => 20,
                'orderby'        => 'date',
                'order'          => 'DESC',
                'paged'          => $this->get_pagenum(),
                'category_name'  => $category_filter,
            );
            // Query posts
            $this->set_pagination_args( array(
                'total_items' => wp_count_posts( 'post' )->publish,
                'per_page'    => 20,
            ) );
            $this->items = get_posts( $args );
        } else {
            // Nonce verification failed, handle the error (e.g., show an error message, redirect, etc.)
            wp_die( 'Nonce verification failed.', 'Error' );
        }
    }

}

// Create the custom admin page
function pacp_custom_post_list_page() {
    ?>
        <div class="wrap">
            <h2><?php echo esc_html('Custom Post List'); ?></h2>
            <?php
                // Create an instance of your custom list table
                $list_table = new PACP_Custom_Post_List_Table();
                $list_table->pacp_prepare_items();
                // Display the category filter and post list
                $list_table->display();
             ?>
        </div>
    <?php
}

// Hook into admin_menu to add the custom admin page
function pacp_custom_admin_menu() {
    add_menu_page('Custom Post List', 'Custom Post List', 'manage_options', 'custom-post-list', 'pacp_custom_post_list_page');
}
add_action('admin_menu', 'pacp_custom_admin_menu');
