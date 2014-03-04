<?php
/***
* Plugin Settings Page
*/

add_action('admin_menu', 'wbsoft_plugin_menu');

function wbsoft_plugin_menu() 
{    
    add_options_page('All Round Order', 'All Round Order', 'manage_options', 'wbso-options', 'wbsoft_order_plugin_settings');
}


/**
* There is not Settings. 
* This page just showes how to use this plugin
* 
*/
function wbsoft_order_plugin_settings()
{
    global $wp_roles;
    
    if(isset($_POST['wbso-order-options-save']))
    {
        //Save Options
        $options = array(
            'auto_sort' => $_POST['wbso_auto_sort'] ? 1: 0,
            'can_sort' => 'manage_options'
//            'can_sort' => $_POST['wbso_can_sort']
        );        
        update_option('wbso_order_options', $options);
    }
    
    //Use Array for Extensibility
    $options = wbsoft_order_plugin_options();
    $user_roles = get_editable_roles();
    
?>
    <div class="wrap"> 
        <div id="icon-settings" class="icon32"></div>
        <h2><?php _e('Settings', 'wbso') ?></h2>                               
        <form method="post" name="adminform">
            <table cellpadding="10">
                <tr>
                    <th valign="top" align="right">Auto Sort</th>
                    <td>
                        <input type="checkbox" name="wbso_auto_sort" id="wbso_auto_sort" value="1" <?php echo $options['auto_sort'] ? 'checked="checked"' : '' ?> />
                        If this is checked, the plugin will update the wp_queries automatically and the results will be sorted by the new order. <br />
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;If you want to use the new order for some special pages, you can uncheck this option and include 'menu_order' as orderby option into wp_query args.<br />
                        
                        
<pre>
    Example:
    
    $args = array(
      ... ...
      'orderby'   => 'menu_order',
      'order'     => 'ASC'
      ... ...
    );
    
    $query = new WP_Query($args);
    
    or
    
    $posts = get_posts($args);
    
</pre>
                    </td>
                </tr>
                <?php
                //For Now We disabled it. We will add this feature in the new version
                /*<tr>
                    <th><label>Who can use this plugin?</label></th>
                    <td>
                        <select name="wbso_can_sort" id="wbso_can_sort">
                            <?php
                                foreach($user_roles as $rid=>$role)
                                    echo '<option value="' . $rid . '" ' . ($rid == $options['can_sort'] ? 'selected="selected"' : '') . '>' . $role['name'] . '</option>';
                            ?>
                        </select>
                    </td>
                </tr>*/
                ?>
            </table>
            <input type="submit" name="wbso-order-options-save" class="button button-primary" value="Save Settings" />
        </form>
    </div>          
    <?php 
       
}
