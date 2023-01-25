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

        // OneTrust Settings Fields
        $this->is_enabled = Parsing\sanitize_bool(gdwl()->settings()->get_value_bool(OPTION_ONETRUST_ENABLED, false));
        $this->group_id_necessary = sanitize_text_field(gdwl()->settings()->get_value_string(OPTION_ONETRUST_GROUP_NECESSARY, 'C0001'));
        $this->group_id_performance = sanitize_text_field(gdwl()->settings()->get_value_string(OPTION_ONETRUST_GROUP_PERFORMANCE, 'C0002'));
        $this->group_id_functional = sanitize_text_field(gdwl()->settings()->get_value_string(OPTION_ONETRUST_GROUP_FUNCTIONAL, 'C0003'));
        $this->group_id_targeting = sanitize_text_field(gdwl()->settings()->get_value_string(OPTION_ONETRUST_GROUP_TARGETING, 'C0004'));
        $this->group_id_social = sanitize_text_field(gdwl()->settings()->get_value_string(OPTION_ONETRUST_GROUP_SOCIAL, 'C0005'));

        // Third-Party Integrations
        $this->hotjar_id = sanitize_text_field(gdwl()->settings()->get_value_string(OPTION_HOTJAR_ID, ''));
        $this->google_analytics_id = sanitize_text_field(gdwl()->settings()->get_value_string(OPTION_GA_ID, ''));
        $this->google_tag_manager_id = sanitize_text_field(gdwl()->settings()->get_value_string(OPTION_GTAG_ID, ''));
        $this->facebook_pixel_id = sanitize_text_field(gdwl()->settings()->get_value_string(OPTION_FB_PIXEL_ID, ''));

        $this->js_type = ($this->is_enabled ? 'text/plain' : 'text/javascript');


        // Setup Hooks
        add_action('wp_head', array($this, 'output_wrapper_script'), 1);
        add_action('wp_head', array($this, 'output_header_tags'), 10);
        add_action('wp_body_open', array($this, 'output_body_tags'), 0);
    }

    //</editor-fold> Initialization


    //--------------------------------------------
    // Methods
    //--------------------------------------------
    //<editor-fold desc="Methods">

    public function output_wrapper_script() {
        echo PHP_EOL;
        ?>
        <script type="text/javascript">
            function OptanonWrapper() {
                // Get initial OnetrustActiveGroups ids
                if(typeof OptanonWrapperCount == "undefined"){
                    otGetInitialGrps();
                }

                //Delete cookies
                otDeleteCookie(otIniGrps);

                // Assign OnetrustActiveGroups to custom variable
                function otGetInitialGrps(){
                    OptanonWrapperCount = '';
                    otIniGrps =  OnetrustActiveGroups;
                    console.log("otGetInitialGrps", otIniGrps);
                }

                function otDeleteCookie(iniOptGrpId)
                {
                    var otDomainGrps = JSON.parse(JSON.stringify(Optanon.GetDomainData().Groups));
                    console.log("otDomainGrps", otDomainGrps);
                    var otDeletedGrpIds = otGetInactiveId(iniOptGrpId, OnetrustActiveGroups);
                    console.log("otDeletedGrpIds", otDeletedGrpIds);
                    if(otDeletedGrpIds.length != 0 && otDomainGrps.length !=0){
                        for(var i=0; i < otDomainGrps.length; i++){
                            //Check if CustomGroupId matches
                            if(otDomainGrps[i]['CustomGroupId'] != '' && otDeletedGrpIds.includes(otDomainGrps[i]['CustomGroupId'])){
                                for(var j=0; j < otDomainGrps[i]['Cookies'].length; j++){
                                    console.log("otDeleteCookie",otDomainGrps[i]['Cookies'][j]['Name']);
                                    //Delete cookie
                                    eraseCookie(otDomainGrps[i]['Cookies'][j]['Name'], otDomainGrps[i]['Cookies'][j]['Host']);
                                }
                            }

                            //Check if Hostid matches
                            if(otDomainGrps[i]['Hosts'].length != 0){
                                for(var j=0; j < otDomainGrps[i]['Hosts'].length; j++){
                                    //Check if HostId presents in the deleted list and cookie array is not blank
                                    if(otDeletedGrpIds.includes(otDomainGrps[i]['Hosts'][j]['HostId']) && otDomainGrps[i]['Hosts'][j]['Cookies'].length !=0){
                                        for(var k=0; k < otDomainGrps[i]['Hosts'][j]['Cookies'].length; k++){
                                            //Delete cookie
                                            eraseCookie(otDomainGrps[i]['Hosts'][j]['Cookies'][k]['Name'], otDomainGrps[i]['Hosts'][j]['Cookies'][k]['Host']);
                                        }
                                    }
                                }
                            }

                        }
                    }
                    otGetInitialGrps(); //Reassign new group ids
                }

                //Get inactive ids
                function otGetInactiveId(customIniId, otActiveGrp){
                    //Initial OnetrustActiveGroups
                    console.log("otGetInactiveId",customIniId);
                    customIniId = customIniId.split(",");
                    customIniId = customIniId.filter(Boolean);

                    //After action OnetrustActiveGroups
                    otActiveGrp = otActiveGrp.split(",");
                    otActiveGrp = otActiveGrp.filter(Boolean);

                    var result=[];
                    for (var i=0; i < customIniId.length; i++){
                        if ( otActiveGrp.indexOf(customIniId[i]) <= -1 ){
                            result.push(customIniId[i]);
                        }
                    }
                    return result;
                }

                //Delete cookie
                function eraseCookie(name, domain) {
                    console.log("eraseCookie called on '" + name + "'");
                    //Delete root path cookies
                    if (typeof(domain) == 'undefined' || domain == null) {
                        domain = location.hostname;
                    }

                    //!!!DEBUG
                    if (domain === 'ioglstaging.wpengine.com') {
                        domain = location.hostname;
                    }
                    //!!! END DEBUG

                    // Check for dynamic cookie names
                    let names = [];
                    const regex = /(xxx(?:x)*)/;
                    const dynamicPos = name.search(regex);
                    if (dynamicPos !== -1) {
                        console.log(name + " is a dynamic cookie");
                        const cookieNames = getCookieNames();
                        const nonDynamic = name.slice(0, dynamicPos);
                        const dLength = (name.substring(dynamicPos)).length;
                        for (let i = 0; i < cookieNames.length; ++i) {
                            if (cookieNames[i].startsWith(nonDynamic)) {
                                const cookieVar = cookieNames[i].substring(dynamicPos);
                                console.log(cookieNames[i] + " could be a match [" + cookieVar + "] | [" + name.substring(dynamicPos) + "]");
                                if (cookieVar.length <= dLength) {
                                    console.log(cookieNames[i] + " is a match for " + name);
                                    names.push(cookieNames[i]);
                                }
                            }
                        }
                        console.log(cookieNames);
                    } else {
                        names.push(name);
                    }



                    //Iterate over the cookie name matches and attempt removal

                    for (let i = 0; i < names.length; ++i) {
                        const cookieName = names[i];

                        //domainName = window.location.hostname;
                        document.cookie = cookieName+'=; Max-Age=-99999999; Path=/;Domain='+ domain;
                        console.log('Cookie - Trying to delete ' + cookieName + ' for domain ' + domain);

                        //Delete LSO incase LSO being used, cna be commented out.
                        const lSto = localStorage.getItem(cookieName);
                        if (lSto !== null) {
                            console.log('Local Storage - Removing ' + cookieName);
                        } else {
                            console.log('Local Storage - Could not find ' + cookieName);
                        }
                        localStorage.removeItem(cookieName);

                        //Check for the current path of the page
                        const pathArray = window.location.pathname.split('/');
                        //Loop through path hierarchy and delete potential cookies at each path.
                        for (let j=0; j < pathArray.length; j++){
                            if (pathArray[j]){
                                //Build the path string from the Path Array e.g /site/login
                                const currentPath = pathArray.slice(0,j+1).join('/');
                                document.cookie = cookieName+'=; Max-Age=-99999999; Path=' + currentPath + ';Domain='+ domain;
                                //Maybe path has a trailing slash!
                                document.cookie = cookieName+'=; Max-Age=-99999999; Path=' + currentPath + '/;Domain='+ domain;
                            }
                        }
                    }
                }

                function getCookieNames() {
                    let decodedCookie = decodeURIComponent(document.cookie);
                    let ca = decodedCookie.split(';');
                    let ret = [];
                    for (let i = 0; i < ca.length; ++i) {
                        let pos = ca[i].indexOf("=");
                        ret.push(ca[i].slice(0, pos).trim());
                    }
                    return ret;
                }
            }
        </script>
        <?php
        echo PHP_EOL;
    }

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
        <!-- Hotjar Tracking Code for https://ioglstaging.wpengine.com/ -->
        <script type="<?php echo $this->js_type;?>" class="<?php echo $class_grp_performance; ?>">
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
        <script type="<?php echo $this->js_type;?>" class="<?php echo $class_grp_performance; ?>">
            !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
                n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
                t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
                document,'script','https://connect.facebook.net/en_US/fbevents.js');
        </script>
        <!-- End Facebook Pixel Code -->
        <script type="<?php echo $this->js_type;?>" class="<?php echo $class_grp_performance; ?>">
            fbq('init', '<?php echo $this->facebook_pixel_id; ?>', {}, {
                "agent": "wordpress-6.1.1-3.0.6"
            });
        </script><script type="<?php echo $this->js_type;?>" class="<?php echo $class_grp_performance; ?>">
            fbq('track', 'PageView', []);
        </script>
        <!-- Facebook Pixel Code -->
        <noscript>
            <img height="1" width="1" style="display:none" alt="fbpx"
                 src="https://www.facebook.com/tr?id=<?php echo $this->facebook_pixel_id; ?>&ev=PageView&noscript=1" />
        </noscript>
        <!-- End Facebook Pixel Code -->
        <?php endif; ?>
        <?php
        echo PHP_EOL; //Newline to end the block
    }

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