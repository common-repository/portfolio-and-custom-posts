 <?php 
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'pacp_post_table'; 
    
    $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE `type` = %s", 'taxonomy'));
    $num_results = count($result);
    wp_enqueue_style('pacp-styles-css', plugins_url('../../assets/css/pacp-styles.css' , __FILE__ ), false, '1.0', 'all' );
    wp_enqueue_script('pacp-admin-script', plugins_url( '../../assets/js/admin-script.js' , __FILE__ ), array('jquery'), '1.0', false);
    wp_localize_script('pacp-admin-script', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'taxonomy_nonce' => wp_create_nonce('pacp_triger_taxonomies_nonce')
    ));
?>
<div class="pacp-headerbar">
    <h1 class="pacp-page-title"><?php echo esc_html('Taxonomies'); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=pacp-taxonomy-add')); ?>" class="pacp-btn pacp-btn-sm">
        <i class="pacp-icon pacp-icon-plus"></i><?php echo esc_html('Add New'); ?>
    </a>

</div>
<div class="pacp-body taxonomy-content">
  
  <?php if ($num_results == 0): ?>
    <div class="subsubsub_content">
        <ul class="subsubsub">
            <li class="all">
                <a href="#" class="current" aria-current="page"><?php echo esc_html('All'); ?> <span class="count"><?php echo esc_html('(0)'); ?></span></a></li>
        </ul>
    </div>
    <div class="posts_content">
        <form id="posts-filter" method="get">
            <?php wp_nonce_field( 'posts_filter', 'posts_filter_nonce' ); ?>
           <div class="pacp-no-post-types-wrapper">
              <div class="pacp-no-post-types-inner">
                <img src="<?php echo esc_url(plugins_url('../../assets/images/empty-post-types.svg', __FILE__)); ?>"/>
                 <h2><?php echo esc_html('Add Your First Taxonomy'); ?></h2>
                 <p><?php echo esc_html('Create custom taxonomies to classify post type content'); ?></p>
                 <a href="<?php echo esc_url(admin_url('admin.php?page=pacp-taxonomy-add')); ?>" class="pacp-btn"><i class="pacp-icon pacp-icon-plus"></i> <?php echo esc_html('Add Post Type'); ?></a>
              </div>
           </div>
        </form>
    </div>
  <?php else: ?>

    <div class="subsubsub_content">
        <ul class="subsubsub">
            <li class="all">
                <a href="#" class="current" aria-current="page"><?php echo esc_html('All'); ?> <span class="count">(<?php echo esc_html($num_results); ?>)</span></a></li>
        </ul>
    </div>
    <div class="posts_content">

 
        <?php 
            wp_enqueue_script('moment');
            wp_enqueue_style('pacp-bootstrap4-css', plugins_url( '../../assets/css/dataTables.bootstrap4.min.css' , __FILE__ ), false, '1.0', 'all' );
            wp_enqueue_style('pacp-datatables-css', plugins_url( '../../assets/css/datatables.min.css' , __FILE__ ), false, '1.0', 'all' );
            wp_enqueue_script('pacp-jquery-dataTables', plugins_url( '../../assets/js/jquery.dataTables.min.js' , __FILE__ ), array('jquery', 'moment'), '1.0', false);
            wp_enqueue_script('pacp-dataTables-bootstrap4', plugins_url( '../../assets/js/dataTables.bootstrap4.min.js' , __FILE__ ), array('jquery'), '1.0', false);
        ?>

        <div class="container mt-5">
        <table id="pacp_listing_table" class="display" style="width:100%">
            <thead>
                <tr>
                    <th><input type="checkbox" id="select-all-bottom"></th>
                    <th><?php echo esc_html('Title'); ?></th>
                    <th><?php echo esc_html('Slug'); ?></th>
                    <th><?php echo esc_html('Taxonomies'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $key => $value): ?>
                <tr>
                    <td><input type="checkbox" class="bulk-checkbox"></td>
                    <td>
                        <a href="<?php  esc_url(admin_url()); ?>admin.php?page=pacp-taxonomy-add&edit=<?php echo esc_attr($value->id); ?>"><?php echo ($value->title)? esc_html($value->title) :''; ?></a>
                        <?php if ($value->status == 0): ?>
                            — <span class="post-state"><span class="dashicons dashicons-hidden"></span>  <?php echo esc_html('Inactive'); ?></span>
                        <?php endif; ?>
                        <div class="action_container">
                            <span class="edit">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=pacp-taxonomy-add&edit=' . esc_attr($value->id))); ?>" aria-label="Edit <?php echo esc_attr($value->title); ?>"><?php echo esc_html('Edit'); ?></a> |
                            </span>
                            <span>
                                <a class="pacdduplicate" href="#" data-id="<?php echo esc_attr($value->id); ?>" aria-label="Duplicate this item"><?php echo esc_html('Duplicate'); ?></a> |
                            </span>
                            <span>
                                <?php if ($value->status == 1): ?>
                                    <a class="pacdactivate" href="#" data-id="<?php echo esc_attr($value->id); ?>"><?php echo esc_html('Deactivate'); ?></a>
                                <?php else: ?>
                                    <a class="pacddeactivate" href="#" data-id="<?php echo esc_attr($value->id); ?>"><?php echo esc_html('Activate'); ?></a>
                                <?php endif; ?>
                                |
                            </span>
                            <span>
                                <a class="trash" href="#" data-id="<?php echo esc_attr($value->id); ?>" aria-label="Move <?php echo esc_attr($value->title); ?> to the Trash"><?php echo esc_html('Delete'); ?></a>
                            </span>

                        </div>
                    </td>
                    <td><?php echo esc_attr($value->singular_name); ?></td>
                    <td><?php echo esc_attr($value->pacp_taxonomies); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th></th>
                    <th><?php echo esc_html('Title'); ?></th>
                    <th><?php echo esc_html('Slug'); ?></th>
                    <th><?php echo esc_html('Taxonomies'); ?></th>
                </tr>
            </tfoot>
        </table>

       <!--  <div class="bulk-action-selector-bottom mt-3">
            <button class="btn btn-primary">Apply Bulk Action</button>
        </div> -->
    </div>

</div>
<?php endif; ?>
</div>  