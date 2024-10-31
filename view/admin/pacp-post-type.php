<?php 
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
wp_enqueue_style('pacp-styles-css', plugins_url('../../assets/css/pacp-styles.css' , __FILE__ ), false, '1.0', 'all' );
wp_enqueue_style('pacp-select2-css', plugins_url('../../assets/css/select2.min.css' , __FILE__ ), false, '1.0', 'all' );
wp_enqueue_script('pacp-select2', plugins_url( '../../assets/js/select2.min.js' , __FILE__ ), array('jquery'), '1.0', false);

global $wpdb;
$table_name = $wpdb->prefix . 'pacp_post_table';

$result_taxonomy = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE `type` = %s",'taxonomy')); 

if ( isset( $_GET['edit'] ) && !empty( $_GET['edit'] ) ) {

    $id = isset( $_GET['edit'] ) ? absint( $_GET['edit'] ) : 0;
    $result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE `id` = %d", $id ) )[0];
    $advanced_settings = json_decode( $result->advanced_settings );
}

wp_enqueue_media();
?>

<div class="pacp-headerbar pacp-headerbar-field-editor">
	<div class="pacp-headerbar-inner">
		<div class="pacp-headerbar-content">
			<?php if (isset($_GET['edit']) && !empty($_GET['edit'])): ?>
				<h1 class="pacp-page-title"><?php echo esc_html('Edit Post Type'); ?></h1>
			<?php else: ?>
				<h1 class="pacp-page-title"><?php echo esc_html('Add New Post Type'); ?></h1>
			<?php endif; ?>
		</div>

		<div class="pacp-headerbar-actions" id="submitpost">
			<?php if (isset($_GET['edit']) && !empty($_GET['edit'])): ?>
				<button class="pacp-btn post_update_btn pacp-publish disabled" id="update_publish" type="button"><?php echo esc_html('Update Changes'); ?></button>
			<?php else: ?>
				<button class="pacp-btn post_save_btn pacp-publish" id="top_publish" type="button"><?php echo esc_html('Save Changes'); ?></button>
			<?php endif; ?>
		</div>
	</div>
</div>
<div class="posts_content">
	<?php if (isset($_GET['edit']) && !empty($_GET['edit'])): ?>
		<form id="pacp_post_update">
	<?php else: ?>
		<form id="pacp_post">
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
	                                    	<input type="text" id="pacp_post_type-labels-name" class="pacp_plural_label" name="name" placeholder="Movies" required="required"  value="<?php echo (isset($result->title)) ? esc_attr($result->title) : ''; ?>" />
	                                    </div>
	                                </div>
	                            </div>
	                            <div class="pacp-field pacp-field-text pacp-field-singular-name is-required" data-name="singular_name">
	                                <div class="pacp-label">
	                                    <label for="pacp_post_type-labels-singular_name"><?php echo esc_html('Singular Label'); ?> <span class="pacp-required">*</span></label>
	                                </div>
	                                <div class="pacp-input">
	                                    <div class="pacp-input-wrap">
	                                        <input type="text" id="pacp_post_type-labels-singular_name" class="pacp_slugify_to_key pacp_singular_label" name="singular_name" placeholder="Movie" required="required"  value="<?php echo (isset($result->singular_name))? esc_attr($result->singular_name) : ''; ?>"/>
	                                    </div>
	                                </div>
	                            </div>
	                            <div class="pacp-field pacp-field-text pacp-field-post-type is-required">
	                                <div class="pacp-label">
	                                    <label for="pacp_post_type-post_type"><?php echo esc_html('Post Type Key'); ?> <span class="pacp-required">*</span></label>
	                                </div>
	                                <div class="pacp-input">
	                                    <div class="pacp-input-wrap">
	                                    	<input type="text" id="pacp_post_type-post_type" class="pacp_slugified_key" name="post_type" placeholder="movie" maxlength="20" required="required" value="<?php echo (isset($result->post_type))? esc_attr($result->post_type) : ''; ?>"/></div>
	                                    <p class="description"><?php echo esc_html('Lower case letters, underscores and dashes only, Max 20 characters'); ?>.</p>
	                                </div>
	                            </div>

	                            <div class="pacp-field pacp-field-text pacp-field-post-type is-required">
	                                <div class="pacp-label">
	                                    <label for="pacp_post_type-post_type"><?php echo esc_html('Taxonomies'); ?> </label>
	                                </div>
	                                <div class="pacp-input">
	                                    <div class="pacp-input-wrap">
	                                    	<?php 
	                                    		$taxonomies = explode(',', @$result->pacp_taxonomies); 
											?>
											<select id="multi-select-box" multiple name="taxonomies[]">
											    <?php foreach ($result_taxonomy as $key => $value): ?>
											        <?php $isSelected = in_array($value->id, $taxonomies); ?>
											        <option value="<?php echo isset($value->id) ? esc_attr($value->id) : ''; ?>" <?php echo $isSelected ? esc_html('selected') : ''; ?>>
													    <?php echo isset($value->title) ? esc_html($value->title) : ''; ?>
													</option>
											    <?php endforeach ?>
											</select>

	                                    	<p class="description"><?php echo esc_html('Select existing taxonomies to classify items of the post type'); ?>.</p>
	                                	</div>
	                            	</div>

	                            <hr>
	                            <br> 
	                            <div class="pacp-field pacp-field-text pacp-field-post-type is-required">
	                                <div class="pacp-input">
	                                    <div class="pacp-input-wrap d-flex">
	                                    	<label class="pacp_switch_btn">
											  <input value="1" name="public" type="checkbox" <?php if (@$result->public == 1) { echo esc_html('checked'); } ?>>
											  <span class="slider round"></span>
											</label>
											<label><b><?php echo esc_html('Public'); ?></b> <br> <?php echo esc_html('Visible on the frontend and in the admin dashboard'); ?>.</label>
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
											<label><b><?php echo esc_html('Hierarchical'); ?></b> <br> <?php echo esc_html('Hierarchical post types can have descendants (like pages)'); ?>.</label>
	                                	</div>
	                            	</div>
	                            </div>

	                            <hr>
	                            <br>
	                            <h3><?php echo esc_html('Advanced Settings'); ?></h3>
	                            <hr>
								
								<div class="pacp-field pacp-field-checkbox pacp-field-supports">
								   <div class="pacp-label">
								      <label for="pacp_post_type-supports"><?php echo esc_html('Supports'); ?></label>
								      <p class="description"><?php echo esc_html('Enable various features in the content editor'); ?>.</p>
								   </div>
								   <div class="pacp-input">
								      <ul class="pacp-checkbox-list pacp-bl pacp_post_type_supports">
								         <li>
								         	<label class="selected">
								         		<input value="1" type="checkbox" id="pacp_post_type-supports-title" name="supports_title" <?php if (@$advanced_settings->supports_title == 'on' || @$advanced_settings->supports_title == 1  || !isset($_GET['edit'])) { echo esc_html('checked'); } ?>> <?php echo esc_html('Title'); ?></label>
								         </li>
								         <li>
								         	<label>
								         		<input value="1" type="checkbox" id="pacp_post_type-supports-author" name="supports_author" <?php if (@$advanced_settings->supports_author == 'on' || @$advanced_settings->supports_author == 1) { echo esc_html('checked'); } ?>> <?php echo esc_html('Author'); ?></label>
								         </li>
								         <li>
								         	<label>
								         		<input value="1" type="checkbox" id="pacp_post_type-supports-comments" name="supports_comments" <?php if (@$advanced_settings->supports_comments == 'on' || @$advanced_settings->supports_comments == 1) { echo esc_html('checked'); } ?>> <?php echo esc_html('Comments'); ?></label>
								         </li>
								         <li>
								         	<label>
								         		<input value="1" type="checkbox" id="pacp_post_type-supports-trackbacks" name="supports_trackbacks" <?php if (@$advanced_settings->supports_trackbacks == 'on' || @$advanced_settings->supports_trackbacks == 1) { echo esc_html('checked'); } ?>> <?php echo esc_html('Trackbacks'); ?></label>
								         </li>
								         <li>
								         	<label class="selected">
								         		<input value="1" type="checkbox" id="pacp_post_type-supports-editor" name="supports_editor" <?php if (@$advanced_settings->supports_editor == 'on' || @$advanced_settings->supports_editor == 1  || !isset($_GET['edit'])) { echo esc_html('checked'); } ?>> <?php echo esc_html('Editor'); ?></label></li>
								         <li>
								         	<label>
								         		<input value="1" type="checkbox" id="pacp_post_type-supports-excerpt" name="supports_excerpt" <?php if (@$advanced_settings->supports_excerpt == 'on' || @$advanced_settings->supports_excerpt == 1) { echo esc_html('checked'); } ?>> <?php echo esc_html('Excerpt'); ?></label>
								         </li>
								         <li>
								         	<label>
								         		<input value="1" type="checkbox" id="pacp_post_type-supports-revisions" name="supports_revisions" <?php if (@$advanced_settings->supports_revisions == 'on' || @$advanced_settings->supports_revisions == 1) { echo esc_html('checked'); } ?>> <?php echo esc_html('Revisions'); ?></label>
								         </li>
								         <li>
								         	<label>
								         		<input value="1" type="checkbox" id="pacp_post_type-supports-page-attributes" name="supports_attributes" <?php if (@$advanced_settings->supports_attributes == 'on' || @$advanced_settings->supports_attributes == 1) { echo esc_html('checked'); } ?>> <?php echo esc_html('Page Attributes'); ?></label>
								         </li>
								         <li>
								         	<label class="selected">
								         		<input value="1" type="checkbox" id="pacp_post_type-supports-thumbnail" name="supports_thumbnail" <?php if (@$advanced_settings->supports_thumbnail == 'on' || @$advanced_settings->supports_thumbnail == 1) { echo esc_html('checked'); } ?>> <?php echo esc_html('Featured Image'); ?></label>
								         </li>
								         <li>
								         	<label>
								         		<input value="1" type="checkbox" id="pacp_post_type-supports-custom-fields" name="supports_custom_fields" <?php if (@$advanced_settings->supports_custom_fields == 'on' || @$advanced_settings->supports_custom_fields == 1) { echo esc_html('checked'); } ?>> <?php echo esc_html('Custom Fields'); ?></label>
								         </li>
								      </ul>
								   </div>
								</div>


	                            <br>
	                            <hr>
	                            <br>
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
	                            <br>

	                            <br>
	                            <?php 
	                            $featured_image = isset($advanced_settings->featured_image) ? $advanced_settings->featured_image : '';
	                            ?>
	                            <div class="pacp-field pacp-field-text pacp-field-post-type is-required">
	                            	<div class="up_img">
	                            		<div class="img-section position-relative d-inline">
										    <?php if (!empty($featured_image)): ?>
										        <button type="button" class="btn btn-danger remove_img p-0">
										            x
										        </button>
										        <img src="<?php echo esc_url($featured_image); ?>" width="100" style="width: 30%;">
										    <?php endif; ?>
										</div>

				                        <label for="images"><?php echo esc_html('Archive Banner'); ?>:</label>
				                        <input class="img-up" type="hidden" name="featured_image" value="<?php echo !empty($featured_image) ? esc_url($featured_image) : ''; ?>" readonly>
				                        <input type="button" class="button button-primary upload_featured_image" value="Upload Image">
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


        var mediaUploader;
        $(document).on("click",".upload_featured_image",function(e) {   
            var inputField = $(this).prev(".img-up");
            var showing = $(this).parent().find('.img-section');
			var imageUrl = inputField.val();
			var mediaUploader = wp.media({
				title: "Upload Image",
				multiple: false
			});

			if (imageUrl) {
				mediaUploader.on('open', function() {
				  var selection = mediaUploader.state().get('selection');
				  var attachment = wp.media.attachment(imageUrl);
				  attachment.fetch();
				  selection.add(attachment);
				});
			}
            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                inputField.val(attachment.url);
                showing.empty();
                showing.append(
                    `<button type="button" class="btn btn-danger remove_img p-0">x</button>
                        <img width="100" src="`+attachment.url+`"  style="width: 30%;">`
                );
            });

            mediaUploader.open();
        });
        $(document).on("click",".remove_img",function(e) {
        	$('input.img-up').val('');
        	$('.img-section').empty();
        });
	});
</script>

<?php if (isset($_GET['edit']) && !empty($_GET['edit'])): ?>
	<?php $id = isset($_GET['edit']) ? absint($_GET['edit']) : 0; ?>
	<script>
    	jQuery(document).ready(function($) {

        $("form#pacp_post_update").on("submit", function(e) {
            e.preventDefault();

            $("#update_publish").prop("disabled", true);
            $("#update").prop("disabled", true);
            
            var name = $('input#pacp_post_type-labels-name').val();
            var singular = $('input#pacp_post_type-labels-singular_name').val();
            var post_type = $('input#pacp_post_type-post_type').val();

            if (name != '' && singular != '' && post_type != '') {
            	var formData = $(this).serialize();

            	var selectedPosts = $('#multi-select-box').val();
            	formData += '&selected_taxonomies=' + selectedPosts.join(',');

            	var featuredImage = decodeURIComponent(formData.match(/(?<=featured_image=)(.*?)(?=&|$)/)[0]);
            	featuredImage = featuredImage.replace(/\//g, '-');
            	formData = formData.replace(/featured_image=([^&]*)/, 'featured_image=' + encodeURIComponent(featuredImage));

            	var nonce = '<?php echo esc_js( wp_create_nonce( "pacp_post_update" ) ); ?>';
                $.ajax({
                    type: "post",
                    url: "<?php echo esc_url(admin_url('admin-ajax.php'));?>",
                    data: {
                        action:'pacp_update_post',
                        id: <?php echo esc_html($id); ?>,
                        formData:formData,
                        pacp_post_update_nonce: nonce
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
                    		$('input#pacp_post_type-post_type').after('<p class="error_m">Same Name Post already exist please change and try again</p>')
                    	}else{
                    		// window.location.href = 'admin.php?page=portfolio-and-custom-posts';
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
        $("form#pacp_post").on("submit", function(e) {
            e.preventDefault();

            $("#top_publish").prop("disabled", true);
            $("#publish").prop("disabled", true);

            var name = $('input#pacp_post_type-labels-name').val();
            var singular = $('input#pacp_post_type-labels-singular_name').val();
            var post_type = $('input#pacp_post_type-post_type').val();

            if (name != '' && singular != '' && post_type != '') {
            	var formData = $(this).serialize();
            	var selectedPosts = $('#multi-select-box').val();
            	formData += '&selected_taxonomies=' + selectedPosts.join(',');
            	
            	var featuredImage = decodeURIComponent(formData.match(/(?<=featured_image=)(.*?)(?=&|$)/)[0]);
            	featuredImage = featuredImage.replace(/\//g, '-');
            	formData = formData.replace(/featured_image=([^&]*)/, 'featured_image=' + encodeURIComponent(featuredImage));
            	
            	var nonce = '<?php echo esc_js( wp_create_nonce( "pacp_post" ) ); ?>';
                $.ajax({
                    type: "post",
                    url: "<?php echo esc_url(admin_url('admin-ajax.php'));?>",
                    data: {
                        action:'pacp_add_post',
                        formData:formData,
                        pacp_post_nonce: nonce
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
                    		$('input#pacp_post_type-post_type').after('<p class="error_m">Same Name Post already exist please change and try again</p>')
                    	}else{
                    		window.location.href = 'admin.php?page=portfolio-and-custom-posts';
                    	}
                    }
                  }); 
            }
        });
	    });
	</script>
<?php endif; ?>

