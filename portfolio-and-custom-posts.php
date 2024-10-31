<?php
/**
 * Portfolio and Custom Posts
 *
 * @package       PORTFOLIOA
 * @author        Peritos Solutions
 * @license       gplv2
 * @version       1.15.22
 *
 * @wordpress-plugin
 * Plugin Name:   Portfolio and Custom Posts
 * Plugin URI:    https://peritossolutions.com/
 * Description:   Portfolio and Custom Posts
 * Version:       1.15.22
 * Author:        Peritos Solutions
 * Author URI:    https://peritossolutions.com/
 * Text Domain:   portfolio-and-custom-posts
 * Domain Path:   /languages
 * License:       GPLv2
 * License URI:   https://www.gnu.org/licenses/gpl-2.0.html
 *
 * You should have received a copy of the GNU General Public License
 * along with Portfolio and Custom Posts. If not, see <https://www.gnu.org/licenses/gpl-2.0.html/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;


//register_activation_hook(__FILE__, 'portfolio_and_custom_posts_activation');
// register_uninstall_hook(__FILE__, 'pacp_portfolio_and_custom_posts_uninstall');

 
require_once( plugin_dir_path( __FILE__ ) . 'block/cpt.php');
require_once( plugin_dir_path( __FILE__ ) . 'model/table.php');
require_once( plugin_dir_path( __FILE__ ) . 'controller/admin.php');
require_once( plugin_dir_path( __FILE__ ) . 'controller/index.php');


function pacp_enqueue_jquery() {
    wp_enqueue_script('jquery');
}
add_action('wp_enqueue_scripts', 'pacp_enqueue_jquery');

function pacp_admin_css() {
    wp_enqueue_style('pacp-admin-css', plugins_url('/assets/css/pacp-admin.css', __FILE__), array(), '1.0.0');
}
add_action('admin_enqueue_scripts', 'pacp_admin_css');


add_action('admin_menu', 'pacp_post_list');
function pacp_post_list() {
    add_menu_page(
        'Portfolio and Custom Posts',
        'Portfolio and Custom Posts',
        'manage_options',
        'portfolio-and-custom-posts',
        '',
        'dashicons-welcome-widgets-menus',
        199
    );
    add_submenu_page(
        'portfolio-and-custom-posts',
        'Post Types',
        'Post Types',
        'manage_options',
        'portfolio-and-custom-posts',
        'pacp_portfolio_and_custom_posts_callback'
    );
    add_submenu_page(
        'portfolio-and-custom-posts',
        'Add Post Type',
        'Add Post Type',
        'manage_options',
        'pacp-post-type',
        'pacp_add_new_callback'
    );
}
function pacp_portfolio_and_custom_posts_callback(){
    require_once( plugin_dir_path( __FILE__ ) . 'view/admin/post-list.php');
}
 
function pacp_add_new_callback(){
    require_once( plugin_dir_path( __FILE__ ) . 'view/admin/pacp-post-type.php');
}

add_action('admin_menu', 'pacp_taxonomy_list');
function pacp_taxonomy_list() {
    add_submenu_page(
        'portfolio-and-custom-posts',
        'Taxonomies',
        'Taxonomies',
        'manage_options',
        'pacp-taxonomy-type',
        'pacp_taxonomy_list_callback'
    );

    add_submenu_page(
        'portfolio-and-custom-posts',
        'Add Taxonomy',
        'Add Taxonomy',
        'manage_options',
        'pacp-taxonomy-add',
        'pacp_taxonomy_add_callback'
    );

    add_submenu_page(
        'portfolio-and-custom-posts',
        'Settings',
        'Settings',
        'manage_options',
        'pacp-post-settings',
        'pacp_settings_callback'
    );

    $result = pacp_get_PACP_post_data('post');

    foreach ($result as $key => $value) {
        if ($value->status == 1) {
            add_submenu_page(
                'portfolio-and-custom-posts',
                $value->title,
                $value->title,
                'manage_options',
                'edit.php?post_type=' . $value->post_type
            );
        }
    }
}
function pacp_taxonomy_add_callback(){
    require_once( plugin_dir_path( __FILE__ ) . 'view/admin/pacp-post-taxonomy.php');
}
function pacp_taxonomy_list_callback(){
    require_once( plugin_dir_path( __FILE__ ) . 'view/admin/taxonomy-list.php');
}

function pacp_settings_callback() {
    require_once( plugin_dir_path( __FILE__ ) . 'view/admin/settings.php');
}

function pacp_admin_content() {
    require_once( plugin_dir_path( __FILE__ ) . 'view/admin/index.php');
}
add_action('admin_notices', 'pacp_admin_content');