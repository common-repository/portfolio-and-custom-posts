<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function pacp_savePostData($block){
	$postType = isset($block['post_type']) ? $block['post_type'] : '';
	$title = isset($block['name']) ? $block['name'] : '';
	$singular_name = isset($block['singular_name']) ? $block['singular_name'] : '';

	$taxonomies = isset($block['selected_taxonomies']) ? $block['selected_taxonomies'] : '';
 
    $public = isset($block['public']) ? 1 : 0;
    $hierarchical = isset($block['hierarchical']) ? 1 : 0;
    $status = isset($block['status']) ? 1 : 0;
     
	$supports_title = isset($block['supports_title']) ? $block['supports_title'] : '';
	$supports_author = isset($block['supports_author']) ? $block['supports_author'] : '';
	$supports_comments = isset($block['supports_comments']) ? $block['supports_comments'] : '';
	$supports_trackbacks = isset($block['supports_trackbacks']) ? $block['supports_trackbacks'] : '';
	$supports_editor = isset($block['supports_editor']) ? $block['supports_editor'] : '';
	$supports_excerpt = isset($block['supports_excerpt']) ? $block['supports_excerpt'] : '';
	$supports_revisions = isset($block['supports_revisions']) ? $block['supports_revisions'] : '';
	$supports_attributes = isset($block['supports_attributes']) ? $block['supports_attributes'] : '';
	$supports_thumbnail = isset($block['supports_thumbnail']) ? $block['supports_thumbnail'] : '';
	$supports_custom_fields = isset($block['supports_custom_fields']) ? $block['supports_custom_fields'] : '';
	$featured_image = isset($block['featured_image']) ? $block['featured_image'] : '';
	$featured_image = explode('uploads-', $featured_image);
    $featured_image = preg_replace('/-/', '/', $featured_image[1], 2);
    $featured_image =  esc_url(home_url('/wp-content/uploads/'.$featured_image));

	$newdate = current_time('Y-m-d H:i:s');

	$supports = [];
	if ($block['supports_title'] == 1) {
	    $supports[] = 'title';
	}
	if ($block['supports_editor'] == 1) {
	    $supports[] = 'editor';
	}
	if ($block['supports_excerpt'] == 1) {
	    $supports[] = 'excerpt';
	}
	if ($block['supports_thumbnail'] == 1) {
	    $supports[] = 'thumbnail';
	}
	if ($block['supports_revisions'] == 1) {
	    $supports[] = 'revisions';
	}
	if ($block['supports_author'] == 1) {
	    $supports[] = 'author';
	}
	if ($block['supports_author'] == 1) {
	    $supports[] = 'comments';
	}
	if ($block['supports_author'] == 1) {
	    $supports[] = 'trackbacks';
	}
	if ($block['supports_attributes'] == 1) {
	    $supports[] = 'page-attributes';
	}
	if ($block['supports_custom_fields'] == 1) {
	    $supports[] = 'custom-fields';
	}

	$advanced = [
		'supports' => $supports,
		'supports_title' => $supports_title,
		'supports_author' => $supports_author,
		'supports_comments' => $supports_comments,
		'supports_trackbacks' => $supports_trackbacks,
		'supports_editor' => $supports_editor,
		'supports_excerpt' => $supports_excerpt,
		'supports_revisions' => $supports_revisions,
		'supports_attributes' => $supports_attributes,
		'supports_thumbnail' => $supports_thumbnail,
        'featured_image' => $featured_image,
		'supports_custom_fields' => $supports_custom_fields
	];
	$advanced_data = wp_json_encode($advanced,true);

    $data = array(
        'title' => $title,
        'type' => 'post',
        'status' => $status,
        'singular_name' => $singular_name,
        'post_type' => $postType,
        'pacp_taxonomies' => $taxonomies,
        'advanced_settings' => $advanced_data,
        'public' => $public,
        'hierarchical' => $hierarchical,
        'created_at' => $newdate,
        'updated_at' => $newdate
    );

    return $data;
}

add_action('wp_ajax_pacp_add_post', 'pacp_post_handler' );
add_action('wp_ajax_nopriv_pacp_add_post','pacp_post_handler');

function pacp_post_handler(){

	if ( ! isset( $_POST['pacp_post_nonce'] ) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['pacp_post_nonce'])), 'pacp_post' ) ) {
        wp_die( 'Nonce verification failed' );
    }

	$formData = isset($_POST['formData']) ? sanitize_text_field(wp_unslash($_POST['formData'])) : '';
   	parse_str($formData, $block);

    try {
        global $wpdb;
      	$table_name = $wpdb->prefix . 'pacp_post_table';

      	$postType = @$block['post_type'];

		$cache_key = 'pacp_post_table_' . $postType;
		$cached_result = wp_cache_get($cache_key);
		if ($cached_result === false) {
		    $result = $wpdb->get_results($wpdb->prepare("SELECT `id` FROM $table_name WHERE `post_type` = %s" , $postType));
		    wp_cache_set($cache_key, $result);
		} else {
		    $result = $cached_result;
		}
    	 
		if (count($result) == 0) {
		    
			$data = pacp_savePostData($block);
 
	        $wpdb->insert($table_name, $data);

	        if (isset($block['taxonomies'])){
				$inserted_id = $wpdb->insert_id;
				foreach ($block['taxonomies'] as $key => $value) {
				    
				    $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE `id` = %s", $value))[0]; 

				    if (strpos($postType, ',') !== false) {
				    	$ids = $result->post_type.','.$inserted_id;
				    	$ids_ar = explode(',', $ids);
				    	$inserted_ids = array_unique($ids_ar);
				    	$getids = implode(',', $inserted_ids);
				    }else{
				    	$getids = $inserted_id;
				    }
			    	$data = array(
		                'post_type' => $getids
		            );
		            $where = array('id' => $value);
		            $wpdb->update($table_name, $data, $where);
		    	}
		    }

		    flush_rewrite_rules();

	        echo wp_json_encode([
	            'status'=> true,
	            'message'=>'Success'
	        ], true);
	        
		}else{
			echo esc_html('exist');
		}

		wp_die();

    } catch (Exception $e) {
        echo wp_json_encode([
            'status'=>false,
            'message'=>$e->getMessage()
        ], true);
        wp_die();
    }
}

add_action('wp_ajax_pacp_update_post', 'pacp_update_post_handler' );
add_action('wp_ajax_nopriv_pacp_update_post','pacp_update_post_handler');

function pacp_update_post_handler(){ 
  
	if (!isset($_POST['pacp_post_update_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['pacp_post_update_nonce'])), 'pacp_post_update')) {
        wp_die('Nonce verification failed');
    }
    $id = isset($_POST['id']) ? absint($_POST['id']) : 0;
	$formData = isset($_POST['formData']) ? sanitize_text_field(wp_unslash($_POST['formData'])) : '';
   	parse_str($formData, $block);
 
    try {
        global $wpdb;
      	$table_name = $wpdb->prefix . 'pacp_post_table';

      	$postType = @$block['post_type'];
 
	    $cache_key = 'pacp_post_' . $id;
        $cached_result = wp_cache_get($cache_key);
        if ($cached_result === false) {
            $storedPostType = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE `id` = %d" , $id))[0];
            wp_cache_set($cache_key, $storedPostType);
        } else {
            $storedPostType = $cached_result;
        }

	    if ($storedPostType->post_type === $postType) {
	    	$data = pacp_savePostData($block);
	        $where = array('id' => $id);
            $wpdb->update($table_name, $data, $where);
  			flush_rewrite_rules();
	        echo wp_json_encode([
		            'status'=> true,
		            'message'=>'Success'
		        ], true);

	    }else{
	    	$result = $wpdb->get_results($wpdb->prepare("SELECT 'id' FROM $table_name WHERE `post_type` = %s", $postType));
	    	if (count($result) == 0) {

	    		$data = pacp_savePostData($block);
		        $where = array('id' => $id);
	            $wpdb->update($table_name, $data, $where);
	            flush_rewrite_rules();
		        echo wp_json_encode([
		            'status'=> true,
		            'message'=>'Success'
		        ], true);
	        
			}else{
				echo esc_html('exist');
			}
		}

		wp_die();

	

    } catch (Exception $e) {
        echo wp_json_encode([
            'status'=>false,
            'message'=>$e->getMessage()
        ], true);
        wp_die();
    }
}



function pacp_duplicate_post($post) {
	global $wpdb;
    $table_name = $wpdb->prefix . 'pacp_post_table';
	$newdate = current_time('H:i:s');

	$cache_key = 'pacp_post_' . $post->post_type;
	$cached_result = wp_cache_get($cache_key);
	if ($cached_result === false) {
	    $result = $wpdb->get_results($wpdb->prepare("SELECT 'id' FROM $table_name WHERE `post_type` = %s", $post->post_type));
	    wp_cache_set($cache_key, $result);
	} else {
	    $result = $cached_result;
	}

	$postType = $post->post_type.'-'.count($result);
 
	$data = array(
        'title' => $post->title,
        'type' => $post->type,
        'status' => $post->status,
        'singular_name' => $post->singular_name,
        'post_type' => $postType,
        'pacp_taxonomies' => $post->pacp_taxonomies,
        'advanced_settings' => $post->advanced_settings,
        'public' => $post->public,
        'hierarchical' => $post->hierarchical,
        'created_at' => $newdate,
        'updated_at' => $newdate
    );
    return $data;
}
add_action('wp_ajax_pacp_triger_post', 'pacp_triger_post_handler' );
add_action('wp_ajax_nopriv_pacp_triger_post','pacp_triger_post_handler');
function pacp_triger_post_handler(){
	if (!isset($_POST['pacp_triger_post_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['pacp_triger_post_nonce'])), 'pacp_triger_post_nonce')) {
        wp_die('Nonce verification failed');
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'pacp_post_table';
    try {
		$id = isset($_POST['id']) ? absint($_POST['id']) : 0;
 
		$triger = isset($_POST['triger']) ? sanitize_text_field($_POST['triger']) : '';
		$newdate = current_time('Y-m-d H:i:s'); 
		if ($triger == 'trash') {

			$cache_key = 'pacp_post_taxonomy';
		    $cached_result = wp_cache_get($cache_key);
		    if ($cached_result === false) {
		        $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE `type` = %s", 'taxonomy'));
		        wp_cache_set($cache_key, $result);
		    } else {
		        $result = $cached_result;
		    }

			foreach ($result as $key => $value) {
				if (strpos($value->post_type, ',') !== false) {
					$ids = explode(',', $value->post_type);
					$ids = array_filter($ids, function ($item) use ($id) {
					    return $item !== $id;
					});
					$ids = array_values($ids);
					$ids = implode(',', $ids);
				}else{
					if ($value->post_type == $id) {
						$ids = '';
					}else{
						$ids = $value->post_type;
					}
				}
				$data = array(
			        'post_type' => $ids
			    );
				$where = array('id' => $value->id);
				$wpdb->update($table_name, $data, $where);
			}
   
	        $wpdb->query($wpdb->prepare("DELETE FROM $table_name WHERE `id` = %d",$id));


	        flush_rewrite_rules();
	        echo wp_json_encode([
	            'status'=> true,
	            'message'=>'Success'
	        ], true);

	    }elseif($triger == 'activate'){
	    	$data = array(
		        'status' => 1,
		        'updated_at' => $newdate
		    );
	        $where = array('id' => $id);
            $wpdb->update($table_name, $data, $where);
	    }elseif($triger == 'deactivate'){
	    	$data = array(
		        'status' => 0,
		        'updated_at' => $newdate
		    );
	        $where = array('id' => $id);
            $wpdb->update($table_name, $data, $where);

	    }elseif($triger == 'duplicate'){

	    	$result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE `id` = %d",$id))[0];
			$data = pacp_duplicate_post($result);
	        $wpdb->insert($table_name, $data);

	        echo wp_json_encode([
	            'status'=> true,
	            'message'=>'Success'
	        ], true);
	    }
	    flush_rewrite_rules();
        wp_die();
	} catch (Exception $e) {
        echo wp_json_encode([
            'status'=>false,
            'message'=>$e->getMessage()
        ], true);
        wp_die();
    }
}

function pacp_saveTaxonomyData($block){
	$newdate = current_time('Y-m-d H:i:s');

	$title = isset($block['name']) ? $block['name'] : '';
	$singular_name = isset($block['singular_name']) ? $block['singular_name'] : '';
	$taxonomy_type = isset($block['taxonomy_type']) ? $block['taxonomy_type'] : '';

    $posts = isset($block['selected_posts']) ? $block['selected_posts'] : '';

    $public = isset($block['public']) ? 1 : 0;
    $hierarchical = isset($block['hierarchical']) ? 1 : 0;
    $status = isset($block['status']) ? 1 : 0;

    $data = array(
        'title' => $title,
        'status' => $status,
        'type' => 'taxonomy',
        'singular_name' => $singular_name,
        'post_type' => $posts,
        'pacp_taxonomies' => $taxonomy_type,
        'public' => $public,
        'hierarchical' => $hierarchical,
        'created_at' => $newdate,
        'updated_at' => $newdate
    );

    return $data;

}

add_action('wp_ajax_pacp_add_taxonomy', 'pacp_taxonomy_handler' );
add_action('wp_ajax_nopriv_pacp_add_taxonomy','pacp_taxonomy_handler');
function pacp_taxonomy_handler(){ 
	if ( ! isset( $_POST['pacp_taxonomy_nonce'] ) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['pacp_taxonomy_nonce'])), 'pacp_taxonomy' ) ) {
        wp_die( 'Nonce verification failed' );
    }
	$formData = isset($_POST['formData']) ? sanitize_text_field(wp_unslash($_POST['formData'])) : '';
   	parse_str($formData, $block);
    try {
        global $wpdb;
      	$table_name = $wpdb->prefix . 'pacp_post_table';

      	$taxonomyType = @$block['taxonomy_type'];
	 
	    // Check if data is cached
		$cache_key = 'pacp_taxonomy_' . $taxonomyType;
		$cached_result = wp_cache_get($cache_key);
		if ($cached_result === false) {
		    $result = $wpdb->get_results($wpdb->prepare("SELECT 'id' FROM $table_name WHERE `pacp_taxonomies` = %s", $taxonomyType));
		    wp_cache_set($cache_key, $result);
		} else {
		    $result = $cached_result;
		}
    	 
		if (count($result) == 0) {

			$data = pacp_saveTaxonomyData($block);
			$wpdb->insert($table_name, $data);

			if (isset($block['posts'])) {
				$inserted_id = $wpdb->insert_id;
				foreach ($block['posts'] as $key => $value) {
			    	$data = array(
		                'pacp_taxonomies' => $inserted_id
		            );
		            $where = array('id' => $value);
		            $wpdb->update($table_name, $data, $where);
		    	}
		    }
		    flush_rewrite_rules();
	        echo wp_json_encode([
	            'status'=> true,
	            'message'=>'Success'
	        ], true);
		}else{
			echo esc_html('exist');
		}
        wp_die();
    } catch (Exception $e) {
        echo wp_json_encode([
            'status'=>false,
            'message'=>$e->getMessage()
        ], true);
        wp_die();
    }
}


add_action('wp_ajax_pacp_update_taxonomy', 'pacp_update_taxonomy_handler' );
add_action('wp_ajax_nopriv_pacp_update_taxonomy','pacp_update_taxonomy_handler');

function pacp_update_taxonomy_handler(){
	if ( ! isset( $_POST['pacp_taxonomy_update_nonce'] ) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['pacp_taxonomy_update_nonce'])), 'pacp_taxonomy_update' ) ) {
        wp_die( 'Nonce verification failed' );
    }

	$id = isset($_POST['id']) ? absint($_POST['id']) : 0;
	$formData = isset($_POST['formData']) ? sanitize_text_field(wp_unslash($_POST['formData'])) : '';
   	parse_str($formData, $block);
 
    try {
        global $wpdb;
      	$table_name = $wpdb->prefix . 'pacp_post_table';
      	$taxonomyType = @$block['taxonomy_type'];

	    // Check if data is cached
        $cache_key = 'pacp_taxonomy_' . $id;
        $cached_result = wp_cache_get($cache_key);
        if ($cached_result === false) {
            $storedtaxonomiesType = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE `id` = %d", $id))[0];
            wp_cache_set($cache_key, $storedtaxonomiesType);
        } else {
            $storedtaxonomiesType = $cached_result;
        }

	    if ($storedtaxonomiesType->pacp_taxonomies === $taxonomyType) {
	    	$data = pacp_saveTaxonomyData($block);
 
	        $where = array('id' => $id);
            $wpdb->update($table_name, $data, $where);

            if (isset($block['posts'])) {
            	$inserted_id = $wpdb->insert_id;
            	 
            	foreach ($block['posts'] as $key => $value) {
            		
            		// Check if data is cached
			        $cache_key = 'pacp_post_' . implode('_', $block['posts']);
			        $cached_result = wp_cache_get($cache_key);
			        if ($cached_result === false) {
			            foreach ($block['posts'] as $key => $value) {
			                $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE `id` = %s", $value))[0];
			                wp_cache_set($cache_key, $result);
			            }
			        } else {
			            $result = $cached_result;
			        }

            		if (strpos($result->pacp_taxonomies, ',') !== false) {
            			$array = explode(',', $result->pacp_taxonomies);
            			
            			array_push($array, $inserted_id);
 						$uniqueArray = array_unique($array);
            			$ids = implode(',',$uniqueArray);
            		}else{
            			if ($result->pacp_taxonomies == $inserted_id) {
							$ids = $inserted_id;
						}else{
							$ids = $result->pacp_taxonomies;
						}
            		}
            		$data = array(
		                'pacp_taxonomies' => $ids
		            );
		            $where = array('id' => $value);
		            $wpdb->update($table_name, $data, $where);
            	}
            	
				 
		    }
 			flush_rewrite_rules();
	        echo wp_json_encode([
		            'status'=> true,
		            'message'=>'Success'
		        ], true);

	    }else{
	   
	    	$result = $wpdb->get_results($wpdb->prepare("SELECT 'id' FROM $table_name WHERE `pacp_taxonomies` = %s", $taxonomyType));

	    	if (count($result) == 0) {

	    		$data = pacp_saveTaxonomyData($block);
		        $where = array('id' => $id);
	            $wpdb->update($table_name, $data, $where);
	            flush_rewrite_rules();
		        echo wp_json_encode([
		            'status'=> true,
		            'message'=>'Success'
		        ], true);
	        
			}else{
				echo esc_html('exist');
			}
		}

		wp_die();
    } catch (Exception $e) {
        echo wp_json_encode([
            'status'=>false,
            'message'=>$e->getMessage()
        ], true);
        wp_die();
    }
}


function pacp_duplicate_taxonomies($post) {
	global $wpdb;
    $table_name = $wpdb->prefix . 'pacp_post_table';
	$newdate = current_time('H:i:s');
   
  	// Check if data is cached
	$cache_key = 'pacp_taxonomies_' . $post->pacp_taxonomies;
	$cached_result = wp_cache_get($cache_key);
	if ($cached_result === false) {
	    $result = $wpdb->get_results($wpdb->prepare("SELECT 'id' FROM $table_name WHERE `pacp_taxonomies` = %s", $post->pacp_taxonomies)); 
	    wp_cache_set($cache_key, $result);
	} else {
	    $result = $cached_result;
	}
	$taxonomy_type = $post->pacp_taxonomies.'-'.count($result);
 	
	$data = array(
		'title' => $post->title,
        'type' => $post->type,
        'status' => $post->status,
        'singular_name' =>  $post->singular_name,
        'post_type' =>  $post->post_type,
        'pacp_taxonomies' => $taxonomy_type,
        'public' => $post->public,
        'hierarchical' => $post->hierarchical,
        'created_at' => $newdate,
        'updated_at' => $newdate
    );
	$taxonomyType = @$block['taxonomy_type'];
    return $data;
}

add_action('wp_ajax_pacp_triger_taxonomies', 'pacp_triger_taxonomies_handler' );
add_action('wp_ajax_nopriv_pacp_triger_taxonomies','pacp_triger_taxonomies_handler');
function pacp_triger_taxonomies_handler(){
	if (!isset($_POST['pacp_triger_taxonomies_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['pacp_triger_taxonomies_nonce'])), 'pacp_triger_taxonomies_nonce')) {
        wp_die('Nonce verification failed');
    }
    global $wpdb;
    $table_name = $wpdb->prefix . 'pacp_post_table';
    try {
		$id = isset($_POST['id']) ? absint($_POST['id']) : 0;
		$triger = isset($_POST['triger']) ? sanitize_text_field($_POST['triger']) : '';
		$newdate = current_time('Y-m-d H:i:s');

		if ($triger == 'trash') {

			$cache_key = 'pacp_post_trash';
			$cached_result = wp_cache_get($cache_key);
			if ($cached_result === false) {
			    $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE `type` = %s", 'post'));
			    wp_cache_set($cache_key, $result);
			} else {
			    $result = $cached_result;
			}

			foreach ($result as $key => $value) {
				if (strpos($value->pacp_taxonomies, ',') !== false) {
					$ids = explode(',', $value->pacp_taxonomies);
					$ids = array_filter($ids, function ($item) use ($id) {
					    return $item !== $id;
					});
					$ids = array_values($ids);
					$ids = implode(',', $ids);
				}else{
					if ($value->pacp_taxonomies == $id) {
						$ids = '';
					}else{
						$ids = $value->pacp_taxonomies;
					}
				}
				$data = array(
			        'pacp_taxonomies' => $ids
			    );
				$where = array('id' => $value->id);
				$wpdb->update($table_name, $data, $where);
			}
 
	        $wpdb->query($wpdb->prepare("DELETE FROM $table_name WHERE `id` = %d", $id));
	        echo wp_json_encode([
	            'status'=> true,
	            'message'=>'Success'
	        ], true);

	    }elseif($triger == 'activate'){
	    	$data = array(
		        'status' => 1,
		        'updated_at' => $newdate
		    );
	        $where = array('id' => $id);
            $wpdb->update($table_name, $data, $where);
	    }elseif($triger == 'deactivate'){
	    	$data = array(
		        'status' => 0,
		        'updated_at' => $newdate
		    );
	        $where = array('id' => $id);
            $wpdb->update($table_name, $data, $where);

	    }elseif($triger == 'duplicate'){

	  
	    	$result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE `id` = %d", $id))[0];
			$data = pacp_duplicate_taxonomies($result);
	        $wpdb->insert($table_name, $data);

	        echo wp_json_encode([
	            'status'=> true,
	            'message'=>'Success'
	        ], true);
	        


	    }
	    flush_rewrite_rules();
        wp_die();
	} catch (Exception $e) {
        echo wp_json_encode([
            'status'=>false,
            'message'=>$e->getMessage()
        ], true);
        wp_die();
    }
}


add_action('wp_ajax_pacp_setting_post', 'pacp_setting_post_handler' );
add_action('wp_ajax_nopriv_pacp_setting_post','pacp_setting_post_handler');
function pacp_setting_post_handler(){
	global $wpdb;
	$table_name = $wpdb->prefix . 'pacp_post_setting';


	// Check if data is cached
	$cache_key = 'pacp_post_setting';
	$cached_result = wp_cache_get($cache_key);
	if ($cached_result === false) {
		$result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE `setting` = %s", 'per_page'))[0]; 
		wp_cache_set($cache_key, $result);
	} else {
		$result = $cached_result;
	}
 
	if (isset($_POST['setting_post_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['setting_post_nonce'])), 'setting_post')) {
	    if (isset($_POST['setting_per_pages'])) {

	    	try {
		        $new_value = intval($_POST['setting_per_pages']);
		        $wpdb->update(
		            $table_name,
		            array('data' => $new_value),
		            array('setting' => 'per_page'),
		            array('%d'),
		            array('%s')
		        );

		        echo wp_json_encode([
		            'status'=> true,
		            'message'=>'Success'
		        ], true);
		        wp_die();
	        } catch (Exception $e) {
		        echo wp_json_encode([
		            'status'=>false,
		            'message'=>$e->getMessage()
		        ], true);
		        wp_die();
		    }
	    }
	}

}