<?php 
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $pagenow;
$result = pacp_get_PACP_post_data('post');
$postType = [];
foreach ($result as $key => $value) {
    $postType[] = $value->post_type;
}
if ($pagenow == 'edit.php' && isset($_GET['post_type']) && in_array($_GET['post_type'], $postType)): ?>
	<?php $post_type = isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : '';  ?>
	<div class="category-section">
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php echo esc_html('Taxonomy'); ?></h1>
            <?php if (isset($post_type)): ?>
            	<?php
                	$taxonomy_names = get_object_taxonomies($post_type);
                ?>
                <?php foreach ($taxonomy_names as $taxonomy_name): ?>
                    <?php 
                    	$taxonomy = get_taxonomy($taxonomy_name);
                        $taxonomy_label = $taxonomy->labels->name;
                        $terms = get_terms(array('taxonomy' => $taxonomy_name,'hide_empty' => false,'parent' => 0));
                    ?>
                    <div class="category_container">
	                    <h4 class="wp-heading-inline"><?php echo esc_html($taxonomy_label); ?>
						    <a href="<?php echo esc_url(admin_url('/edit-tags.php?taxonomy=' . $taxonomy_name)); ?>" class="page-title-action">
						        <i class="dashicons-before dashicons-open-folder"></i> <?php echo esc_html('New'); ?>
						    </a>
						</h4>
                    	<ul class="menu-0">
		                    <?php foreach ($terms as $term): ?>
		                    	<?php 
								    $term_name = $term->name;
								    $term_count = $term->count;
								    $term_link = get_term_link($term->term_id, $taxonomy_name);
								    $term_admin_url = get_edit_term_link($term->term_id, $taxonomy_name);

								    $subcategories = get_terms(array('taxonomy' => $taxonomy_name,'hide_empty' => false,'parent' => $term->term_id));
								?>
								<li class="items menu-item-0">
								    <a href="<?php echo esc_url($term_admin_url); ?>">
								        <i class="dashicons-before dashicons-open-folder"></i> <?php echo esc_html($term_name); ?>
								        <span><?php echo esc_html($term_count); ?></span>
								    </a>
								    <div class="fornt_view">
								        <a class="page-title-action" target="_blank" href="<?php echo esc_url($term_link); ?>"><?php echo esc_html('View'); ?></a>
								    </div>
								</li>

                    			<ul class="menu-1">
									<?php foreach ($subcategories as $subcategory): ?>
										<?php 
									        $subterm_name = $subcategory->name;
									        $subterm_count = $subcategory->count;
								    		$sub_link = get_term_link($subcategory->term_id, $taxonomy_name);
									        $subterm_admin_url = get_edit_term_link($subcategory->term_id, $taxonomy_name); 
									        $subcategories = get_terms(array('taxonomy' => $taxonomy_name,'hide_empty' => false,'parent' => $subcategory->term_id));
									    ?>
										<li class="items menu-item-1">
										    <a href="<?php echo esc_url($subterm_admin_url); ?>">
										        <i class="dashicons-before dashicons-open-folder"></i> <?php echo esc_html($subterm_name); ?>
										        <span><?php echo esc_html($subterm_count); ?></span>
										    </a>
										    <div class="fornt_view">
										        <a class="page-title-action" target="_blank" href="<?php echo esc_url($sub_link); ?>"><?php echo esc_html('View'); ?></a>
										    </div>
										</li>

                    					<ul class="menu-2">
                    						<?php foreach ($subcategories as $subcategory): ?>
                    						<?php
										        $subterm_name = $subcategory->name;
										        $subterm_count = $subcategory->count;
										        $term_link = get_term_link($subcategory->term_id, $taxonomy_name);
										        $subterm_admin_url = get_edit_term_link($subcategory->term_id, $taxonomy_name); 
									        	$subcategories = get_terms(array('taxonomy' => $taxonomy_name,'hide_empty' => false,'parent' => $subcategory->term_id));
										    ?>
										    	<li class="items menu-item-2">
												    <a href="<?php echo esc_url($subterm_admin_url); ?>">
												        <i class="dashicons-before dashicons-open-folder"></i> <?php echo esc_html($subterm_name); ?>
												        <span><?php echo esc_html($subterm_count); ?></span>
												    </a>
												    <div class="fornt_view">
												        <a class="page-title-action" target="_blank" href="<?php echo esc_url($term_link); ?>"><?php echo esc_html('View'); ?></a>
												    </div>
												</li>

		                    					<ul class="menu-3">
		                    						<?php foreach ($subcategories as $subcategory): ?>
		                    						<?php
												        $subterm_name = $subcategory->name;
												        $subterm_count = $subcategory->count;
												        $term_link = get_term_link($subcategory->term_id, $taxonomy_name);
												        $subterm_admin_url = get_edit_term_link($subcategory->term_id, $taxonomy_name);
											        	 
									        			$subcategories = get_terms(array('taxonomy' => $taxonomy_name,'hide_empty' => false,'parent' => $subcategory->term_id));
												    ?>
												    	<li class="items menu-item-3">
														    <a href="<?php echo esc_url($subterm_admin_url); ?>">
														        <i class="dashicons-before dashicons-open-folder"></i> <?php echo esc_html($subterm_name); ?>
														        <span><?php echo esc_html($subterm_count); ?></span>
														    </a>
														    <div class="fornt_view">
														        <a class="page-title-action" target="_blank" href="<?php echo esc_url($term_link); ?>"><?php echo esc_html('View'); ?></a>
														    </div>
														</li>

													<?php endforeach; ?>
												</ul>
											<?php endforeach; ?>
										</ul>
									<?php endforeach; ?>
								</ul>
								<?php endforeach; ?>
						</ul>
					</div>
                  <?php endforeach; ?>       
            <?php endif; ?>
	         <style type="text/css">
	            .category-section {
	                display: inline-block;
	                width: 27%;
	                float: left;
	                margin-top: 3%;
	            }
	            div#wpbody-content .category-section + .wrap {
	                width: 70%;
	                float: right;
	                display: inline-block;
	            }
	            .category-section a.page-title-action {
				    font-size: 12px;
				}

				.category-section a.page-title-action i:before {
				    font-size: 17px;
				}
				.category_container ul > ul {
				    padding-left: 20px;
				}
				.category_container ul li {
				    margin: 0;
				    border-bottom: 1px solid #ddd;
				    border-left: 1px solid #ddd;
				    padding: 4px 0;
				    display: flex;
				    gap: 10px;
				}
				.category_container ul a {
				    font-size: 15px;
				    text-decoration: none;
				    padding: 5px 0;
				    padding-left: 10px;
    				display: inline-block;
				}
				.category_container ul a span {
				    background: #000;
				    color: #fff;
				    border-radius: 50%;
				    padding: 1px 5px;
				    font-size: 12px;
				    height: 15px;
				    display: inline-block;
				    line-height: 13px;
				}
				h4.wp-heading-inline {
				    display: flex;
				    align-items: center;
				    justify-content: space-between;
				    flex-wrap: wrap;
				}
				.category-section a.page-title-action {
				    font-size: 12px !important;
				    padding: 5px 5px 0 !important;
				    position: unset !important;
				}
				h4.wp-heading-inline {
				    display: flex;
				    align-items: center;
				    justify-content: space-between;
				    flex-wrap: wrap;
				    background: #ddd;
				    padding: 0px 4px;
				    margin: 0;
				    color: #000;
				    font-size: 14px;
				}
				.category-section h1.wp-heading-inline {
				    margin-bottom: 13px;
				}
				.category-section .fornt_view a {
				    padding: 2px !important;
				    color: #fff;
				    background: #2271b1;
				}
	        </style>
        </div>
    </div>
<?php endif; ?>