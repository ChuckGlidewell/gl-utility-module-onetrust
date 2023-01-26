<?php
namespace Glidewell\Vendors\OneTrust;
if(!defined('ABSPATH')) {exit;}

//Usings


//--------------------------------------------
// Constants
//--------------------------------------------
//<editor-fold desc="Constants">
const OPTION_HOTJAR_ID = 'tag_id_hotjar';
const OPTION_GA_ID = 'tag_id_google_analytics';
const OPTION_GTAG_ID = 'tag_id_google_tag_manager';
const OPTION_FB_PIXEL_ID = 'tag_id_facebook_pixel';


const OPTION_ONETRUST_ENABLED = 'onetrust_enabled';
const OPTION_ONETRUST_DOMAIN_ID = 'onetrust_domain_id';
const OPTION_ONETRUST_AUTO_BLOCK = 'onetrust_auto_blocking';
const OPTION_ONETRUST_GROUP_NECESSARY = 'onetrust_group_id_necessary';
const OPTION_ONETRUST_GROUP_PERFORMANCE = 'onetrust_group_id_performance';
const OPTION_ONETRUST_GROUP_FUNCTIONAL = 'onetrust_group_id_functional';
const OPTION_ONETRUST_GROUP_TARGETING = 'onetrust_group_id_targeting';
const OPTION_ONETRUST_GROUP_SOCIAL = 'onetrust_group_id_social';

const OPTION_ONETRUST_DEBUG_MODE = 'onetrust_debug_mode';
const OPTION_ONETRUST_DEBUG_DOMAIN = 'onetrust_debug_domain';
//</editor-fold> Constants

//--------------------------------------------
// Includes
//--------------------------------------------
//<editor-fold desc="Includes">
include_once GDWL_PLUGIN_DIR . '/modules/onetrust/admin/settings.php'; // Admin settings for the OneTrust module
include_once GDWL_PLUGIN_DIR . '/modules/onetrust/includes/class-onetrust.php'; // The OneTrust singleton class
//</editor-fold> Includes

