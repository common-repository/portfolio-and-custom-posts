<?php 
	if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }
	wp_enqueue_style('pacp-styles-css', plugins_url('../../assets/css/pacp-styles.css' , __FILE__ ), false, '1.0', 'all' );
	wp_enqueue_script('pacp-admin-script', plugins_url( '../../assets/js/admin-script.js' , __FILE__ ), array('jquery'), '1.0', false);
	wp_localize_script('pacp-admin-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
?>
<div class="pacp-headerbar">
	<h1 class="pacp-page-title"><?php echo esc_html('Setting'); ?></h1>
</div>
<div class="pacp-body">
	<br>
	<?php 
	    global $wpdb;
	    $table_name = $wpdb->prefix . 'pacp_post_setting';
	    $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE `setting` = %s",'per_page'))[0];
	?>
	<form id="setting_post">
		<?php wp_nonce_field( 'setting_post', 'setting_post_nonce' ); ?>
		<div class="inside">
            <div class="pacp-field pacp-field-text pacp-field-name is-required">
                <div class="pacp-label">
                    <label for="pacp_post_type-labels-name"><?php echo esc_html('Post per pages'); ?> <span class="pacp-required">*</span></label>
                </div>
                <div class="pacp-input" style="display: flex;">
                    <div class="pacp-input-wrap">
                    	<input type="number" id="setting_per-pages" class="setting_per-pages" name="setting_per_pages" required="required" value="<?php echo isset($result->data) ? esc_attr($result->data) : ''; ?>" style="width: 100%;">
                    </div>
                	<button class="pacp-btn post_save_btn setting_per-pages" type="submit"><?php echo esc_html('Save Changes'); ?></button>
                </div>
            </div>
        </div>
	</form>
</div>