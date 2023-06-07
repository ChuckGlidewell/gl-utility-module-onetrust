<?php
namespace Glidewell\Vendors\OneTrust;
if(!defined('ABSPATH')) {exit;}

// Usings
use Glidewell\StringUtils as Strings;
use Glidewell\Debug;
use Glidewell\Parsing;

/**
 * Class for managing the OneTrust Cookie Banner integration on the satellite site
 */
class OneTrust {

    //--------------------------------------------
    // Fields
    //--------------------------------------------
    //<editor-fold desc="Fields">

    public $js_type = 'text/javascript';

    // Settings Fields
    /**
     * Determines whether the OneTrust integration is enabled.
     * @var bool
     */
    public $is_enabled = false;
    /**
     * The ID key that identifies this domain in OneTrust. Used for outputting the banner scripts.
     * @var string
     */
    public $domain_id = '';
    /**
     * Determines if the OneTrust cookie banner has Auto-Blocking enabled
     * @var bool
     */
    public $auto_blocking_enabled = true;
    /**
     * The Group ID for Strictly Necessary cookies
     * @var string
     */
    public $group_id_necessary = '';
    /**
     * The Group ID for Functional cookies
     * @var string
     */
    public $group_id_functional = '';
    /**
     * The Group ID for Performance cookies
     * @var string
     */
    public $group_id_performance = '';
    /**
     * The Group ID for targeting cookies
     * @var string
     */
    public $group_id_targeting = '';
    /**
     * The Group ID for Social Media cookies
     * @var string
     */
    public $group_id_social = '';
    /**
     * Determines if the debug mode setting is enabled
     * @var bool
     */
    public $is_debug_mode = false;
    /**
     * The original domain the cookies are registered under for use with debugging on staging/dev environments
     * @var string
     */
    public $debug_domain = '';

    /**
     * The Hotjar ID for this site
     * @var string
     */
    public $hotjar_id = '';
    /**
     * The Google Analytics property ID for this site
     * @var string
     */
    public $google_analytics_id = '';
    /**
     * The Google Tag Manager container ID for this site
     * @var string
     */
    public $google_tag_manager_id = '';
    /**
     * The Facebook Pixel ID for this site
     * @var string
     */
    public $facebook_pixel_id = '';

    //</editor-fold> Fields


    //--------------------------------------------
    // Initialization
    //--------------------------------------------
    //<editor-fold desc="Initialization">

    /**
     * Main class constructor
     */
    public function __construct() {
        // Setup Hooks
        add_action('gdwl_settings_loaded', array($this, 'load_settings'));
        add_action('gdwl_on_environment_changed', array($this, 'on_environment_changed'), 10, 2);
        gdwl()->load_script('gdwl-onetrust-script', 'modules/onetrust/assets/onetrust.js', array(), false);
        add_action('wp_head', array($this, 'output_banner_script'), 0);
        add_action('wp_head', array($this, 'output_header_tags'), 10);
        add_action('wp_body_open', array($this, 'output_body_tags'), 0);
    }

    //</editor-fold> Initialization


    //--------------------------------------------
    // Methods
    //--------------------------------------------
    //<editor-fold desc="Methods">

    /**
     * Loads the settings for the OneTrust integration
     * @return void
     */
    public function load_settings() {
        // OneTrust Settings Fields
        $this->is_enabled = Parsing\sanitize_bool(gdwl()->settings()->get_value_bool(OPTION_ONETRUST_ENABLED, false));
        $this->domain_id = sanitize_text_field(gdwl()->settings()->get_value_string(OPTION_ONETRUST_DOMAIN_ID, ''));
        $this->auto_blocking_enabled = Parsing\sanitize_bool(gdwl()->settings()->get_value_bool(OPTION_ONETRUST_AUTO_BLOCK, true));
        $this->group_id_necessary = sanitize_text_field(gdwl()->settings()->get_value_string(OPTION_ONETRUST_GROUP_NECESSARY, 'C0001'));
        $this->group_id_performance = sanitize_text_field(gdwl()->settings()->get_value_string(OPTION_ONETRUST_GROUP_PERFORMANCE, 'C0002'));
        $this->group_id_functional = sanitize_text_field(gdwl()->settings()->get_value_string(OPTION_ONETRUST_GROUP_FUNCTIONAL, 'C0003'));
        $this->group_id_targeting = sanitize_text_field(gdwl()->settings()->get_value_string(OPTION_ONETRUST_GROUP_TARGETING, 'C0004'));
        $this->group_id_social = sanitize_text_field(gdwl()->settings()->get_value_string(OPTION_ONETRUST_GROUP_SOCIAL, 'C0005'));

        // Debug Settings
        $this->is_debug_mode = Parsing\sanitize_bool(gdwl()->settings()->get_value_bool(OPTION_ONETRUST_DEBUG_MODE, false));
        $this->debug_domain = sanitize_text_field(gdwl()->settings()->get_value_string(OPTION_ONETRUST_DEBUG_DOMAIN, ''));

        // Third-Party Integrations
        $this->hotjar_id = sanitize_text_field(gdwl()->settings()->get_value_string(OPTION_HOTJAR_ID, ''));
        $this->google_analytics_id = sanitize_text_field(gdwl()->settings()->get_value_string(OPTION_GA_ID, ''));
        $this->google_tag_manager_id = sanitize_text_field(gdwl()->settings()->get_value_string(OPTION_GTAG_ID, ''));
        $this->facebook_pixel_id = sanitize_text_field(gdwl()->settings()->get_value_string(OPTION_FB_PIXEL_ID, ''));

        $this->js_type = ($this->is_enabled ? 'text/plain' : 'text/javascript');
    }

    /**
     * Outputs the header script for the OneTrust banner functionality. Will automatically output the test scripts if
     * debug mode is enabled.
     * @return void
     */
    public function output_banner_script() {
        if (!$this->is_enabled || Strings\is_null_or_empty($this->domain_id)) {
            return;
        }
        $urlparts = parse_url(home_url());
        $domain = $urlparts['host'];
        $domain_key = trim($this->domain_id) . ($this->is_debug_mode ? '-test' : '');
?>
        <!-- OneTrust Cookies Consent Notice start for <?php echo $domain; ?> -->
        <?php if ($this->auto_blocking_enabled) : ?>
        <script type="text/javascript" src="https://cdn.cookielaw.org/consent/<?php echo $domain_key; ?>/OtAutoBlock.js" ></script>
        <?php endif; ?>
        <script src="https://cdn.cookielaw.org/scripttemplates/otSDKStub.js"  type="text/javascript" charset="UTF-8" data-domain-script="<?php echo $domain_key; ?>" ></script>
        <script type="text/javascript">
            var oneTrustData = {
                "domain": "<?php echo $this->debug_domain ?>",
                "testMode": <?php echo $this->is_debug_mode ? 'true' : 'false'; ?>
            };
            function OptanonWrapper() {
                var event = new Event('optanonUpdated', {
                    bubbles: true,
                    cancelable: true,
                    composed: false
                });
                window.dispatchEvent(event);
            }
        </script>
        <!-- OneTrust Cookies Consent Notice end for <?php echo $domain; ?> -->
<?php
    }

    /**
     * Outputs script tags for third-party integrations using OneTrust blocking mechanism for the header.
     * @return void
     */
    public function output_header_tags() {
        echo PHP_EOL; //Newline to start the block

        // Setup class variables
        $class_grp_performance = ($this->is_enabled ? 'optanon-category-' . $this->group_id_performance : '');
        $class_grp_functional = ($this->is_enabled ? 'optanon-category-' . $this->group_id_functional : '');
        $class_grp_targeting = ($this->is_enabled ? 'optanon-category-' . $this->group_id_targeting : '');
        $class_grp_social = ($this->is_enabled ? 'optanon-category-' . $this->group_id_social : '');

        ?>
        <?php if (!Strings\is_null_or_empty($this->google_analytics_id)) : ?>
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script type="<?php echo $this->js_type;?>" class="<?php echo $class_grp_performance; ?>" async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $this->google_analytics_id; ?>"></script>
        <script type="<?php echo $this->js_type;?>" class="<?php echo $class_grp_performance; ?>">
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());

          gtag('config', '<?php echo $this->google_analytics_id; ?>');
        </script>
        <!-- End Google Analytics -->
        <?php endif; ?>
        <?php if (!Strings\is_null_or_empty($this->google_tag_manager_id)) : ?>
        <!-- Google Tag Manager -->
        <script type="<?php echo $this->js_type;?>" class="<?php echo $class_grp_performance; ?>">
            (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','<?php echo $this->google_tag_manager_id; ?>');
        </script>
        <!-- End Google Tag Manager -->
        <?php endif; ?>
        <?php if (!Strings\is_null_or_empty($this->hotjar_id)) : ?>
        <!-- Hotjar Tracking Code -->
        <script type="<?php echo $this->js_type;?>" class="<?php echo $class_grp_targeting; ?>">
        (function(h,o,t,j,a,r){
            h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
            h._hjSettings={hjid:<?php echo $this->hotjar_id; ?>,hjsv:6};
            a=o.getElementsByTagName('head')[0];
            r=o.createElement('script');r.async=1;
            r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
            a.appendChild(r);
        })(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
        </script>
        <!-- END Hotjar -->
        <?php endif; ?>
        <?php if (!Strings\is_null_or_empty($this->facebook_pixel_id)) : ?>
        <!-- Facebook Pixel Code -->
        <script type="<?php echo $this->js_type;?>" class="<?php echo $class_grp_targeting; ?>">
            !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
                n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
                t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
                document,'script','https://connect.facebook.net/en_US/fbevents.js');
        </script>
        <script type="<?php echo $this->js_type;?>" class="<?php echo $class_grp_targeting; ?>">
            fbq('init', '<?php echo $this->facebook_pixel_id; ?>', {}, {
                "agent": "wordpress-6.1.1-3.0.6"
            });
        </script><script type="<?php echo $this->js_type;?>" class="<?php echo $class_grp_targeting; ?>">
            fbq('track', 'PageView', []);
        </script>
        <noscript>
            <img height="1" width="1" style="display:none" alt="fbpx"
                 src="https://www.facebook.com/tr?id=<?php echo $this->facebook_pixel_id; ?>&ev=PageView&noscript=1" />
        </noscript>
        <!-- End Facebook Pixel Code -->
        <?php endif; ?>
        <?php
        echo PHP_EOL; //Newline to end the block
    }

    /**
     * Outputs script tags for third-party integrations using OneTrust blocking mechanism for the body.
     * @return void
     */
    public function output_body_tags() {
        echo PHP_EOL; //Newline to start the block
        $src = ($this->is_enabled ? 'data-src' : 'src');
        $iClass = ($this->is_enabled ? 'class="optanon-category-' . $this->group_id_performance . '"' : '');

        if (!Strings\is_null_or_empty($this->google_tag_manager_id)) : ?>
        <!-- Google Tag Manager (noscript) -->
        <noscript><iframe <?php echo $src; ?>="https://www.googletagmanager.com/ns.html?id=<?php echo $this->google_tag_manager_id; ?>"
                          height="0" width="0" style="display:none;visibility:hidden" <?php echo $iClass; ?>></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->
<?php
        endif;
        echo PHP_EOL; //Newline to end the block
    }

    /**
     * Event called when the site environment changes due to staging being pushed to production via WPEngine
     * @param $prev
     * @param $cur
     */
    public function on_environment_changed($prev = 1, $cur = 0) {
        $env_prev = ($prev === -1 ? 'UNSET' : gdwl()->get_environment_by_index($prev));
        $env_new = gdwl()->get_environment_by_index($cur);

        Debug::log(__METHOD__ . '() - Environment Changed from [col=yellow]' . $env_prev . '[/col] to [col=green]' . $env_new . '[/col]');

        // Only proceed if this is the Production Environment
        if ($cur !== 0) {
            return;
        }

        // Revert Debug Mode and Debug Domain to prevent issues on Production
        $set_debug = gdwl()->settings()->get_setting(OPTION_ONETRUST_DEBUG_MODE);
        $set_domain = gdwl()->settings()->get_setting(OPTION_ONETRUST_DEBUG_DOMAIN);

        $sDEBUG = "\n" . '========== Reverting Debug Settings ==========' . "\n";

        if ($set_debug != null) {
            $old_value = $set_debug->get_value();
            $set_debug->set_value(false);
            $sDEBUG .= '[col=white][b]Debug Mode[/b][/col]: [col=yellow]' . $old_value . '[/col] >> [col=green]false[/col]';
        }
        if ($set_domain != null) {
            $old_value = $set_domain->get_value();
            $set_domain->set_value('');
            $sDEBUG .= '[col=white][b]Debug Mode[/b][/col]: [col=yellow]"' . $old_value . '"[/col] >> [col=green]""[/col]';
        }
        $sDEBUG .= '========== DONE Reverting Debug Settings ==========' . "\n";
        Debug::log(__METHOD__ . '() - ' . $sDEBUG);
    }

    //</editor-fold> Methods


    //----------------------------------------------------------
    // Singleton Methods
    //----------------------------------------------------------
    // <editor-fold desc="Singleton Methods">

    /** @var OneTrust */
    private static $_instance = null;


    /**
     * The main instance of the plugin class
     * @return OneTrust
     */
    public static function instance()
    {
        if (is_null(self::$_instance))
        {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Cloning is forbidden.
     */
    public function __clone()
    {
        _doing_it_wrong(__METHOD__, 'Cloning is forbidden.', '1.0');
    }

    /**
     * Deserializing instances of this class is forbidden.
     */
    public function __wakeup()
    {
        _doing_it_wrong(__METHOD__, 'Deserializing instances of this class is forbidden.', '1.0');
    }
    // </editor-fold> Singleton Methods
}

/**
 * Returns the current instance of the OneTrust class
 * @return OneTrust
 */
function onetrust() : OneTrust {
    return OneTrust::instance();
}

onetrust();