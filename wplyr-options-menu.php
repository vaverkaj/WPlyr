<?php
function wp_wplyr_register_menu()
{
    add_options_page('Wplyr Plugin Options', 'WPlyr Plugin', 'manage_options', 'wplyr-video-menu', 'wp_wplyr_menu');
}

function wp_wplyr_menu()
{
    echo $GLOBALS['wplyr_video_path'];
    ?>
    <div class="wrap">
        <form method="post" action="options.php">
            <?php
            settings_fields('wplyr-video-menu');	
            do_settings_sections('wplyr-video-menu');
            submit_button();
            ?>
        </form>
    </div>
<?php
}

add_action('admin_menu', 'wp_wplyr_register_menu');

// ------------------------------------------------------------------
// Add all your sections, fields and settings during admin_init
// ------------------------------------------------------------------
//

function wplyr_settings_api_init()
{

    // Add the section to reading settings so we can add our
    // fields to it
    add_settings_section(
        'wplyr_setting_section',
        'WPlyr Plugin Options',
        'wplyr_setting_section_callback_function',
        'wplyr-video-menu'
    );

    // Add the field with the names and function to use for our new
    // settings, put it in our new section
    add_settings_field(
        'wplyr_setting_video_path',
        'Path to the video files:',
        'wplyr_setting_callback_function',
        'wplyr-video-menu',
        'wplyr_setting_section'
    );

    // Register our setting so that $_POST handling is done for us and
    // our callback function just has to echo the <input>
    register_setting('wplyr-video-menu', 'wplyr_setting_video_path');
}

add_action('admin_init', 'wplyr_settings_api_init');


// ------------------------------------------------------------------
// Settings section callback function
// ------------------------------------------------------------------
//
// This function is needed if we added a new section. This function 
// will be run at the start of our section
//

function wplyr_setting_section_callback_function()
{ }

// ------------------------------------------------------------------
// Callback function for our example setting
// ------------------------------------------------------------------
//
// creates a checkbox true/false option. Other types are surely possible
//

function wplyr_setting_callback_function()
{
    ?>
    <label name="wplyr_setting_video_path" 
    id="wplyr_setting_video_path" 
     ><?php echo get_option('wplyr_setting_video_path')?></label>
     <?php
    
}

function wplyr_modify_global_paths(){
    
}

add_action('update_option_wplyr_setting_video_path', 'wplyr_modify_global_paths');
?>