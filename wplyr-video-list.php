<?php

class Video_List extends WP_List_Table
{

    /** Class constructor */
    public function __construct()
    {

        parent::__construct([
            'singular' => 'Video', //singular name of the listed records
            'plural'   => 'Videos', //plural name of the listed records
            'ajax'     => false //should this table support ajax?

        ]);
    }



    public function prepare_items()
    {

        $this->_column_headers = $this->get_column_info();

        /** Process bulk action */
        $this->process_bulk_action();

        $per_page     = $this->get_items_per_page('videos_per_page_option', 5);
        $current_page = $this->get_pagenum();
        $total_items  = self::record_count();

        $this->set_pagination_args([
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page'    => $per_page //WE have to determine how many items to show on a page
        ]);


        $args = array(
            'post_type' => 'wp_wplyr_videos',
            'posts_per_page' => $per_page,
            'offset'  => $current_page
        );

        $this->items = get_posts($args);

        //$columns = $this->get_columns();
        $this->_column_headers = array(
            $this->get_columns(),        // columns
            array(),            // hidden
            $this->get_sortable_columns(),    // sortable
        );
        //$_wp_column_headers[get_current_screen()->id]=$columns;
        //$this->items = self::get_customers( $per_page, $current_page );
    }

    /**
     *  Associative array of columns
     *
     * @return array
     */
    function get_columns()
    {
        return $columns = array(
            'ID' => __('ID'),
            'post_title' => __('Name'),
            'post_modified' => __('Last modified'),
            'post_date' => __('Created'),
            'shortcode' => __('Shortcode'),
        );

        return $columns;
    }

    /**
     * Returns the count of records in the database.
     *
     * @return null|string
     */
    public static function record_count()
    {
        //var_dump(wp_count_posts('wp_wplyr_videos'));
        return wp_count_posts('wp_wplyr_videos')->publish;
    }



    /** Text displayed when no customer data is available */
    public function no_items()
    {
        _e('No videos avaliable.', 'sp');
    }


    /**
     * Method for name column
     *
     * @param array $item an array of DB data
     *
     * @return string
     */
    function column_name($item)
    {

        // create a nonce
        //$delete_nonce = wp_create_nonce( 'sp_delete_customer' );

        $title = '<strong>' . $item['name'] . '</strong>';

        //$actions = [
        //  'delete' => sprintf( '<a href="?page=%s&action=%s&customer=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['ID'] ), $delete_nonce )
        //];

        return $title; // . $this->row_actions( $actions );
    }


    public function column_default($item, $column_name)
    {
        switch ( $column_name ) {
            case 'shortcode':
                return '<button class="button" 
                                type="button" 
                                onclick="insert_shortcode_into_editor('.$item->ID.')">
                                Insert shortcode
                                </button>';
            default:
              return print_r($item->$column_name, true);
        }
    }

    /**
     * Columns to make sortable.
     *
     * @return array
     */
    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'ID' => 'ID'
        );

        return $sortable_columns;
    }

    public function display()
    {
        echo '<script>function insert_shortcode_into_editor(video_id) {
        if (tinyMCE && tinyMCE.activeEditor) {
            tinymce.activeEditor.execCommand("mceInsertContent", false,
                "[wplyr id=" + video_id + "]"
            );}}</script>';
        parent::display();
    }
    
}
