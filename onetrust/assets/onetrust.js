/**
 * This function is called by the OneTrust cookie banner integration whenever the
 * user changes their preferences for cookies in the Preference Center.
 */
console.log("GD IT!");
function OnOptanonUpdate() {
    // Do not proceed if the oneTrustData object does not exist
    if (typeof oneTrustData == 'undefined' || oneTrustData == null) {
        console.error('OptanonWrapper cannot fire because oneTrustData was not found!');
        return;
    }

    /**
     * Logs a debug message to the console. Only logs when the OneTrust
     * integration is in test mode.
     * @param {string} value The text output of the log
     * @param {any} context A context object to log the contents of
     */
    function log(value, context) {
        if (oneTrustData.testMode) {
            if (typeof(context) === 'undefined' || context == null) {
                context = '';
            }
            console.log('::[OneTrust Cookie Banner]:: - ' + context, value);
        }
    }

    /**
     * Returns the currently set value of the OptanonConsent cookie which
     * stores the user's preferences regarding cookies.
     * @return {string} The cookie value
     */
    function getConsentCookie() {
        const name = "OptanonConsent=";
        const ca = document.cookie.split(';');
        for(let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }

    /**
     * Returns a list of category IDs for cookie groups that have
     * been disabled by the user.
     * @return {array} Array of disabled cookie category IDs
     */
    function getDisabledGroups() {
        const disabledGroups = [];

        // Pull the consent cookie value
        const cookieValue = getConsentCookie();
        if (cookieValue === "") {
            return disabledGroups;
        }

        // Obtain the group ID statuses from the cookie value
        const valueSet = cookieValue.split("&");
        let groupValue = "";
        for (let i = 0; i < valueSet.length; ++i) {
            if (valueSet[i].startsWith("groups=")) {
                groupValue = decodeURIComponent(valueSet[i]);
            }
        }
        const groups = groupValue.split(",");
        for (let i = 0; i < groups.length; ++i) {
            const data = groups[i].split(":");
            if (data[1] === "0") {
                disabledGroups.push(data[0]);
            }
        }
        return disabledGroups;
    }

    /**
     * Pulls the initial cookie category groups and assigns them to otIniGrps.
     */
    function otGetInitialGrps() {
        OptanonWrapperCount = '';
        otIniGrps =  OnetrustActiveGroups;
        log(otIniGrps, "otGetInitialGrps");
    }

    /**
     * Returns a list of cookie catagory groups that have opted out of by the
     * user.
     * @param {string} customIniId Comma-separated group IDs of the loaded category groups
     * @param {*} otActiveGrp OneTrust active groups
     * @return {array} An array of inactive group ids
     */
    function otGetInactiveId(customIniId, otActiveGrp) {
        // Initial OnetrustActiveGroups
        log(customIniId, "otGetInactiveId (customIniId)");
        log(otActiveGrp, "otGetInactiveId (otActiveGrp)");
        // Split the groups into an array
        customIniId = customIniId.split(",");
        customIniId = customIniId.filter(Boolean);
        log(customIniId, "otGetInactiveId (customIniId)");

        // After action OnetrustActiveGroups
        otActiveGrp = otActiveGrp.split(",");
        otActiveGrp = otActiveGrp.filter(Boolean);
        log(otActiveGrp, "otGetInactiveId (otActiveGrp)");

        var result=[];
        // Iterate through all the given group IDs and check against the active groups to build the delete list
        for (var i=0; i < customIniId.length; i++){
            if ( otActiveGrp.indexOf(customIniId[i]) <= -1 ){
                // If the active groups does not contain the given group, add it to the delete list
                result.push(customIniId[i]);
            }
        }
        return result;
    }

    /**
     * Removes the given cookie with the given domain from the user's browser cookies.
     * @param {string} name The name of the cookie to remove
     * @param {string} domain The domain the cookie was originally registered to
     */
    function eraseCookie(name, domain) {
        log("eraseCookie called on '" + name + "'");
        // Delete root path cookies
        if (typeof(domain) == 'undefined' || domain == null) {
            domain = location.hostname;
        }

        if (oneTrustData.testMode) {
            if (domain === oneTrustData.domain || domain === '.' + oneTrustData.domain) {
                domain = location.hostname;
            }
        }

        // Remove the cookie from the document cookies
        document.cookie = name+'=; Max-Age=-99999999; Path=/;Domain='+ domain;
        log('Cookie - Trying to delete ' + name + ' for domain ' + domain);
        document.cookie = name+'=; Max-Age=-99999999; Path=/;Domain=.'+ domain;
        log('Cookie - Trying to delete ' + name + ' for domain .' + domain);
        document.cookie = name+'=; Max-Age=-99999999; Path=/;Domain=.wpengine.com';
        log('Cookie - Trying to delete ' + name + ' for domain .wpengine.com');

        //Delete LSO incase LSO being used, cna be commented out.
        const lSto = localStorage.getItem(name);
        if (lSto !== null) {
            log('Local Storage - Removing ' + name);
            localStorage.removeItem(name);
        } else {
            log('Local Storage - Could not find ' + name);
        }

        //Check for the current path of the page
        const pathArray = window.location.pathname.split('/');
        //Loop through path hierarchy and delete potential cookies at each path.
        for (let j=0; j < pathArray.length; j++){
            if (pathArray[j]){
                //Build the path string from the Path Array e.g /site/login
                const currentPath = pathArray.slice(0,j+1).join('/');
                document.cookie = name+'=; Max-Age=-99999999; Path=' + currentPath + ';Domain='+ domain;
                //Maybe path has a trailing slash!
                document.cookie = name+'=; Max-Age=-99999999; Path=' + currentPath + '/;Domain='+ domain;
            }
        }
    }

    /**
     * Removes active cookies from the given cookie category group IDs. Called once
     * the user opts out of cookies.
     * @param {array} groupIds
     */
    function otDeleteCookie(groupIds) {
        var otDomainGrps = JSON.parse(JSON.stringify(Optanon.GetDomainData().Groups));
        log(otDomainGrps, "otDomainGrps");

        if(groupIds.length != 0 && otDomainGrps.length !=0){
            for(var i=0; i < otDomainGrps.length; i++){
                //Check if CustomGroupId matches
                if(otDomainGrps[i]['CustomGroupId'] != '' && groupIds.includes(otDomainGrps[i]['CustomGroupId'])){
                    for(var j=0; j < otDomainGrps[i]['Cookies'].length; j++){
                        log(otDomainGrps[i]['Cookies'][j]['Name'], "otDeleteCookie");
                        //Delete cookie
                        eraseCookie(otDomainGrps[i]['Cookies'][j]['Name'], otDomainGrps[i]['Cookies'][j]['Host']);
                    }
                }

                //Check if Hostid matches
                if(otDomainGrps[i]['Hosts'].length != 0){
                    for(var j=0; j < otDomainGrps[i]['Hosts'].length; j++){
                        //Check if HostId presents in the deleted list and cookie array is not blank
                        if(groupIds.includes(otDomainGrps[i]['Hosts'][j]['HostId']) && otDomainGrps[i]['Hosts'][j]['Cookies'].length !=0){
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

    /**
     * Checks the inital group IDs passed to the function against the active group
     * IDs and removes the cookies for any groups that are inactive.
     * @param {*} iniOptGrpId
     */
    function synchCookiePreferences(iniOptGrpId) {
        var otDeletedGrpIds = otGetInactiveId(iniOptGrpId, OnetrustActiveGroups);
        log(otDeletedGrpIds, "synchCookiePreferences");

        if (otDeletedGrpIds.length != 0) {
            localStorage.setItem('gdwl_optanon_check', '1');
            log("Set storage variable to do a check again after next page load.");
        }

        otDeleteCookie(otDeletedGrpIds);
    }

    // Get initial OnetrustActiveGroups ids
    if (typeof OptanonWrapperCount == "undefined") {
        otGetInitialGrps();
    }

    // Check if we just removed cookies so we can check again on the first page load to remove some pesky clingers
    const optanonCheck = localStorage.getItem('gdwl_optanon_check');
    if (optanonCheck !== null) {
        log("Just deleted cookies, doing another check to make sure they're all gone.");
        // Get all the opted-out groups
        const deleteGroups = getDisabledGroups();

        // Call delete cookie function on opted-out groups
        otDeleteCookie(deleteGroups);

        // Finally, remove the local storage value so this doesn't fire again
        localStorage.removeItem('gdwl_optanon_check');
    }

    // Delete cookies in opted out groups
    synchCookiePreferences(otIniGrps);
}
window.addEventListener('optanonUpdated', function() {
    OnOptanonUpdate();
});