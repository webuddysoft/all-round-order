<?php
/**
* Get Plugin Options
* 
*/
function wbsoft_order_plugin_options()
{
    $options = get_option('wbso_order_options');
    if(!$options)
        $options = array(
                            'auto_sort' => 1,
                            'can_sort' => 'Administrator'
                        );
    
    return $options;
}

/*function wbsoft_get_user_roles()
{
    $roles = array(
        'Administrator' => 'install_themes',
        'Editor' => 'edit_pages',
        'Author' => 'edit_published_posts',
        'Contributor' => 'edit_posts',
        'Subscriber' => 'activate_plugins',
    );
}*/