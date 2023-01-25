<?php
namespace Glidewell\Vendors\OneTrust\Admin;
if(!defined('ABSPATH')) {exit;}

// Usings
use Glidewell\Vendors\OneTrust;
use \Gdwl_Admin_Tab;
use \Gdwl_Setting_Numeric_Int;
use \Gdwl_Setting_Toggle;
use \Gdwl_Setting_Text;

/**
 * Filter to add the settings for Integrations and OneTrust to the admin dashboard
 * @param $settings
 * @return array|mixed
 */
function populate_settings($settings) {

    if (is_array($settings)) {

        // Third-Party Integrations
        //$settings[] = new Gdwl_Setting_Text('', '', '', '', '', 'integration');
        $settings[] = new Gdwl_Setting_Text('Hotjar ID', OneTrust\OPTION_HOTJAR_ID, OneTrust\OPTION_HOTJAR_ID, '', 'The tracking ID for Hotjar', 'integration');
        $settings[] = new Gdwl_Setting_Text('Google Analytics Property ID', OneTrust\OPTION_GA_ID, OneTrust\OPTION_GA_ID, '', 'The ID of the Google Analytics property for this site', 'integration');
        $settings[] = new Gdwl_Setting_Text('Google Tag Manager Container ID', OneTrust\OPTION_GTAG_ID, OneTrust\OPTION_GTAG_ID, '', 'The ID of the container in Google Tag Manager for this site', 'integration');
        $settings[] = new Gdwl_Setting_Text('Facebook Pixel ID', OneTrust\OPTION_FB_PIXEL_ID, OneTrust\OPTION_FB_PIXEL_ID, '', 'The Facebook Pixel ID for this site', 'integration');

        // OneTrust Cookie Banner
        //$settings[] = new Gdwl_Setting_Text('', '', '', '', '', 'onetrust');
        $settings[] = new Gdwl_Setting_Toggle('Enabled', OneTrust\OPTION_ONETRUST_ENABLED, OneTrust\OPTION_ONETRUST_ENABLED, false, 'Determines if the OneTrust Cookie Banner integration is enabled and processing. Only toggle this on AFTER setting up the necessary tags', 'onetrust');
        $settings[] = new Gdwl_Setting_Text('Group ID - Strictly Necessary Cookies', OneTrust\OPTION_ONETRUST_GROUP_NECESSARY, OneTrust\OPTION_ONETRUST_GROUP_NECESSARY, 'C0001', 'Group ID for the "Strictly Necessary" cookies group.', 'onetrust');
        $settings[] = new Gdwl_Setting_Text('Group ID - Functional Cookies', OneTrust\OPTION_ONETRUST_GROUP_FUNCTIONAL, OneTrust\OPTION_ONETRUST_GROUP_FUNCTIONAL, 'C0003', 'Group ID for the "Functional" cookies group', 'onetrust');
        $settings[] = new Gdwl_Setting_Text('Group ID - Performance Cookies', OneTrust\OPTION_ONETRUST_GROUP_PERFORMANCE, OneTrust\OPTION_ONETRUST_GROUP_PERFORMANCE, 'C0002', 'Group ID for the "Performance" cookies group', 'onetrust');
        $settings[] = new Gdwl_Setting_Text('Group ID - Targeting Cookies', OneTrust\OPTION_ONETRUST_GROUP_TARGETING, OneTrust\OPTION_ONETRUST_GROUP_TARGETING, 'C0004', 'Group ID for the "Targeting" cookies group', 'onetrust');
        $settings[] = new Gdwl_Setting_Text('Group ID - Social Media Cookies', OneTrust\OPTION_ONETRUST_GROUP_SOCIAL, OneTrust\OPTION_ONETRUST_GROUP_SOCIAL, 'C0005', 'Group ID for the "Social Media" cookies group', 'onetrust');

    }
    return $settings;
}
add_filter('gdwl_init_settings', 'Glidewell\\Vendors\\OneTrust\\Admin\\populate_settings', 10, 1);

/**
 * Filter to add the OneTrust and Integration tabs to the Settings page
 * @param $tabs
 * @return array|mixed
 */
function populate_tabs($tabs) {

    if (is_array($tabs)) {

        if (!isset($tabs['integrations'])) {
            $tabs['integrations'] = new Gdwl_Admin_Tab('Integrations', 'gdwl-tab-integrations', 'Glidewell\\Vendors\\OneTrust\\Admin\\tab_integrations');
        }

        if (!isset($tabs['onetrust'])) {
            $tabs['onetrust'] = new Gdwl_Admin_Tab('OneTrust', 'gdwl-tab-onetrust', 'Glidewell\\Vendors\\OneTrust\\Admin\\tab_onetrust');
        }
    }
    return $tabs;
}
add_filter('gdwl_admin_page_settings_tabs', 'Glidewell\\Vendors\\OneTrust\\Admin\\populate_tabs', 10, 1);

/**
 * Displays the "Integrations" tab on the settings page
 */
function tab_integrations() {
    ?>
    <h2>Third-Party Integrations</h2>
    <p>This tab handles the third-party integrations for things like Hotjar nad Google Analytics. Enter the IDs below and the tracking tags will be automatically output. If a value is left blank, that tracking tag will not be output in the header.</p>
<?php
    gdwl()->settings()->output_controls('integration');
}

/**
 * Displays the "OneTrust" tab on the settings page
 */
function tab_onetrust() {
    ?>
    <h2>OneTrust Integration</h2>
    <p>There will be more here later...</p>
<?php
    gdwl()->settings()->output_controls('onetrust');
}