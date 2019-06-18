<?php
/*
Plugin Name: WP Memory
Plugin URI: http://wpmemory.com
Description: Check For High Memory Usage and include result in the Site Health Page.
Version: 1.0
Author: Bill Minozzi
Domain Path: /language
Author URI: http://billminozzi.com
Text Domain: wpmemory
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
// Make sure the file is not directly accessible.
if (!defined('ABSPATH')) {
    die('We\'re sorry, but you can not directly access this file.');
}
$wpmemory_memory['usage'] = function_exists('memory_get_usage') ? round(memory_get_usage() / 1024 / 1024, 0) : 0;
if (!is_numeric($wpmemory_memory['usage']) or $wpmemory_memory['usage'] < 1)  {
    $wpmemory_memory['usage'] = 1;
}
$wpmemory_mb = 'MB';
if (defined("MEMORY_LIMIT")) {
    $wpmemory_memory['wp_limit'] = trim($wpmemory_memory['wp_limit']);
    $wpmemory_memory['wp_limit'] = substr($wpmemory_memory['wp_limit'], 0, strlen($wpmemory_memory['wp_limit']) - 1);
} else {
    $wpmemory_memory['wp_limit'] = 40;
}
if(!is_numeric($wpmemory_memory['wp_limit']))
{
	$wpmemory_memory['wp_limit'] = 40;
}
$perc = $wpmemory_memory['usage'] / $wpmemory_memory['wp_limit'];
// $perc = 100;
 if ($perc > .7) {
	 $wpmemory_color = 'red';
 }
 else
 {
	$wpmemory_color = 'green'; 
 }
$wpmemory_usage_content = __('Current memory WordPress Limit', 'wpmemory') .': ' . $wpmemory_memory['wp_limit'] . $wpmemory_mb . '&nbsp;&nbsp;&nbsp;  |&nbsp;&nbsp;&nbsp;';
$wpmemory_usage_content .= '<span style="color:' . $wpmemory_color. ';">';
$wpmemory_usage_content .= 'Your usage now: ' . $wpmemory_memory['usage'] .
        'MB &nbsp;&nbsp;&nbsp;';
$wpmemory_usage_content .= '</span>';
$wpmemory_usage_content .= '<br />';
$wpmemory_usage_content .= '</strong>';
 if ($perc > .7) {
	 $wpmemory_label = 'Critical';
	 $wpmemory_status = 'critical';
	 $wpmemory_description = $wpmemory_usage_content . sprintf('<p>%s</p>', __('Run your site with High Memory Usage, can result in behaving slowly, or pages fail to load, you get random white screens of death or 500 internal server error. Basically, the more content, features and plugins you add to your site, the bigger your memory limit has to be. Increase the WP Memory Limit is a standard practice in WordPress. You can manually increase memory limit in WordPress by editing the wp-config.php file. You can find instructions in the official WordPress documentation (Increasing memory allocated to PHP). Just click the link below: ', 'wpmemory'));
     $wpmemory_actions = sprintf('<p><a href="%s">%s</a></p>', 'https://codex.wordpress.org/Editing_wp-config.php', __('WordPress Help Page', 'wpmemory'));
 }
 else
 {
    $wpmemory_label = 'Performance';
   	$wpmemory_status = 'good';
   	$wpmemory_description = __('Pass', 'wpmemory').'.';
    $wpmemory_actions =	 '';
 }
function wpmemory_add_memory_test($tests)
{
    $tests['direct']['wpmemory_plugin'] = array(
        'label' => __('WP Memory Test', 'wpmemory'),
        'test' => 'wpmemory_memory_test'
    );
    return $tests;
}
add_filter('site_status_tests', 'wpmemory_add_memory_test'); 
 
function wpmemory_memory_test()
{
    global $wpmemory_usage_content, $wpmemory_color, $wpmemory_label, $wpmemory_status, $wpmemory_description, $wpmemory_actions;
    $result = array(
        'badge' => array(
            'label' => $wpmemory_label,
            'color' => $wpmemory_color,
            // color: Applies a CSS class with this value to the badge. Core styles support blue, green, red, orange, purple and gray.
        ),
        'test' => 'wpmemory_test',
        // status: Section the result should be displayed in. Possible values are good, recommended, or critical.
        'status' => $wpmemory_status,
        'label' => __('Memory Usage', 'wpmemory'),
        'description' => $wpmemory_description.'  '.$wpmemory_usage_content,
        'actions' => $wpmemory_actions
    );
    return $result;
}
function wpmemory_add_debug_info($debug_info)
{   Global $wpmemory_usage_content;
    $debug_info['wpmemory'] = array(
        'label' => __('Memory Usage', 'wpmemory'),
        'fields' => array(
            'memory' => array(
                'label' => __('Memory Usage information', 'wpmemory'),
                'value' => strip_tags($wpmemory_usage_content),
                'private' => true
            )
        )
    );
    return $debug_info;
}
add_filter('debug_information', 'wpmemory_add_debug_info');
/**
 * check on plugin activation
 * @return void
 */
function wpmemory_activation()
{
    global $wp_version;
    if (version_compare(PHP_VERSION, '5.3', '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        load_plugin_textdomain('wpmemory', false, dirname(plugin_basename(__FILE__)) . '/language/');
        $plugin_data    = get_plugin_data(__FILE__);
        $plugin_version = $plugin_data['Version'];
        $plugin_name    = $plugin_data['Name'];
        wp_die('<h1>' . __('Could not activate plugin: PHP version error', 'wpmemory') . '</h1><h2>PLUGIN: <i>' . $plugin_name . ' ' . $plugin_version . '</i></h2><p><strong>' . __('You are using PHP version', 'wpmemory') . ' ' . PHP_VERSION . '</strong>. ' . __('This plugin has been tested with PHP versions 5.3 and greater.', 'wpmemory') . '</p><p>' . __('WordPress itself <a href="https://wordpress.org/about/requirements/" target="_blank">recommends using PHP version 7 or greater</a>. Please upgrade your PHP version or contact your Server administrator.', 'wpmemory') . '</p>', __('Could not activate plugin: PHP version error', 'wpmemory'), array(
            'back_link' => true
        ));
    }
    if (version_compare($wp_version, '5.2') < 0) {
        deactivate_plugins(plugin_basename(__FILE__));
        load_plugin_textdomain('wpmemory', false, dirname(plugin_basename(__FILE__)) . '/language/');
        $plugin_data    = get_plugin_data(__FILE__);
        $plugin_version = $plugin_data['Version'];
        $plugin_name    = $plugin_data['Name'];
        wp_die('<h1>' . __('Could not activate plugin: WordPress need be 5.2 or bigger.', 'wpmemory') . '</h1><h2>PLUGIN: <i>' . $plugin_name . ' ' . $plugin_version . '</i></h2><p><strong>' . __('Please, Update WordPress to Version 5.2 or bigger to use this plugin.', 'wpmemory') . '</strong>', array(
            'back_link' => true
        ));
    }
}
register_activation_hook(__FILE__, 'wpmemory_activation');
