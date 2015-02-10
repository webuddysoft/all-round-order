<?php
/*
Plugin Name: All Round Order
Plugin URI: http://store.webuddysoft.com
Description: Posts, Custom Post Types and Media Library Order using a Drag and Drop feature
Author: Eric So
Author URI: http://webuddysoft.com
Version: 1.0.0
*/

define('WBSOFT_ORDER_PLUGIN_PATH',   plugin_dir_path(__FILE__));
define('WBSOFT_ORDER_PLUGIN_URL',    plugins_url('', __FILE__));
define('WBSOFT_ORDER_PLUGIN_CAN_SORT',    'manage_options');

require_once(WBSOFT_ORDER_PLUGIN_PATH . '/settings.php');
require_once(WBSOFT_ORDER_PLUGIN_PATH . '/functions.php');
require_once(WBSOFT_ORDER_PLUGIN_PATH . '/classes/class.walk.php');

/**
* Plugin Activation Hook Function
* Init Order Options
*/
register_activation_hook(__FILE__, 'wbsoft_order_plugin_actived');
function wbsoft_order_plugin_actived() 
{
    //make sure the vars are set as default
    $options = get_option('wbsoft_order_options');
    
    if (!isset($options['auto_sort']))
        $options['auto_sort'] = '1';
        
/*    if (!isset($options['can_sort']))
        $options['can_sort'] = 'manage_options';*/
        
    update_option('wbsoft_order_options', $options);
}

/**
* Plugin Deactivation Hook Function
* Remove Options
*/
register_deactivation_hook(__FILE__, 'wbsoft_order_plugin_deactived');
function wbsoft_order_plugin_deactived() 
{
    //To do: This function will be called when this plugin is de-actived    
    delete_option('wbsoft_order_options');
}

/**
* Set Suppress_filters to false if auto_sort is checked
*/
add_filter('pre_get_posts', 'wbsoft_order_pre_get_posts');
function wbsoft_order_pre_get_posts($query_object)
{
    global $post;
    
    if(is_object($post) && $post->ID < 1) 
    { 
        return $query_object; 
    }  
    
       
    $options = wbsoft_order_plugin_options();
    
    if (is_admin())
    {        
        return false;   
    }
    
    if ($options['auto_sort'] == "1")
    {   
        $query_object->set('suppress_filters', false);
    }
        
    return $query_object;
}

/**
* Add menu_order if auto sort is checked
*/
add_filter('posts_orderby', 'wbsoft_order_posts_orderby', 99, 2);
function wbsoft_order_posts_orderby($order_by, $query_object) 
{
    global $wpdb;
    
    $options = wbsoft_order_plugin_options();
    
    //Ignore search
    if($query_object->is_search())
        return($order_by);
    
    if ($options['auto_sort'] == "1")
        $order_by = "{$wpdb->posts}.menu_order, " . $order_by;
    
    return $order_by;
}
    
add_action('admin_menu', 'wbsoft_order_add_admin_menu' );     
/**
* Add menu to admin section
* 
*/
function wbsoft_order_add_admin_menu() 
{
    
    $options = wbsoft_order_plugin_options();
    //Check Current User Roles    
    if (!current_user_can(WBSOFT_ORDER_PLUGIN_CAN_SORT))
    {
        return;
    }        
    
    //Add order posts section over all post types
    $post_types = get_post_types();
        
    foreach( $post_types as $post_type ) 
    {
        //Ignore BBPress
        if($post_type == 'reply' || $post_type == 'topic' || $post_type == 'forum')
            continue; 
        
        //Add Attachment Order
        if($post_type == 'attachment')
        {
            add_submenu_page('upload.php', __('Custom Order', 'wbso'), __('Custom Order', 'wbso'), WBSOFT_ORDER_PLUGIN_CAN_SORT, 'custom-order-'.$post_type, 'wbsoft_order_show_order_page');
        }
            
        
        if ($post_type == 'post')
        {
            add_submenu_page('edit.php', __('Custom Order', 'wbso'), __('Custom Order', 'wbso'), WBSOFT_ORDER_PLUGIN_CAN_SORT, 'custom-order-'.$post_type, 'wbsoft_order_show_order_page');
        }else{
//            if (!is_post_type_hierarchical($post_type))
                add_submenu_page('edit.php?post_type='.$post_type, 'Custom Order', 'Custom Order', WBSOFT_ORDER_PLUGIN_CAN_SORT, 'custom-order-'.$post_type, 'wbsoft_order_show_order_page');
        }
    }
}

function wbsoft_order_get_post_type() 
{
    $post_type = null;
    
    if ( isset($_GET['page']) && strpos($_GET['page'], 'custom-order-') !== false ) 
    {
        $post_type = get_post_type_object(str_replace( 'custom-order-', '', $_GET['page'] ));        
    }
    
    return $post_type;
}

function wbsoft_order_show_order_page()
{
    $post_type = wbsoft_order_get_post_type();
?>
    <div class="wrap">
      <?php if(!$post_type): ?>
        Invalid Request!
      <?php else: ?>
        <div class="icon32" id="icon-edit"><br></div>
        <h2><?php echo $post_type->labels->singular_name . ' -  '. __('Custom Order', 'wbso') ?></h2>

        <div id="wbsoft-order-response"></div>
        
        <noscript>
            <div class="error message">
                <p><?php _e('Javascript should be enabled to use this plugin.', 'wbso') ?></p>
            </div>
        </noscript>
        
        <div id="wbsoft-all-round-order">
            <ul id="sortable">
                <?php 
                    wbsoft_order_get_post_list('hide_empty=0&title_li=&post_type='.$post_type->name); 
                ?>
            </ul>
            
            <div class="clear"></div>
        </div>
        
        <p class="submit">
            <a href="#" id="save-order" class="button-primary"><?php _e('Save Order', 'wbso' ) ?></a>
        </p>
        
        <script type="text/javascript">
            jQuery(document).ready(function() {
                jQuery("#sortable").sortable({
                    'tolerance':'intersect',
                    'cursor':'pointer',
                    'items':'li',
                    'placeholder':'placeholder',
                    'nested': 'ul'
                });
                
                jQuery("#sortable").disableSelection();
                jQuery("#save-order").bind( "click", function() {
                    jQuery.post( ajaxurl, { action:'update-custom-type-order', results: jQuery("#sortable").sortable("serialize") }, function() {
                        jQuery("#wbsoft-order-response").html('<div class="message updated fade"><p><?php _e('Order has been updated.', 'wbso') ?></p></div>');
                        jQuery("#wbsoft-order-response div").delay(5000).hide("slow");
                    });
                });
            });
        </script>        
      <?php endif; ?>        
    </div>
<?php
}

function wbsoft_order_get_post_list($args = '') 
{
    $defaults = array(
        'depth' => 0, 'show_date' => '',
        'date_format' => get_option('date_format'),
        'child_of' => 0, 'exclude' => '',
        'title_li' => __('Pages'), 'echo' => 1,
        'authors' => '', 'sort_column' => 'menu_order',
        'link_before' => '', 'link_after' => '', 'walker' => ''
    );

    $r = wp_parse_args( $args, $defaults );
    extract( $r, EXTR_SKIP );

    $output = '';

    $r['exclude'] = preg_replace('/[^0-9,]/', '', $r['exclude']);
    $exclude_array = ( $r['exclude'] ) ? explode(',', $r['exclude']) : array();
    $r['exclude'] = implode( ',', apply_filters('wp_list_pages_excludes', $exclude_array) );

    // Query pages.
    $r['hierarchical'] = 0;
    $args = array(
                'sort_column'   =>  'menu_order',
                'post_type'     =>  $post_type,
                'posts_per_page' => -1,
                'orderby'        => 'menu_order',
                'order'         => 'ASC'
    );
    if($post_type == 'attachment')
        $args['post_status'] = 'any';
        
    $the_query = new WP_Query($args);
    $pages = $the_query->posts;
    
    if ( !empty($pages) ) {
        if ( $r['title_li'] )
            $output .= '<li class="pagenav intersect">' . $r['title_li'] . '<ul>';
            
        $output .= wbsoft_order_walk_tree($pages, $r['depth'], $r);

        if ( $r['title_li'] )
            $output .= '</ul></li>';
    }

    $output = apply_filters('wp_list_pages', $output, $r);

    if ( $r['echo'] )
        echo $output;
    else
        return $output;
}

function wbsoft_order_walk_tree($pages, $depth, $r) 
{
    if ( empty($r['walker']) )
        $walker = new WbSoft_Order_Walker;
    else
        $walker = $r['walker'];

    $args = array($pages, $depth, $r);
    return call_user_func_array(array(&$walker, 'walk'), $args);
}

add_action('admin_init', 'wbsoft_order_enqueue_files');
/**
* Load Scripts and Stylesheets
* 
*/
function wbsoft_order_enqueue_files() 
{
    if(wbsoft_order_get_post_type()) 
    {
        wp_enqueue_style('wbsoft-order-stylesheet', WBSOFT_ORDER_PLUGIN_URL . '/css/wbsoft-order.css');
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-sortable');    
    }
}

add_action( 'wp_ajax_update-custom-type-order', 'wbsoft_order_save_sort_results' );
/**
* Save Customer Order
*/
function wbsoft_order_save_sort_results()
{
    global $wpdb;
                
    parse_str($_POST['results'], $data);
    
    if(is_array($data))
    {
        foreach($data as $key => $values) 
        {
            if($key == 'item'){
                foreach($values as $position => $id) 
                {
                    $wpdb->update($wpdb->posts, array('menu_order' => $position/*, 'post_parent' => 0*/), array('ID' => $id));
                } 
            }/*else{
                foreach($values as $position => $id) 
                {
                    $wpdb->update($wpdb->posts, array('menu_order' => $position, 'post_parent' => str_replace('item_', '', $key)), array('ID' => $id));
                }
            }*/
        }
    }
    
}
