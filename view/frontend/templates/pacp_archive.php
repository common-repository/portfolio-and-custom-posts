<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }

    get_header(); 

    $request_uri = isset($_SERVER['REQUEST_URI']) ? esc_url(sanitize_text_field($_SERVER['REQUEST_URI'])) : '';

    $current_url = esc_url(home_url($request_uri)); 
    
    $current_post_type = get_post_type();
    if ($current_post_type):
    
        global $wpdb;
        $table_setting = $wpdb->prefix . 'pacp_post_setting';
        $sql_result = $wpdb->get_results($wpdb->prepare("SELECT * FROM %s WHERE `setting` = %s",$table_setting, 'per_page'))[0];
        $taxonomy = get_object_taxonomies($current_post_type);
        $args = array(
            'posts_per_page' => $sql_result->data,
            'post_type' => $current_post_type,
            'post_status' => 'publish',
            'orderby' => 'post_date',
            'order' => 'DESC',
        );
        $loop = new WP_Query($args);


        function pacp_getPost( $current_post_type,$value,$term_id){
            $args = array(
            'post_type' => $current_post_type,
            'posts_per_page' => -1,
            'tax_query' => array(
                    array(
                        'taxonomy' => $value,
                        'field' => 'term_id',
                        'terms' => $term_id,
                        'operator' => 'IN',
                    ),
                ),
            );
            $posts = get_posts($args);
            $post_ids = array();

            foreach ($posts as $post) {
                $post_ids[] = $post->ID;
            }

            return $post_ids;
        } 
        
        $argsTotal = array('posts_per_page' => -1,'post_type' => $current_post_type,'post_status' => 'publish');
        $total_data = new WP_Query($argsTotal);


        $maintable = $wpdb->prefix . 'pacp_post_table';
        $banner = $wpdb->get_results($wpdb->prepare("SELECT `advanced_settings` FROM %s WHERE `type` = 'post' AND `post_type` = %s",$maintable, $current_post_type))[0];
        $banner = json_decode($banner->advanced_settings);

        wp_enqueue_style('pacp-archive-style', plugins_url('../../../assets/css/pacp-archive-style.css' , __FILE__ ), false, '1.0', 'all' );
        wp_enqueue_style('pacp-bootstrap-grid', plugins_url('../../../assets/css/bootstrap-grid.less' , __FILE__ ), false, '1.0', 'all' );
        wp_enqueue_script('pacp-script', plugins_url( '../../../assets/js/pacp-script.js' , __FILE__ ), array('jquery'), '1.0', false);

        $nonce = esc_js(wp_create_nonce("pacp_load_more_nonce"));
        $get_post_type = esc_html(get_post_type());

        wp_localize_script('pacp-script', 'ajax_object', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'load_more_nonce' => $nonce,
            'get_post_type' => $get_post_type,
            'totalCount' => esc_html($total_data->found_posts),
            'per_page' => esc_html($sql_result->data + $sql_result->data),
            'current_post_type' => esc_html($current_post_type)
        ));

?> 

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <?php if (get_post() && !preg_match('/vc_row/', get_post()->post_content)) : ?>
            <div class="wraper_blog_main default-page">
        <?php endif; ?>
            <?php if (isset($banner->featured_image) && !empty($banner->featured_image)): ?>
                <div class="container-fluid page-container p-0 banner_content">
                    <div class="pacp_main_banner" style="background-image: url('<?php echo esc_url($banner->featured_image); ?>');">
                        <h2 class="text-light"><?php echo esc_html(get_post_type()); ?></h2>
                    </div>
                </div>
            <?php endif ?>
            <div class="container page-container p-0">
                <div class="pacp_main row">
                    <div class="filterHolder col-lg-3 col-12 px-0">
                        <div class="filter_button d-md-none">
                            <span><?php echo esc_html('Categories Filter'); ?></span>
                        </div>
                        <?php if (count($taxonomy) > 0): ?>
                            <form action="#" method="POST" id="pacp_filter" >
                                <?php wp_nonce_field('pacp_filter', 'pacp_filter_nonce'); ?>
                                <h2 class="filterTitle"><?php echo esc_html('Categories'); ?></h2>
                                <div class="filterContainer">
                                    <div class="toggle w-100" id="pacp_toggle">
                                        <?php foreach ($taxonomy as $key => $value): ?>
                                            <?php  
                                                $pattern = '/[^a-zA-Z0-9]/';
                                                $value_filter = preg_replace($pattern, '', $value);

                                                $taxonomy = get_taxonomy($value);
                                                $taxonomy_label = $taxonomy->labels->name;

                                                $terms = get_terms(array('taxonomy' => $value,'hide_empty' => false,'parent' => 0));
                                            ?>
                                            <div class="toggle-items">
                                                <h2 class="toggle-header" id="cat_filter_<?php echo esc_attr($key); ?>">
                                                    <a class="toggle-button" role="button" data-bs-toggle="collapse" data-bs-target="#collapse_<?php echo esc_attr($key); ?>" aria-expanded="false" aria-controls="collapse_<?php echo esc_attr($key); ?>">
                                                        <span><?php echo esc_html($taxonomy_label); ?></span>
                                                        <i class="toogle_icon"></i>
                                                    </a>
                                                </h2>

                                                <div id="collapse_<?php echo esc_attr($key); ?>" class="toggle-collapse collapse" aria-labelledby="cat_filter_<?php echo esc_attr($key); ?>" data-bs-parent="#pacp_toggle">
                                                   <?php if (count($terms) > 0): ?> 
                                                        <div class="toggle-body">
                                                            <?php foreach ($terms as $term): ?>
                                                                <?php 
                                                                    $term_type = $term->slug;
                                                                    $category_parent = $term->parent;
                                                                    
                                                                    $subcategories = get_terms(array('taxonomy' => $value,'hide_empty' => false,'parent' => $term->term_id));

                                                                    $get_ids = [];
                                                                    $get_ids[] = pacp_getPost( $current_post_type,$value,$term->term_id);

                                                                    $combinedArray = array_merge(...$get_ids);
                                                                    $uniqueArray = array_unique($combinedArray);
                                                                    $finalArray = array_values($uniqueArray);
                                                                ?>
                                                                <?php if (@$category->parent == 0): ?>
                                                                    <div class="filterInputContainer">
                                                                            <?php if (isset($_GET[$value_filter]) && !empty($_GET[$value_filter]) && $_GET[$value_filter] == $term->term_id): ?>
                                                                                <input type="checkbox" value="<?php echo esc_attr($term->term_id); ?>" id="<?php echo esc_attr($term->term_id); ?>" name="<?php echo esc_attr($value_filter); ?>[]" data-id="<?php echo esc_attr($value_filter); ?>" checked>
                                                                            <?php else: ?>
                                                                                <input type="checkbox" value="<?php echo esc_attr($term->term_id); ?>" id="<?php echo esc_attr($term->term_id); ?>" name="<?php echo esc_attr($value_filter); ?>[]" data-id="<?php echo esc_attr($value_filter); ?>">
                                                                            <?php endif; ?>
                                                                        <label for="<?php echo esc_attr($term->term_id); ?>"><?php echo esc_html($term->name); ?> (<?php echo esc_html(count($finalArray)); ?>)</label>
                                                                        <?php if (count($subcategories) > 0): ?>
                                                                            <span class="toggle_btn"></span>
                                                                        <?php endif; ?>
                                                                    </div>
 
                                                                    <?php if (count($subcategories) > 0): ?>
                                                                        <ul class="sub_cat">
                                                                            <?php foreach ($subcategories as $sub_cat): ?>
                                                                                <?php 
                                                                                    $get_ids[] = pacp_getPost( $current_post_type,$value,$sub_cat->term_id);
                                                                                ?>
                                                                                <li>
                                                                                    <div class="filterInputContainer">
                                                                                        <input type="checkbox" value="<?php echo esc_attr($sub_cat->term_id); ?>" id="<?php echo esc_attr($sub_cat->term_id); ?>" name="<?php echo esc_attr($value_filter); ?>[]" data-id='<?php echo esc_attr($value_filter); ?>'>
                                                                                        <label for="<?php echo esc_attr($sub_cat->term_id); ?>"><?php echo esc_html($sub_cat->name); ?> (<?php echo esc_html($sub_cat->count); ?>)</label>
                                                                                    </div>
                                                                                </li>

                                                                            <?php endforeach; ?>
                                                                        </ul>
                                                                    <?php endif; ?>
                                                                <?php endif;?>
                                                            <?php endforeach; ?>
                                                        </div>
                                                        <?php if (count($terms) > 10): ?>
                                                        <div class="moreless_btn" >
                                                            <div class="toggle-more-btn">+ <?php echo esc_html('Show more'); ?></div>
                                                        </div>
                                                        <?php endif;?>
                                                    <?php endif;?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <input type="hidden" name="action" value="pacp_pacpCategoryfilter">
                                <input type="hidden" name="post_type" value="<?php echo esc_attr($current_post_type); ?>">
                            </form>  
                        <?php else: ?>
                            <?php echo esc_html('no category found'); ?>
                        <?php endif; ?>
                    </div>
 
                    <div class="pacp_response col-lg-9 col-12">
                        <div class="container-fluid p-0">
                            <div class="row">
                            <?php if (strpos($current_url, '?') == false):?>    
                                <div class="col-12 pacp_holder_header">
                                    <div class="projectCount">
                                        <span class="count-icon">
                                            <img src="<?php echo esc_url(plugins_url('../../../assets/images/total-projects.png', __FILE__)); ?>"/>
                                        </span>
                                        <div class="count-total">
                                            <span id="totalCount"><?php echo esc_html($total_data->found_posts); ?></span>
                                            <b>Total <?php echo esc_html(get_post_type()); ?></b>
                                        </div>
                                    </div>
                                </div> 
                                <?php if ($loop->have_posts()): ?>
                                    <?php while ($loop->have_posts()): ?>
                                        <?php $loop->the_post(); ?>
                                        <?php $url = get_permalink();?>
                                        <div class="col-lg-4 col-md-6 col-sm-6 col-12 pacp_holder">
                                            <div class="pacp_head">
                                                <span class="title_head"><?php echo esc_html(get_the_title()); ?></span>
                                            </div>
                                            <div class="pacp_container">
                                                <a href="<?php echo esc_url($url); ?>" class="thumbnail-image">
                                                    <?php if (has_post_thumbnail()): ?>
                                                        <?php $image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'single-post-thumbnail'); ?>
                                                        <img src="<?php echo esc_url($image[0]); ?>">
                                                    <?php endif; ?>
                                                </a>

                                                <div class="the_content_data">
                                                    <a href="<?php echo esc_url($url); ?>" class="pacp_title">
                                                        <?php echo esc_html(get_the_title()); ?>
                                                    </a>
                                                    <div class="the_excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 20)); ?></div>
                                                </div>

                                            </div>
                                            <div class="pacp_footer">
                                                <a class="pacp_read-more" href="<?php echo esc_url($url); ?>"><?php echo esc_html('Read More'); ?> →</a>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    no post 
                                <?php endif; ?>
                                <?php
                                wp_reset_postdata();
                                ?>
                            <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($total_data->found_posts > $sql_result->data): ?>
                            <div class="load_more mt-4">
                                <button class="load_btn" data-id="<?php echo esc_attr($sql_result->data + $sql_result->data); ?>">
                                    <?php echo esc_html('Load More'); ?>
                                </button>
                            </div>
                        <?php endif ?>
                    </div>
                </div>

            </div>

        <?php if (get_post() && !preg_match('/vc_row/', get_post()->post_content)) : ?>
            </div>
        <?php endif; ?>
    </main><!-- #main -->
</div><!-- #primary -->

<?php endif; ?>

<?php 
    get_footer();
?>