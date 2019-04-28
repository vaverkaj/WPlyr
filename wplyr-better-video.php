<?php
/**
* Plugin Name: WPlyr - Better Video Player
* Description: This plugin implements Plyr for extending WordPress video playback functionality
* Version: 0.9
* Author: Jakub Váverka
* Author URI: https://github.com/LaserPork
**/
if ( !defined('ABSPATH') )
    define('ABSPATH', dirname(__FILE__) . '/');


function wp_wplyr_video_custom_script_load(){
    wp_enqueue_script( 'plyr-polyfilled-min', plugin_dir_url(__FILE__) .'/player/node_modules/plyr/dist/plyr.polyfilled.min.js');
    wp_enqueue_style( 'plyr-stylesheet', plugin_dir_url(__FILE__) .'/player/node_modules/plyr/dist/plyr.css');
    wp_enqueue_style( 'bootstrap-stylesheet', plugin_dir_url(__FILE__) .'/player/node_modules/bootstrap/dist/css/bootstrap.min.css');

    wp_enqueue_script( 'player-min', plugin_dir_url(__FILE__) .'/player/dist/player.min.js');
    wp_enqueue_style( 'player-stylesheet', plugin_dir_url(__FILE__) .'/player/dist/player.css');

}

function wp_wplyr_video_gutenberg_register_block() {
    wp_register_script( 
        'wp_wplyr_video_element', 
        plugins_url('wplyr-video-element.js', __FILE__ ),
        array( 'wp-element')
    );
    
    wp_register_script(
        'wp_wplyr_video_gutenberg_block',
        plugins_url( 'wplyr-gutenberg-block.js', __FILE__ ),
        array( 'wp-blocks', 'wp-element', 'wp_wplyr_video_element')
    );

    register_block_type( 'wplyr-better-video/wplyr-video-block', array(
        'script' => 'wp_wplyr_video_element',
        'editor_script' => 'wp_wplyr_video_gutenberg_block',
    ) );
}

function wp_wplyr_modify_jsx_tag( $tag, $handle, $src ) {
    // Check that this is output of JSX file
    if ( 'wp_wplyr_video_element' == $handle ) {
      $tag = str_replace( "<script type='text/javascript'", "<script type='text/babel'", $tag );
    }

    return $tag;
}

//add_filter( 'script_loader_tag', 'wp_wplyr_modify_jsx_tag', 10, 3 );

add_action( 'wp_enqueue_scripts', 'wp_wplyr_video_custom_script_load' );
add_action( 'init', 'wp_wplyr_video_gutenberg_register_block' );

