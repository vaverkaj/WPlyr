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
}

function wp_wplyr_modify_jsx_tag($tag, $handle, $src)
{
    // Check that this is output of JSX file
    if ('wp_wplyr_video_element' == $handle) {
        $tag = str_replace("<script type='text/javascript'", "<script type='text/babel'", $tag);
    }

    return $tag;
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
    add_meta_box(
        'wp_wplyr_box_id',           // Unique ID
        'Video options',  // Box title
        'wp_wplyr_option_box_html',  // Content callback, must be of type callable
        'wp_wplyr_videos'                   // Post type
    );
}

function wp_wplyr_option_box_html($post)
{
    $value = get_post_meta($post->ID, '_wp_wplyr_options', true);
    echo ("-- " . $value . " --");
    ?>
        <div style="margin:5pt">
            <label>Path to the video file:</label>
            <input type="file" name="wp_wplyr_video_path" accept="video/*" value="<?php echo($value); ?>"  style="float:right">
        </div>
<?php
}

function  videos_rest_endpoint( $request_data ) {
    $args = array(
        'post_type' => 'wp_wplyr_videos',
        'posts_per_page'=>-1, 
        'numberposts'=>-1
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

//add_filter( 'script_loader_tag', 'wp_wplyr_modify_jsx_tag', 10, 3 );
add_action('init', 'wp_wplyr_register_content_type');
add_action('init', 'wp_wplyr_video_gutenberg_register_block');
add_action('wp_enqueue_scripts', 'wp_wplyr_video_custom_script_load');
add_action('add_meta_boxes', 'wp_wplyr_add_option_box');
add_action('save_post', 'wp_wplyr_save_postdata');
add_action( 'rest_api_init', function () {
    register_rest_route( 'wplyr', '/videos/', array(
        'methods' => 'GET',
        'callback' => 'videos_rest_endpoint'
    ));
});