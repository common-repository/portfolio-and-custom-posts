<?php 
	if ( ! defined( 'ABSPATH' ) ) {
	    exit; // Exit if accessed directly
	}

	wp_enqueue_style('pacp-styles-css', plugins_url('../../assets/css/pacp-styles.css' , __FILE__ ), false, '1.0', 'all' );
	wp_enqueue_style('pacp-select2-css', plugins_url('../../assets/css/select2.min.css' , __FILE__ ), false, '1.0', 'all' );
	wp_enqueue_script('pacp-select2', plugins_url( '../../assets/js/select2.min.js' , __FILE__ ), array('jquery'), '1.0', false);

    global $wpdb;
    $table_name = $wpdb->prefix . 'pacp_post_table';
     $result_post = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE `type` = %s",'post')); 

    if ( isset( $_GET['edit'] ) && !empty( $_GET['edit'] ) ) {
			$id = isset( $_GET['edit'] ) ? absint( $_GET['edit'] ) : 0;
			$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE `type` = 'taxonomy' AND `id` = %d", $id ) )[0];
	}
?>

<div class="pacp-headerbar pacp-headerbar-field-editor">
	<div class="pacp-headerbar-inner">
		<div class="pacp-headerbar-content">
			<?php if (isset($_GET['edit']) && !empty($_GET['edit'])): ?>
				<h1 class="pacp-page-title"><?php echo esc_html('Edit Taxonomy'); ?></h1>
			<?php else: ?>
				<h1 class="pacp-page-title"><?php echo esc_html('Add New Taxonomy'); ?></h1>
			<?php endif; ?>
		</div>

		<div class="pacp-headerbar-actions" id="submitpost">
			<?php if (isset($_GET['edit']) && !empty($_GET['edit'])): ?>
				<button class="pacp-btn post_update_btn pacp-publish disabled" id="update_publish" type="button"><?php echo esc_html('Update Changes'); ?></button>
			<?php else: ?>
				<button class="pacp-btn post_save_btn pacp-publish disabled" id="top_publish" type="button"><?php echo esc_html('Save Changes'); ?></button>
			<?php endif; ?>
		</div>
	</div>
</div>
<div class="posts_content">
	<?php if (isset($_GET['edit']) && !empty($_GET['edit'])): ?>
		<form id="pacp_taxonomy_update">
	<?php else: ?>
		<form id="pacp_taxonomy">
	<?php endif; ?>
	    <div id="poststuff">
	        <div id="post-body" class="metabox-holder columns-1">
	            <div id="postbox-container-2" class="postbox-container">
	                <div id="normal-sortables" class="meta-box-sortables ui-sortable ui-sortable-disabled">
	                    <div id="pacp-basic-settings" class="postbox">
	                        
	                        <div class="inside">
	                            <div class="pacp-field pacp-field-text pacp-field-name is-required">
	                                <div class="pacp-label">
	                                    <label for="pacp_post_type-labels-name"><?php echo esc_html('Plural Label'); ?> <span class="pacp-required">*</span></label>
	                                </div>
	                                <div class="pacp-input">
	                                    <div class="pacp-input-wrap">
	                                    	<input type="text" id="pacp_post_type-labels-name" class="pacp_plural_label" name="name" placeholder="Genres" required="required" value="<?php echo isset($result->title) ? esc_attr($result->title) : ''; ?>" />
	                                    </div>
	                                </div>
	                            </div>
	                            <div class="pacp-field pacp-field-text pacp-field-singular-name is-required" data-name="singular_name">
	                                <div class="pacp-label">
	                                    <label for="pacp_post_type-labels-singular_name"><?php echo esc_html('Singular Label'); ?> <span class="pacp-required">*</span></label>
	                                </div>
	                                <div class="pacp-input">
	                                    <div class="pacp-input-wrap">
	                                        <input type="text" id="pacp_post_type-labels-singular_name" class="pacp_slugify_to_key pacp_singular_label" name="singular_name" placeholder="Genre" required="required" value="<?php echo isset($result->singular_name) ? esc_attr($result->singular_name) : ''; ?>" />

	                                    </div>
	                                </div>
	                            </div>
	                            <div class="pacp-field pacp-field-text pacp-field-post-type is-required">
	                                <div class="pacp-label">
	                                    <label for="pacp_post_type-post_type"><?php echo esc_html('Taxonomy Key'); ?> <span class="pacp-required">*</span></label>
	                                </div>
	                                <div class="pacp-input">
	                                    <div class="pacp-input-wrap">
	                                    	<input type="text" id="pacp_post_type-post_type" class="pacp_slugified_key" name="taxonomy_type" placeholder="genre" maxlength="20" required="required" value="<?php echo isset($result->pacp_taxonomies) ? esc_attr($result->pacp_taxonomies) : ''; ?>" />
	                                    </div>
	                                    <p class="description"><?php echo esc_html('Lower case letters, underscores and dashes only, Max 32 characters'); ?>.</p>
	                                </div>
	                            </div>

	                            <div class="pacp-field pacp-field-text pacp-field-post-type is-required">
	                                <div class="pacp-label">
	                                    <label for="pacp_post_type-post_type"><?php echo esc_html('Post Types'); ?> </label>
	                                </div>
	                                <div class="pacp-input">
	                                    <div class="pacp-input-wrap">
	                                    	<?php
											    $post_type = explode(',', @$result->post_type);
											?>
											<select id="multi-select-box" multiple name="posts[]">
											    <?php foreach ($result_post as $key => $value): ?>
											        <?php
											        $optionValue = isset($value->id) ? esc_attr($value->id) : '';
											        $optionText = isset($value->title) ? esc_html($value->title) : '';
											        $isSelected = !empty($post_type) && in_array($value->id, $post_type);
											        ?>
											        <option value="<?php echo esc_attr($optionValue); ?>" <?php echo $isSelected ? esc_html('selected') : ''; ?>>
													    <?php echo esc_html($optionText); ?>
													</option>
											    <?php endforeach; ?>
											</select>

	                                    	<p class="description"><?php echo esc_html('One or many post types that can be classified with this taxonomy'); ?>.</p>
	                                	</div>
	                            	</div>

	                            <br> 
	                            <hr>
	                            <br> 
	                            <div class="pacp-field pacp-field-text pacp-field-post-type is-required">
	                                <div class="pacp-input">
	                                    <div class="pacp-input-wrap d-flex">
	                                    	<label class="pacp_switch_btn">
											  <input value="1" name="public" type="checkbox" <?php if (@$result->public == 1) { echo esc_html('checked'); } ?>>
											  <span class="slider round"></span>
											</label>
											<label><b><?php echo esc_html('Public'); ?></b> <br> <?php echo esc_html('Makes a taxonomy visible on the frontend and in the admin dashboard'); ?>.</label>
	                                	</div>
	                            	</div>
	                            </div>

	                            <div class="pacp-field pacp-field-text pacp-field-post-type is-required">
	                                <div class="pacp-input">
	                                    <div class="pacp-input-wrap d-flex">
	                                    	<label class="pacp_switch_btn">
											  <input value="1" name="hierarchical" type="checkbox" <?php if (@$result->hierarchical == 1) { echo esc_html('checked'); } ?>>
											  <span class="slider round"></span>
											</label>
											<label><b><?php echo esc_html('Hierarchical'); ?></b> <br><?php echo esc_html('Hierarchical taxonomies can have descendants (like categories)'); ?>.</label>
	                                	</div>
	                            	</div>
	                            </div>


	                            <div class="pacp-field pacp-field-text pacp-field-post-type is-required">
	                                <div class="pacp-input">
	                                    <div class="pacp-input-wrap d-flex">
	                                    	<label class="pacp_switch_btn">
											  <input value="1" name="status" type="checkbox" <?php if (@$result->status == 1 || !isset($_GET['edit'])) { echo esc_html('checked'); } ?>>
											  <span class="slider round"></span>
											</label>
											<label><b><?php echo esc_html('Active'); ?></b> <br> <?php echo esc_html('Active post types are enabled and registered with WordPress'); ?>.</label>
	                                	</div>
	                            	</div>
	                            </div>
	                        </div>
	                    </div>  
	                </div>
	            </div>
	        </div>
	        <br class="clear" />
	    </div>
	    <?php if (isset($_GET['edit']) && !empty($_GET['edit'])): ?>
			<button class="pacp-btn post_update_btn pacp-publish disabled" id="update" type="submit" style="float: right;"><?php echo esc_html('Update Changes'); ?></button>
		<?php else: ?>
			<button class="pacp-btn post_save_btn pacp-publish disabled" id="publish" type="submit" style="float: right;"><?php echo esc_html('Save Changes'); ?></button>
		<?php endif; ?>
	    
	</form>

</div>


<script>
    jQuery(document).ready(function($) {
    	jQuery('#multi-select-box').select2({
    		placeholder: "Select"
    	});

     	$('input#pacp_post_type-post_type').on('input', function() {
            var inputValue = $(this).val();
            var transformedValue = transformText(inputValue);
            $(this).val(transformedValue);
        });
        function transformText(inputText) {
            var lowercasedText = inputText.toLowerCase();
            var transformedText = lowercasedText.replace(/[^a-zA-Z0-9]/g, '-');
            return transformedText;
        }


        $('#top_publish').on('click', function() {
            $('#publish').click();
        });

        $('#update_publish').on('click', function() {
            $('#update').click();
        });
	});
  </script>
  	<?php if (isset($_GET['edit']) && !empty($_GET['edit'])): ?>
  		<?php $id = isset($_GET['edit']) ? absint($_GET['edit']) : 0; ?>
  		<script>
    		jQuery(document).ready(function($) {
		        $("form#pacp_taxonomy_update").on("submit", function(e) {
		            e.preventDefault();

		            $("#update_publish").prop("disabled", true);
            		$("#update").prop("disabled", true);

		            var name = $('input#pacp_post_type-labels-name').val();
		            var singular = $('input#pacp_post_type-labels-singular_name').val();
		            var post_type = $('input#pacp_post_type-post_type').val();

		            if (name != '' && singular != '' && post_type != '') {
		            	var formData = $(this).serialize();

		            	var selectedPosts = $('#multi-select-box').val();
            			formData += '&selected_posts=' + selectedPosts.join(',');

		            	var nonce = '<?php echo esc_js( wp_create_nonce( "pacp_taxonomy_update" ) ); ?>';
		                $.ajax({
		                    type: "post",
		                    url: "<?php echo esc_url(admin_url('admin-ajax.php'));?>",
		                    data: {
		                        action:'pacp_update_taxonomy',
                        		id: <?php echo esc_html($id); ?>,
		                        formData:formData,
		                        pacp_taxonomy_update_nonce: nonce
		                    },
		                    beforeSend: function () {
		                        $('.ajax-loader').css("visibility", "visible");
		                    },
		                    complete: function () {
		                        $('.ajax-loader').css("visibility", "hidden");
		                    },
		                    success: function(response){
		                       if (response == 'exist') {
		                    		$('input#pacp_post_type-post_type + .error_m').remove();
		                    		$('input#pacp_post_type-post_type').focus();
		                    		$('input#pacp_post_type-post_type').after('<p class="error_m">Same Name Taxonomy already exist please change and try again</p>')
		                    	}else{
		                    		window.location.href = 'admin.php?page=pacp-taxonomy-type';
		                    	}
		                    }
		                  }); 
		            }
		        });
		    });
		</script>
	<?php else: ?>
	  	<script>
    		jQuery(document).ready(function($) {
		        $("form#pacp_taxonomy").on("submit", function(e) {
		            e.preventDefault();

		            $("#top_publish").prop("disabled", true);
            		$("#publish").prop("disabled", true);

		            var name = $('input#pacp_post_type-labels-name').val();
		            var singular = $('input#pacp_post_type-labels-singular_name').val();
		            var post_type = $('input#pacp_post_type-post_type').val();

		            if (name != '' && singular != '' && post_type != '') {
		            	var formData = $(this).serialize();
		            	var selectedPosts = $('#multi-select-box').val();
            			formData += '&selected_posts=' + selectedPosts.join(',');
            			
		            	var nonce = '<?php echo esc_js( wp_create_nonce( "pacp_taxonomy" ) ); ?>';

		                $.ajax({
		                    type: "post",
		                    url: "<?php echo esc_url(admin_url('admin-ajax.php'));?>",
		                    data: {
		                        action:'pacp_add_taxonomy',
		                        formData:formData,
		                        pacp_taxonomy_nonce: nonce
		                    },
		                    beforeSend: function () {
		                        $('.ajax-loader').css("visibility", "visible");
		                    },
		                    complete: function () {
		                        $('.ajax-loader').css("visibility", "hidden");
		                    },
		                    success: function(response){
		                    	if (response == 'exist') {
		                    		$('input#pacp_post_type-post_type + .error_m').remove();
		                    		$('input#pacp_post_type-post_type').focus();
		                    		$('input#pacp_post_type-post_type').after('<p class="error_m">Same Name Taxonomy already exist please change and try again</p>')
		                    	}else{
		                        	window.location.href = 'admin.php?page=pacp-taxonomy-type';
		                        }
		                    }
		                  }); 
		            }
		        });
		    });
		</script>
	<?php endif; ?>