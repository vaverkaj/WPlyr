<?php

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
    /*
    $video_list = new Video_List();
    $video_list->prepare_items();
    $video_list->display(); 
    */
    
}


function wp_wplyr_option_box_html($post)
{
    wp_enqueue_script('wplyr-file-tree-script', plugin_dir_url(__FILE__) . '/video_editor/phpFileTree/php_file_tree.js');
    wp_enqueue_style('wplyr-file-tree-stylesheet', plugin_dir_url(__FILE__) . '/video_editor/phpFileTree/styles/default/default.css');
    wp_enqueue_script('wplyr-editor-script', plugin_dir_url(__FILE__) . '/video_editor/editor_script.js');
    wp_enqueue_style('wplyr-editor-stylesheet', plugin_dir_url(__FILE__) . '/video_editor/editor_style.css');
    ?>

    <div class="wplyr_editor_border" id="wplyr_hidden_path_editor" hidden>
        <a class="wplyr_editor_remove_button" onclick="remove_editor_field(this.parentNode)"></a>
        <div class="wplyr-bar">
            <a class="wplyr-bar-item wplyr-button" onclick="openTab(this,'Video')">Video</a>
            <a class="wplyr-bar-item wplyr-button" onclick="openTab(this,'YouTube')">YouTube</a>
        </div>
        <input name="wp_wplyr_video_type[]" value="video" class="wp_wplyr_video_type_value" type="text" readonly hidden>
        <input name="wp_wplyr_video_source[]" type="text" class="wp_wplyr_video_source_value" readonly hidden>
        <div class="wplyr-tab wplyr-video-tab">
            <div>
                <label>Path to the video file:</label>
                <b class="wp_wplyr_video_source_value"></b>
            </div>
            <?php
            echo php_file_tree(wplyr_video_path, "change_picked_file('[link]',this);");
            ?>
        </div>
        <div class="wplyr-tab wplyr-youtube-tab" style="display: none;">
            <div>
                <label>Url of the video:</label>
                <input type="text" class="wp_wplyr_youtube_input wp_wplyr_video_source_value" onchange="updateInputs()" value="">
            </div>
        </div>
    </div>

    <div id="wplyr_editor_container">
        <?php
        $sources = get_post_meta($post->ID, '_wp_wplyr_video_source', true);
        $types = get_post_meta($post->ID, '_wp_wplyr_video_type', true);
        if(!empty($sources)){
            for ($i = 1; $i < sizeof($sources); $i++) {
                wp_wplyr_print_editor($sources[$i], $types[$i]);
            }
        }
        ?>
    </div>
    <a class="wplyr_editor_add_button" onclick="add_editor_field()"></a>
<?php
}

function wp_wplyr_save_postdata($post_id)
{
    if (array_key_exists('wp_wplyr_video_source', $_POST)) {
        update_post_meta(
            $post_id,
            '_wp_wplyr_video_source',
            $_POST['wp_wplyr_video_source']
        );
    }
    if (array_key_exists('wp_wplyr_video_type', $_POST)) {
        update_post_meta(
            $post_id,
            '_wp_wplyr_video_type',
            $_POST['wp_wplyr_video_type']
        );
    }
}


function wp_wplyr_print_editor($source, $type)
{
    ?>
    <div class="wplyr_editor_border" id="wplyr_hidden_path_editor">
        <a class="wplyr_editor_remove_button" onclick="remove_editor_field(this.parentNode)"></a>
        <div class="wplyr-bar">
            <a class="wplyr-bar-item wplyr-button" onclick="openTab(this,'Video')">Video</a>
            <a class="wplyr-bar-item wplyr-button" onclick="openTab(this,'YouTube')">YouTube</a>
        </div>
        <input name="wp_wplyr_video_type[]" value="<?php echo $type; ?>" type="text" class="wp_wplyr_video_type_value" readonly hidden>
        <input name="wp_wplyr_video_source[]" value="<?php echo $source; ?>" type="text" class="wp_wplyr_video_source_value" readonly hidden>
        <div class="wplyr-tab wplyr-video-tab" style="<?php
            if($type == 'video'){
                echo "display: block;";
            }else if($type == 'youtube'){
                echo "display: none;";
            }
        ?>">
            <div>
                <label>Path to the video file:</label>
                <b class="wp_wplyr_video_source_value"><?php echo $source; ?></b>
            </div>
            <?php
            echo php_file_tree(wplyr_video_path, "change_picked_file('[link]',this);");
            ?>
        </div>
        <div class="wplyr-tab wplyr-youtube-tab" style="<?php
            if($type == 'video'){
                echo "display: none;";
            }else if($type == 'youtube'){
                echo "display: block;";
            }
        ?>">
            <div>
                <label>Url of the video:</label>
                <input class="wp_wplyr_youtube_input wp_wplyr_video_source_value" onchange="updateInputs()" type="text" value="<?php echo $source; ?>">
            </div>
        </div>
    </div>
<?php
}

add_action('save_post', 'wp_wplyr_save_postdata');
add_action('add_meta_boxes', 'wp_wplyr_add_option_box');






