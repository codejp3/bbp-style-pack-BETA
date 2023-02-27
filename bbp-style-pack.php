<?php

/*
Plugin Name: bbp style pack
Plugin URI: http://www.rewweb.co.uk/bbp-style-pack/
Description: This plugin adds styling and features to bbPress.
Version: 5.3.6.1-BETA
Author: Robin Wilson
Text Domain: bbp-style-pack
Domain Path: /languages
Author URI: http://www.rewweb.co.uk
License: GPL2
*/
/*  Copyright 2016-2023  Robin Wilson  (email : wilsonrobine@btinternet.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/


/*******************************************
* global variables
*******************************************/

// load the plugin options
$bsp_style_settings_f = get_option( 'bsp_style_settings_f' );
$bsp_templates = get_option( 'bsp_templates' );
$bsp_forum_display = get_option( 'bsp_forum_display' );
$bsp_forum_order = get_option( 'bsp_forum_order' );
$bsp_style_settings_freshness = get_option( 'bsp_style_settings_freshness' );
$bsp_breadcrumb = get_option( 'bsp_breadcrumb' );
$bsp_style_settings_buttons = get_option( 'bsp_style_settings_buttons' );
$bsp_login = get_option( 'bsp_login' );
$bsp_login_fail = get_option( 'bsp_login_fail' );
$bsp_roles = get_option( 'bsp_roles' );
$bsp_style_settings_email = get_option( 'bsp_style_settings_email' );
$bsp_style_settings_sub_management = get_option( 'bsp_style_settings_sub_management' );
$bsp_topic_order = get_option( 'bsp_topic_order' );
$bsp_style_settings_ti = get_option( 'bsp_style_settings_ti' );
$bsp_style_settings_topic_preview = get_option( 'bsp_style_settings_topic_preview' );
$bsp_style_settings_t = get_option( 'bsp_style_settings_t' );
$bsp_settings_topic_count = get_option ('bsp_settings_topic_count');
$bsp_style_settings_form = get_option( 'bsp_style_settings_form' );
$bsp_profile = get_option( 'bsp_profile' );
$bsp_style_settings_search = get_option( 'bsp_style_settings_search' );
$bsp_style_settings_unread = get_option( 'bsp_style_settings_unread' );
$bsp_style_settings_quote = get_option( 'bsp_style_settings_quote' );
$bsp_style_settings_modtools = get_option( 'bsp_style_settings_modtools' );
$bsp_style_settings_la = get_option( 'bsp_style_settings_la' );
$bsp_css_location = get_option( 'bsp_css_location' );
$bsp_style_settings_translation = get_option( 'bsp_style_settings_translation' );
$bsp_settings_admin  = get_option ('bsp_settings_admin') ;
$bsp_style_settings_bugs = get_option( 'bsp_style_settings_bugs' );
$bsp_css = get_option( 'bsp_css' );
$bsp_style_settings_theme_support = get_option( 'bsp_style_settings_theme_support' );
$bsp_plugin_settings = get_option( 'bsp_plugin_settings' );

$bsp_bbpress_version = get_option('bsp_bbpress_version', '2.5') ;  //set to 2.5 as default if option not set
$bsp_bbpress_version = substr($bsp_bbpress_version, 0, 3) ;  //done on 2 lines to allow for earlier php versions

if(!defined('BSP_PLUGIN_DIR'))
	define('BSP_PLUGIN_DIR', dirname(__FILE__));

function bbp_style_pack_init() {
        load_plugin_textdomain('bbp-style-pack', false, basename( dirname( __FILE__ ) ) . '/languages' );
        //load the plugin stuff
        bsp_load_plugin() ;
        //save the bbpress version
        if( class_exists( 'bbpress' ) )
                update_option ('bsp_bbpress_version' , bbp_get_version()) ;
}
add_action('plugins_loaded', 'bbp_style_pack_init');



//  TEMPLATES - fix for style pack
/* It is annoying, but bbpress does not store it's version in the db, it holds it as a global, but we need to know which version before bbpress loads, so we can call 
the templates if needed.So this is a cheat to make it work.  
Once plugins are loaded, we store the bbpress version in wp_options 'bsp_bbpress_version'.  
Since the style pack plugin loads on each page call, we will have been through this loop before forums are shown, or at absolute worst before they are shown 
for the second time since style pack was enabled !  So in reality before they are shown.

so in bbp_style_pack_init() above, we save the bbpress version as by then bbpress will be loaded.

Template Loading

The method is different (I think) between 2.5.12 and 2.6, it certainly seems to affect the load order, so we find out which version we are on, and allocate dependant on that.
We also allow an override, so admins can try different numbers
*/


//  TEMPLATES - done now as needed when bbpress loads
//register the new templates location
//this just does files in the templates/templates1 directory - set up to allow other variations and only take live those which you need
if (!empty ($bsp_templates['template'] ) && ($bsp_templates['template'] == 1)) {
        add_action( 'bbp_register_theme_packages', 'bsp_register_plugin_template1' );
}

//add in the forum search if set
if (!empty ($bsp_style_settings_search['SearchingActivate'] )) {
	add_action( 'bbp_register_theme_packages', 'bsp_register_plugin_search_template' );
}

//add in the topic/reply_form if we need to
if (!empty ($bsp_style_settings_form['Remove_Edit_LogsActivate'] ) || !empty ($bsp_style_settings_form['Remove_Edit_ReasonActivate'] )  || !empty ($bsp_style_settings_form ['htmlActivate'])  ||  !empty ($bsp_style_settings_form ['nologinActivate']) || !empty ($bsp_style_settings_form['topic_tag_list'])) { 
	add_action( 'bbp_register_theme_packages', 'bsp_register_plugin_form_topicandreply_template' );
}

//add in the mod tools pending shortcode if modtools activated
if( !class_exists( 'bbPressModToolsPlugin') && !empty($bsp_style_settings_modtools['modtools_activate']) ) {
	add_action( 'bbp_register_theme_packages', 'bsp_register_modtools_template' );
}

//add in the feedback no topics is this is to be blank
if (!empty ($bsp_style_settings_ti['empty_forumActivate'] ) ) {
        add_action( 'bbp_register_theme_packages', 'bsp_register_plugin_form_no_feedback_template' );
}

//get the template paths
function bsp_get_template1_path() {
	return BSP_PLUGIN_DIR . '/templates/templates1';
}

function bsp_get_search_template_path() {
	return BSP_PLUGIN_DIR . '/templates/searchform';
}

function bsp_get_form_topicandreply_template_path5() {
	return BSP_PLUGIN_DIR . '/templates/topicandreplyform5';
}

function bsp_get_form_topicandreply_template_path6() {
	return BSP_PLUGIN_DIR . '/templates/topicandreplyform6';
}

function bsp_get_form_no_feedback_template_path() {
	return BSP_PLUGIN_DIR . '/templates/feedbacknotopics';
}

function bsp_get_modtools_template_path() {
	return BSP_PLUGIN_DIR . '/templates/modtools';
}





//register the templates

/* This is the bit that determines the order they load
in 2.5 we have
		bbp_register_template_stack( 'get_template_directory',   12 );
		bbp_register_template_stack( 'bbp_get_theme_compat_dir', 14 );
		
so we load at 12 to get the templates to work, so not sure which loads first - theme or bsp - one to test when I get a moment

in 2.6 we have 
		bbp_register_template_stack( 'get_template_directory',   8 );
		bbp_register_template_stack( array( $bbp->theme_compat->theme, 'get_dir' ) );
		
which is different, and seems to cause issues if left at 12 as other templates have loaded before, so we alter to 6 as default
it actualy looks like something is using a default of 10, as setting to that works, but not 11. 
*/

//set default priorities for versions, and then allow for custom
$version = get_option('bsp_bbpress_version', '2.5') ;  //set to 2.5 as default if option not set
if (substr($version, 0, 3) == '2.5') $priority = 12; 
elseif (substr($version, 0, 3) == '2.6') $priority = 6 ;
//allow for case where neither is set
else $priority = 12 ;


//then allow custom setting
if (!empty($bsp_templates['template_priority'])  && is_numeric ($bsp_templates['template_priority']) ) $priority = $bsp_templates['template_priority'] ;

function bsp_register_plugin_template1() {
	global $priority ;
	bbp_register_template_stack( 'bsp_get_template1_path',  $priority);
}

function bsp_register_plugin_search_template() {
	global $priority ;
	bbp_register_template_stack( 'bsp_get_search_template_path', $priority );
}

function bsp_register_modtools_template() {
	global $priority ;
	bbp_register_template_stack( 'bsp_get_modtools_template_path', $priority );
}

function bsp_register_plugin_form_topicandreply_template() {
	global $priority ;
	//if version 2.5...
	if ($priority == 12) {
	bbp_register_template_stack( 'bsp_get_form_topicandreply_template_path5', $priority);
	}
	//if version 2.6...
	else {
	bbp_register_template_stack( 'bsp_get_form_topicandreply_template_path6', $priority);
	}
}

function bsp_register_plugin_form_no_feedback_template() {
	global $priority ;
	bbp_register_template_stack( 'bsp_get_form_no_feedback_template_path', $priority);
}


	
//add our version of wp_authenticate (pluggable wordpress function) if failed login tab activated - done now to ensure it loads
if( ! function_exists('wp_authenticate') && !empty($bsp_login_fail['activate_failed_login']) ) { 
        function wp_authenticate( $username, $password ) {
                $username = sanitize_user( $username );
                $password = trim( $password );

                /**
                 * Filters whether a set of user login credentials are valid.
                 *
                 * A WP_User object is returned if the credentials authenticate a user.
                 * WP_Error or null otherwise.
                 *
                 * @since 2.8.0
                 * @since 4.5.0 `$username` now accepts an email address.
                 *
                 * @param null|WP_User|WP_Error $user     WP_User if the user is authenticated.
                 *                                        WP_Error or null otherwise.
                 * @param string                $username Username or email address.
                 * @param string                $password User password
                 */
                $user = apply_filters( 'authenticate', null, $username, $password );

                if ( null == $user ) {
                        // TODO: What should the error message be? (Or would these even happen?)
                        // Only needed if all authentication handlers fail to return anything.
                        $user = new WP_Error( 'authentication_failed', __( '<strong>Error</strong>: Invalid username, email address or incorrect password.' ) );
                }
                //***function amended to take out this line and add blank array to ensure we pass back to bbpress on any error
                //$ignore_codes = array( 'empty_username', 'empty_password' );
                $ignore_codes = array () ;
                if ( is_wp_error( $user ) && ! in_array( $user->get_error_code(), $ignore_codes ) ) {
                        $error = $user;

                        /**
                         * Fires after a user login has failed.
                         *
                         * @since 2.5.0
                         * @since 4.5.0 The value of `$username` can now be an email address.
                         * @since 5.4.0 The `$error` parameter was added.
                         *
                         * @param string   $username Username or email address.
                         * @param WP_Error $error    A WP_Error object with the authentication failure details.
                         */
                        do_action( 'wp_login_failed', $username, $error );
                }

                return $user;
        }
}

/*******************************************
* file includes 
*******************************************/

//only fires after all plugins loaded to ensure bbpress is loaded before we fire bbpress functions and filters
function bsp_load_plugin() {
	
	if( class_exists( 'bbpress' ) ) {
            
                // CHECK IF BLOCK THEME
                global $check_block_theme ;
                // get current theme dir
                $theme_dir = get_template_directory();
                //$stylesheet_dir = get_stylesheet_directory(); // possibly need to integrate to catch parent/child theme differences
                //Detect if FSE (what WordPress calls block themes) theme or traditional - FSE Block themes require a theme.json file.
                if ( file_exists( $theme_dir . '/theme.json' ) ) {
                        //wp_die( 'fse theme' );
                        $check_block_theme = 1 ;
                        include(BSP_PLUGIN_DIR . '/includes/functions_theme_support.php');
                }
                
                // front-end and admin files
                global $bsp_style_settings_sub_management ;
                if (!function_exists( 'forums_toolkit_page') && !empty($bsp_style_settings_sub_management['subscriptions_management_activate']))
                        include(BSP_PLUGIN_DIR . '/includes/subscriptions_management.php');

                global $bsp_style_settings_unread ;
                //only load functions_unread if activated
                if (!empty($bsp_style_settings_unread['unread_activate'])) 
                        include(BSP_PLUGIN_DIR . '/includes/functions_unread.php');

                //only load functions_quote if activated
                global $bsp_style_settings_quote ;
                if (!empty($bsp_style_settings_quote['quote_activate'])) 
                        include(BSP_PLUGIN_DIR . '/includes/functions_quote.php');

                //load moderation tools if activated
                //don't load if mod tools plugin already loaded
                global $bsp_style_settings_modtools ;
                if( !class_exists( 'bbPressModToolsPlugin') && !empty($bsp_style_settings_modtools['modtools_activate']) )  {
                        //load moderation tools	
                        require_once( BSP_PLUGIN_DIR . '/modtools/bbpress-modtools.php' );
                        require_once( BSP_PLUGIN_DIR . '/modtools/settings.php' );
                        require_once( BSP_PLUGIN_DIR . '/modtools/admin.php' );
                        require_once( BSP_PLUGIN_DIR . '/modtools/bbpress.php' );
                        require_once( BSP_PLUGIN_DIR . '/modtools/moderation.php' );
                        require_once( BSP_PLUGIN_DIR . '/modtools/report.php' );
                        require_once( BSP_PLUGIN_DIR . '/modtools/users.php' );
                        require_once( BSP_PLUGIN_DIR . '/modtools/scripts.php' );
                        require_once( BSP_PLUGIN_DIR . '/modtools/notifications.php' );
                        //add shortcode function
                        include(BSP_PLUGIN_DIR . '/includes/functions_modtools.php');
                }
                
                include(BSP_PLUGIN_DIR . '/includes/functions.php');
                include(BSP_PLUGIN_DIR . '/includes/functions_email.php');
                include(BSP_PLUGIN_DIR . '/includes/forum_image_metabox.php');
                include(BSP_PLUGIN_DIR . '/includes/generate_css.php');
                include(BSP_PLUGIN_DIR . '/includes/widgets.php');
            
                // admin-only files
                if ( is_admin() ) {
                    
                        // files for everywhere in the admin panel, necessary for initial admin panel loading
                        include(BSP_PLUGIN_DIR . '/includes/functions_admin.php'); // common admin functions
                        //include(BSP_PLUGIN_DIR . '/includes/settings_widgets.php'); // widget settings and widget page settings
                        include(BSP_PLUGIN_DIR . '/includes/settings.php'); // register the admin settings page
                        //include(BSP_PLUGIN_DIR . '/includes/settings.php'); // register the admin settings page
                        // include(BSP_PLUGIN_DIR . '/includes/settings_export.php');
                        
                    
                        // style pack-only files
                        global $pagenow;
                        if ( ( $pagenow == 'options-general.php' ) && ($_GET['page'] == 'bbp-style-pack') ) {
                                include(BSP_PLUGIN_DIR . '/includes/settings_assets.php'); // bsp admin JS/CSS enqueue 
                                include(BSP_PLUGIN_DIR . '/includes/defined_option_groups.php');
                                include(BSP_PLUGIN_DIR . '/includes/defined_fields.php');
                                include(BSP_PLUGIN_DIR . '/includes/defined_tabs.php');
                                //include(BSP_PLUGIN_DIR . '/includes/settings_tab_about.php');
                                //include(BSP_PLUGIN_DIR . '/includes/settings_tab_changelog.php');
                                //include(BSP_PLUGIN_DIR . '/includes/settings_tab_debug_info.php');
                                //include(BSP_PLUGIN_DIR . '/includes/settings_tab_donate.php');
                                //include(BSP_PLUGIN_DIR . '/includes/settings_tab_help.php');
                                //include(BSP_PLUGIN_DIR . '/includes/settings_tab_helpful_plugins.php');
                                //include(BSP_PLUGIN_DIR . '/includes/settings_tab_new.php');
                                // bulk include all files in the tabs directory
                                foreach ( glob( BSP_PLUGIN_DIR . "/includes/tabs/*.php" ) as $filename ) {
                                    include $filename;
                                }
                                include(BSP_PLUGIN_DIR . '/includes/settings_tab.php');
                                
                                
                                //include(BSP_PLUGIN_DIR . '/includes/settings_forums_index.php');
                                //include(BSP_PLUGIN_DIR . '/includes/settings_topics_index.php');
                                //include(BSP_PLUGIN_DIR . '/includes/settings_topic_reply_display.php');
                                //include(BSP_PLUGIN_DIR . '/includes/settings_forum_display.php');
                                //include(BSP_PLUGIN_DIR . '/includes/settings_forum_roles.php');
                                //include(BSP_PLUGIN_DIR . '/includes/settings_custom_css.php');
                                //include(BSP_PLUGIN_DIR . '/includes/settings_topic_order.php');
                                //include(BSP_PLUGIN_DIR . '/includes/settings_forum_order.php');
                                //include(BSP_PLUGIN_DIR . '/includes/settings_freshness_display.php');
                                //include(BSP_PLUGIN_DIR . '/includes/settings_topic_reply_form.php');
                                //include(BSP_PLUGIN_DIR . '/includes/settings_css_location.php');
                                //include(BSP_PLUGIN_DIR . '/includes/settings_login.php');
                                //include(BSP_PLUGIN_DIR . '/includes/settings_login_fail.php');
                                //include(BSP_PLUGIN_DIR . '/includes/settings_search.php');
                                //include(BSP_PLUGIN_DIR . '/includes/settings_forum_templates.php');
                                //include(BSP_PLUGIN_DIR . '/includes/settings_breadcrumbs.php');
                                //include(BSP_PLUGIN_DIR . '/includes/settings_buttons.php');
                                //include(BSP_PLUGIN_DIR . '/includes/settings_profile.php');
                                //include(BSP_PLUGIN_DIR . '/includes/settings_shortcodes.php');
                                //include(BSP_PLUGIN_DIR . '/includes/settings_latest_activity_widget_styling.php');

                                //include(BSP_PLUGIN_DIR . '/includes/settings_reset.php');
                                //include(BSP_PLUGIN_DIR . '/includes/not_working.php');
                                //include(BSP_PLUGIN_DIR . '/includes/settings_unread.php');
                                
                                //include(BSP_PLUGIN_DIR . '/includes/settings_import.php');
                                //include(BSP_PLUGIN_DIR . '/includes/settings_email.php');
                                //include(BSP_PLUGIN_DIR . '/includes/settings_quote.php');
                                //include(BSP_PLUGIN_DIR . '/includes/settings_moderation.php');
                                //include(BSP_PLUGIN_DIR . '/includes/settings_theme_support.php');
                                //include(BSP_PLUGIN_DIR . '/includes/settings_translation.php');
                                //include(BSP_PLUGIN_DIR . '/includes/settings_bugs.php');
                                //include(BSP_PLUGIN_DIR . '/includes/settings_subscriptions_management.php');
                                //include(BSP_PLUGIN_DIR . '/includes/settings_topic_count.php');
                                //include(BSP_PLUGIN_DIR . '/includes/settings_admin.php');
                                //include(BSP_PLUGIN_DIR . '/includes/settings_topic_preview.php');
                                //include(BSP_PLUGIN_DIR . '/includes/help.php');
                                //include(BSP_PLUGIN_DIR . '/includes/plugins.php');
                                //include(BSP_PLUGIN_DIR . '/includes/logo.php');
                                
                        }
                        
                        // files for everywhere in the admin panel, load after all other necessary admin files are loaded
                        //include(BSP_PLUGIN_DIR . '/includes/functions_admin.php'); // common admin functions
                        // include(BSP_PLUGIN_DIR . '/includes/settings_export.php');
                        
                }
                
                // frontend-only files
                if ( ! is_admin() ) {
                        include(BSP_PLUGIN_DIR . '/includes/buddypress.php');
                        include(BSP_PLUGIN_DIR . '/includes/functions_bugs.php');
                        include(BSP_PLUGIN_DIR . '/includes/functions_topic_count.php');
                        include(BSP_PLUGIN_DIR . '/includes/shortcodes.php');
                       
                }


                add_filter( 'plugin_action_links', 'bsp_modify_plugin_action_links', 10, 2 );

                function bsp_modify_plugin_action_links( $links, $file ) {

                        // Return normal links if not bbPress style 
                        if ( 'bbp-style-pack/bbp-style-pack.php' !== $file ) {
                                return $links;
                        }

                        // New links to merge into existing links
                        $new_links = array();

                        // Settings page and what's new page
                        if ( current_user_can( 'manage_options' ) ) {
                                $new_links['settings'] = '<a href="' . esc_url( add_query_arg( array( 'page' => 'bbp-style-pack'   ), admin_url( 'options-general.php' ) ) ) . '">' . esc_html__( 'Settings', 'bbp-style-pack' ) . '</a>';
                                $new_links['about']    = '<a href="' . esc_url( add_query_arg( array( 'page' => 'bbp-style-pack', 'tab' => 'new' ), admin_url( 'options-general.php' ) ) ) . '">' . esc_html__( 'What\'s New?',    'bbp-style-pack' ) . '</a>';
                        }

                        // Add a few links to the existing links array
                        return array_merge( $links, $new_links );
                }


                //amend for searching activate being moved from forum index styling to search styling tab
                //Get entire array
                $options_f= get_option('bsp_style_settings_f');
                if (!empty($options_f["SearchingActivate"])) {
                        //update bsp_style_settings_search
                        $options = get_option('bsp_style_settings_search');
                        $options['SearchingActivate'] = '1';
                        $options['SearchingSearching'] = $options_f["SearchingSearching"];
                        $options['SearchingSpinner'] = $options_f["SearchingSpinner"];
                        //Update entire array
                        update_option('bsp_style_settings_search', $options);
                        //update bsp_style_settings_f
                        unset ($options_f ['SearchingActivate']) ;
                        unset ($options_f['SearchingSearching']);
                        unset ($options_f ['SearchingSpinner']) ;
                        //Update entire array
                        update_option('bsp_style_settings_f', $options_f);

                }

                //update for bsp_login menus
                if (empty ($bsp_login['update448'])) {
                        //run the update once
                        $options = get_option('bsp_login');
                        $options['update448'] = '1' ;
                        $menu_locations = get_nav_menu_locations();
                        $menus = get_terms('nav_menu');
                        //login
                        if (!empty($bsp_login['add_login'])) {
                                foreach($menus as $menu){
                                        if(!empty($bsp_login['only_primary'])) {
                                                if ( ! empty( $menu_locations ) && $menu_locations['primary'] == $menu->term_id ) {
                                                $name= 'login_'.$menu->name ;
                                                $options[$name] = '1' ;
                                                unset ($options['only_primary']) ;
                                                }
                                        }
                                        else {
                                                $name= 'login_'.$menu->name ;
                                                $options[$name] = '1' ;
                                        }
                                }
                        }

                        //register
                        if (!empty($bsp_login['register'])) {
                                foreach($menus as $menu){
                                        if(!empty($bsp_login['register_only_primary'])) {
                                                if ( ! empty( $menu_locations ) && $menu_locations['primary'] == $menu->term_id ) {
                                                $name= 'register_'.$menu->name ;
                                                $options[$name] = '1' ;
                                                unset ($options['register_only_primary']) ;
                                                }
                                        }
                                        else {
                                                $name= 'register_'.$menu->name ;
                                                $options[$name] = '1' ;
                                        }
                                }
                        }

                        //profile
                        if (!empty($bsp_login['edit_profile'])) {
                                foreach($menus as $menu){
                                        if(!empty($bsp_login['profile_only_primary'])) {
                                                if ( ! empty( $menu_locations ) && $menu_locations['primary'] == $menu->term_id ) {
                                                $name= 'profile_'.$menu->name ;
                                                $options[$name] = '1' ;
                                                unset ($options['profile_only_primary']) ;
                                                }
                                        }
                                        else {
                                                $name= 'profile_'.$menu->name ;
                                                $options[$name] = '1' ;
                                        }
                                }
                        }
                update_option('bsp_login', $options);
                }

                //amend settings topic/reply form to allow for different topic/reply text
                $options = get_option('bsp_style_settings_form');
                if (empty ($options['update418'])) {
                        if (!empty($options['topic_rules_text']) && !empty($options['topic_posting_rulesactivate_for_replies']) ) {
                                $options['reply_rules_text'] = $options['topic_rules_text'] ;
                                //and set it to stop running again
                                $options['update418'] = '1' ;
                                update_option('bsp_style_settings_form', $options);
                        }
                }

		/*
		 * Handle upgrade actions
		 */
		if ( ! function_exists( 'get_plugin_data' ) ) {
                        require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
                }
			
		$new_version = get_plugin_data( __FILE__, false, false )['Version'];
                
		if ( ! defined( 'BSP_VERSION_KEY' ) )
                        define( 'BSP_VERSION_KEY', 'bsp_version' );

		if ( ! defined( 'BSP_VERSION_NUM' ) )
                        define( 'BSP_VERSION_NUM', $new_version );
					
		$curr_version = get_option( BSP_VERSION_KEY, false );

		if ($new_version != $curr_version)  {
			 
			 // do the activation actions
			bsp_plugin_update( bsp_is_network_activated() );
		}
		
        } // end of if bbpress class exists - main plugin loading
} //end of bsp_load_plugin


/*
 * Handle update actions
 */
function bsp_plugin_update( $network_wide ) { 
    
        if ( ! function_exists( 'get_plugin_data' ) ) {
                require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
        }

        $new_version = get_plugin_data( __FILE__, false, false )['Version'];
    
        if ( ! defined( 'BSP_VERSION_KEY' ) )
                define( 'BSP_VERSION_KEY', 'bsp_version' );

        if ( ! defined( 'BSP_VERSION_NUM' ) )
                define( 'BSP_VERSION_NUM', $new_version );

        if ( is_multisite() ) {
        /* multisite install */

                $site_ids = get_sites( array( 'fields' => 'ids' ) );
                
                $bsp_name = plugin_basename( __FILE__ );
                
                foreach( $site_ids as $site_id ) {
                        switch_to_blog( $site_id );
                        // network-activated, or active for current site?
                        if ( $network_wide || in_array( $bsp_name, apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

                                /* 
                                * Regenerate CSS/JS files
                                * Pro-actively regenerate files to replace CSS/JS files removed during the upgrade process 
                                */
                                require_once( plugin_dir_path( __FILE__ ) . 'includes/generate_css.php' );
                                generate_style_css();
                                generate_quote_style_css();
                                generate_delete_js();
                                wp_cache_flush();

                                //and update version whether new installation or update
                                update_option( BSP_VERSION_KEY, BSP_VERSION_NUM );
                        }
                        restore_current_blog();
                }

        } else {
        /* single site install */

                /* 
                * Regenerate CSS/JS files
                * Pro-actively regenerate files to replace CSS/JS files removed during the upgrade process 
                */
                require_once( plugin_dir_path( __FILE__ ) . 'includes/generate_css.php' );
                generate_style_css();
                generate_quote_style_css();
                generate_delete_js();
                wp_cache_flush();

                //and update version whether new installation or update
                update_option( BSP_VERSION_KEY, BSP_VERSION_NUM );
				 
        } // end plugin update
}


/*
 * Convert Values
 * Some setting values have changed
 * Let's convert any old values to new values
 */
function bsp_convert_values( $option_group = false, $field_setting = false ) {
    
        $defined_fields = bsp_defined_fields();
                
        // if we were supplied an option group, let's setup for just that option group
        //if ( $opiton_group ) {
            
                // if we were supplied a specific field setting for the option group, let's setup for just that field setting
            
        // else, bulk conversion for all settings that need it    
       // } else {
                foreach ( $defined_field as $option_name => $option_values ) {
                        foreach ( $option_values as $field ) {
                             if ( ! empty( $field['convert_values'] ) ) {
                                    $needs_update = false;
                                    // get the current global option value
                                    foreach ( $field['convert_values'] as $convert_val ) {
                                            //if ( current global value == $convert_val['old']) {
                                                    // update current global value in-place
                                                    //$needs_update = true;
                                            //}
                                    }
                                    if ( $needs_update ) {
                                            // update_option( 'global val name', $values );
                                    }
                             }
                        }
                }
       // }
        
        
    
}