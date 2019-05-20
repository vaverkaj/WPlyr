<?php
/**
 * Plugin Name: WPlyr - Better Video Player
 * Description: This plugin implements Plyr for extending WordPress video playback functionality
 * Version: 0.9
 * Author: Jakub Váverka
 * Author URI: https://github.com/LaserPork
 **/
if (!defined('ABSPATH'))
    define('ABSPATH', dirname(__FILE__) . '/');

include("phpFileTree/php_file_tree.php");

if (!defined('WPLYR_VIDEO_PATH'))
    define('WPLYR_VIDEO_PATH', $_SERVER['DOCUMENT_ROOT'] . "/wordpress/wp-content/videos");
    define('WPLYR_VIDEO_URL', get_site_url() . "/../wordpress/wp-content/videos");
    

function is_gutenberg_active()
{
    $gutenberg    = false;
    $block_editor = false;

    if (has_filter('replace_editor', 'gutenberg_init')) {
        // Gutenberg is installed and activated.
        $gutenberg = true;
    }

    if (version_compare($GLOBALS['wp_version'], '5.0-beta', '>')) {
        // Block editor.
        $block_editor = true;
    }

    if (!$gutenberg && !$block_editor) {
        return false;
    }

    include_once ABSPATH . 'wp-admin/includes/plugin.php';

    if (!is_plugin_active('classic-editor/classic-editor.php')) {
        return true;
    }

    $use_block_editor = (get_option('classic-editor-replace') === 'no-replace');

    return $use_block_editor;
}

function wp_wplyr_video_custom_script_load()
{
    wp_enqueue_script('plyr-polyfilled-min', plugin_dir_url(__FILE__) . '/player/node_modules/plyr/dist/plyr.polyfilled.min.js');
    wp_enqueue_style('plyr-stylesheet', plugin_dir_url(__FILE__) . '/player/node_modules/plyr/dist/plyr.css');
    //wp_enqueue_style( 'bootstrap-stylesheet', plugin_dir_url(__FILE__) .'/player/node_modules/bootstrap/dist/css/bootstrap.min.css');
    wp_enqueue_script('player-min', plugin_dir_url(__FILE__) . '/player/dist/player.min.js');
    wp_enqueue_style('player-stylesheet', plugin_dir_url(__FILE__) . '/player/dist/player.css');
}


function wp_wplyr_video_gutenberg_register_block()
{
    if (is_gutenberg_active()) {
        echo 'true';
    } else {
        echo 'false';
    }
    wp_register_script(
        'wp_wplyr_video_element',
        plugins_url('wplyr-video-element.js', __FILE__),
        array('wp-element')
    );

    wp_register_script(
        'wp_wplyr_video_gutenberg_block',
        plugins_url('wplyr-gutenberg-block.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp_wplyr_video_element')
    );

    register_block_type('wplyr-better-video/wplyr-video-block', array(
        'script' => 'wp_wplyr_video_element',
        'editor_script' => 'wp_wplyr_video_gutenberg_block',
    ));

    register_meta('post', 'wp_wplyr_meta_block_field', array(
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
    ));
}


function wp_wplyr_register_content_type()
{
    //Labels for post type
    $labels = array(
        'name'               => 'WPlyr Video Manager',
        'singular_name'      => 'Video',
        'menu_name'          => 'Videos',
        'name_admin_bar'     => 'Video',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Video',
        'new_item'           => 'New Video',
        'edit_item'          => 'Edit Video',
        'view_item'          => 'View Video',
        'all_items'          => 'All Videos',
        'search_items'       => 'Search Videos',
        'parent_item_colon'  => 'Parent Video:',
        'not_found'          => 'No Videos found.',
        'not_found_in_trash' => 'No Videos found in Trash.',
    );
    //arguments for post type
    $args = array(
        'labels'            => $labels,
        'public'            => false,
        'publicly_queryable' => false,
        'show_ui'           => true,
        'show_in_nav'       => true,
        'query_var'         => true,
        'hierarchical'      => false,
        'supports'          => array('title'),
        'has_archive'       => true,
        'menu_position'     => 20,
        'show_in_admin_bar' => true,
        'menu_icon'         => 'dashicons-video-alt',
        'rewrite'            => array('slug' => 'videos', 'with_front' => 'true')
    );
    //register post type
    register_post_type('wp_wplyr_videos', $args);
}

function wp_wplyr_add_option_box()
{
    if (!is_gutenberg_active()) {
        add_meta_box(
            'wp_wplyr_post_picker_id',           // Unique ID
            'Embed WPlyr video',  // Box title
            'wp_wplyr_post_picker_html',  // Content callback, must be of type callable
            'post'                   // Post type
        );
    }

    add_meta_box(
        'wp_wplyr_video_box_id',           // Unique ID
        'Video options',  // Box title
        'wp_wplyr_option_box_html',  // Content callback, must be of type callable
        'wp_wplyr_videos'                   // Post type
    );
}

function wp_wplyr_post_picker_html($post)
{

    $args = array(
        'post_type' => 'wp_wplyr_videos',
        'posts_per_page' => -1,
        'numberposts' => -1
    );
    $posts = get_posts($args);
    foreach ($posts as $key => $post) {
        $posts[$key]->acf = get_post_meta($post->ID);
        ?>
        <table class="wp-list-table widefat fixed striped posts">
            <tr>
                <td style="width:20px"> <?php echo $post->ID ?> </td>
                <td> <?php echo $post->post_title ?> </td>
                <td> <b>[wplyr id=<?php echo $post->ID ?>]</b> </td>
                <td> <button class="button" type="button" onclick="insert_shortcode_into_editor(<?php echo $post->ID ?>)">Insert shortcode</button> </td>
            </tr>
        </table>

        <script>
            function insert_shortcode_into_editor(video_id) {
                if (tinyMCE && tinyMCE.activeEditor) {
                    tinymce.activeEditor.execCommand('mceInsertContent', false,
                        "[wplyr id=" + video_id + "]"
                    );
                }
            }
        </script>
    <?php
}
}

function wp_wplyr_option_box_html($post)
{
    wp_enqueue_script('file-tree-script', plugin_dir_url(__FILE__) . '/phpFileTree/php_file_tree.js');
    wp_enqueue_style('file-tree-stylesheet', plugin_dir_url(__FILE__) . '/phpFileTree/styles/default/default.css');
    $value = get_post_meta($post->ID, '_wp_wplyr_options', true);
    ?>
    <div style="margin:5pt">
        <label>Path to the video file:</label>
        <b class="wp_wplyr_video_name_tag" style="float:right;">
            <?php echo ($value); ?>
        </b>
        <input name="wp_wplyr_video_path" value="<?php echo ($value); ?>" type="text" class="wp_wplyr_video_name_tag" readonly hidden>
    </div>
    <?php
    echo php_file_tree(WPLYR_VIDEO_PATH, "javascript:change_picked_file('[link]');");
}

function  videos_rest_endpoint($request_data)
{
    $args = array(
        'post_type' => 'wp_wplyr_videos',
        'posts_per_page' => -1,
        'numberposts' => -1
    );
    $posts = get_posts($args);
    foreach ($posts as $key => $post) {
        $posts[$key]->acf = get_post_meta($post->ID);
    }
    return  $posts;
}

function wp_wplyr_save_postdata($post_id)
{
    if (array_key_exists('wp_wplyr_video_path', $_POST)) {
        update_post_meta(
            $post_id,
            '_wp_wplyr_options',
            $_POST['wp_wplyr_video_path']
        );
    }
}

function wp_wplyr_video_setup()
{

    ?>
    <script>
        function wp_wplyr_video_setup() {
           
        }
    </script>
<?php
}

function wp_wplyr_video_shortcode($id)
{
    extract(shortcode_atts(array(
        'id' => 'id'
    ), $id));
    $source = WPLYR_VIDEO_URL.get_post_meta($id)["_wp_wplyr_options"][0];
    $html = ' <div class="container">
                <div class="video-container" id="container">
                 <video controls crossorigin playsinline id="player">
                 <source src="'.$source.'" type="video/mp4">
                 </video>
                </div>
            </div>';
    return $html;
}

//add_filter( 'script_loader_tag', 'wp_wplyr_modify_jsx_tag', 10, 3 );
add_action('init', 'wp_wplyr_register_content_type');
if (is_gutenberg_active()) {
    add_action('init', 'wp_wplyr_video_gutenberg_register_block');
} else {
    add_shortcode("wplyr", "wp_wplyr_video_shortcode");
}
add_action('wp_enqueue_scripts', 'wp_wplyr_video_custom_script_load');
add_action('wp_enqueue_scripts', 'wp_wplyr_video_setup');
add_action('add_meta_boxes', 'wp_wplyr_add_option_box');
add_action('save_post', 'wp_wplyr_save_postdata');
add_action('rest_api_init', function () {
    register_rest_route('wplyr', '/videos/', array(
        'methods' => 'GET',
        'callback' => 'videos_rest_endpoint'
    ));
});
