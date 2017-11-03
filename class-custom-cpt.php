<?php

/*
 *  Custom Post Type Theme Class
 * @class 		MM_Register_Custom_CPT
 * @version		1.0
 * @package		
 * @category             Class
 */

class MM_Register_Custom_CPT {

    public $post_type_name;
    public $post_type_args;
    public $post_type_labels;
    public $post_type_custom_args;

    /* Class constructor */

    public function __construct($name, $cpt_args = array()) {

        // Set some important variables
        $args = array(); 
        $labels = array();

        $this->post_type_name = strtolower(str_replace(' ', '_', $name));
        $this->post_type_args = $args;
        $this->post_type_labels = $labels;

        $this->post_type_custom_args = $cpt_args;

        // Add action to register the post type, if the post type does not already exist
        if (!post_type_exists($this->post_type_name)) {
            add_action('init', array(&$this, 'register_post_type'));
        }

    }

    /* Method which registers the post type */

    public function register_post_type() {
        //Capitilize the words and make it plural
        $name = ucwords(str_replace('_', ' ', $this->post_type_name));
      
        $plural = self::pluralize( $name );
                // We set the default labels based on the post type name and plural. We overwrite them with the given labels.
                $labels = array_merge(
                        // Default
                        array(
                            'name' => _x($plural, 'post type general name'),
                            'singular_name' => _x($name, 'post type singular name'),
                            'add_new' => _x('Add New', strtolower($name)),
                            'add_new_item' => __('Add New ' . $name),
                            'edit_item' => __('Edit ' . $name),
                            'new_item' => __('New ' . $name),
                            'all_items' => __('All ' . $plural),
                            'view_item' => __('View ' . $name),
                            'search_items' => __('Search ' . $plural),
                            'not_found' => __('No ' . strtolower($plural) . ' found'),
                            'not_found_in_trash' => __('No ' . strtolower($plural) . ' found in Trash'),
                            'parent_item_colon' => '',
                            'menu_name' => $name
                        ),
                        // Given labels
                        $this->post_type_labels
                );

                // Same principle as the labels. We set some defaults and overwrite them with the given arguments.
    
                $public = true;
                $publicly_queryable = true;
                $show_ui = true;
                $show_in_menu = true;
                $query_var = true;
                $show_in_nav_menus = true;
                $menu_icon = null;
                $menu_position = null;
                $supports = array('title', 'author', 'thumbnail');
                $hierarchical = true;
                $rewrite_slug_default = sanitize_title_with_dashes($name);
                $has_archive = false;

                $cpt_args = $this->post_type_custom_args;
                if(!empty($cpt_args)) {
                    $public = $cpt_args[0];
                    $publicly_queryable = $cpt_args[1];
                    $show_ui = $cpt_args[2];
                    $show_in_menu = $cpt_args[3];
                    $query_var = $cpt_args[4];
                    $show_in_nav_menus = $cpt_args[5];
                    $menu_icon = $cpt_args[6];
                    $menu_position = $cpt_args[7];
                    $supports = $cpt_args[8];
                    $hierarchical = $cpt_args[9];
                    $rewrite_slug = $cpt_args[10];
                    $has_archive = $cpt_args[11];
                }

                if($rewrite_slug=="") {
                    $rewrite_slug = $rewrite_slug_default;
                } else {
                    $rewrite_slug = $rewrite_slug;
                }
                
                $args = array_merge(
                        // Default
                        array(
                            'label' => $plural,
                            'labels' => $labels,
                            'public'             => $public,
                            'publicly_queryable' => $publicly_queryable,
                            'show_ui'            => $show_ui,
                            'show_in_menu'       => $show_in_menu,
                            'query_var'          => $query_var,
                            'supports' => $supports,
                            'menu_icon' => $menu_icon,
                            'menu_position' => $menu_position,
                            'show_in_nav_menus' => $show_in_nav_menus,
                            '_builtin' => false,
                            'has_archive' => $has_archive,
                            'rewrite' => array( 'slug' => $rewrite_slug ),

                ),
                // Given args
                $this->post_type_args
        );

        // Register the post type
        register_post_type($this->post_type_name, $args);
        flush_rewrite_rules();
    }

    /* Method to attach the taxonomy to the post type */

    public function add_custom_taxonomy() {
        //http://code.tutsplus.com/articles/custom-post-type-helper-class--wp-25104
    }

    //helper function
    public static function pluralize($string) {
        $last = $string[strlen($string) - 1];

        if ($last == 'y') {
            $cut = substr($string, 0, -1);
            //convert y to ies
            $plural = $cut . 'ies';
        } else {
            // just attach an s
            $plural = $string . 's';
        }

        return $plural;
    }

}
