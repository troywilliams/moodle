function lc_testServerConfiguration(url, servername, username, password) {
    var aurl = url+"?user="+username+"&pass="+password+"&server="+servername;
    var callback = {
        success: function(o) {
            if (o.responseText != "ok")
                lc_displayPopup(o.responseText);
            else
                document.forms.adminsettings.submit();
        },
        failure: function(o) { }
    }

    var transaction = YAHOO.util.Connect.asyncRequest('GET', aurl, callback, null);
}

function lc_CheckConfiguration(){
    serverName = document.forms.adminsettings.s__liveclassroom_servername;
    adminUserName = document.forms.adminsettings.s__liveclassroom_adminusername;
    adminPassword = document.forms.adminsettings.s__liveclassroom_adminpassword;

    if(serverName.value.length==0 || serverName.value == null)
    {
        return displayPopup(M.str.liveclassroom.wrongconfigurationURLunavailable);
    }
    if(adminUserName.value.length==0 || adminUserName.value == null)
    {
        return displayPopup(M.str.liveclassroom.emptyAdminUsername);
    }
    if(adminPassword.value.length==0 || adminPassword.value == null)
    {
        return displayPopup(M.str.liveclassroom.emptyAdminPassword);
    }
    if (serverName.value.charAt(serverName.value.length-1) == '/')
    {
        return displayPopup(M.str.liveclassroom.trailingSlash);
    } 

    if (!serverName.value.match('http://') && !serverName.value.match('https://'))
    {
        return displayPopup(M.str.liveclassroom.trailingHttp);
    } 
    //check if the api account filled is correct and allowed
    lc_testServerConfiguration(M.cfg.wwwroot+"/mod/liveclassroom/testConfig.php",serverName.value,adminUserName.value,adminPassword.value);
}

function lc_displayPopup(errorText){
   YAHOO.util.Dom.get('popup').style.display = 'block';
   YAHOO.util.Dom.get('hiddenDiv').style.display = 'block';
   YAHOO.util.Dom.get("popupText").innerHTML=errorText;
   return false;
}

function lc_undisplayPopup(){
   YAHOO.util.Dom.get('popup').style.display = 'none';
   YAHOO.util.Dom.get('hiddenDiv').style.display = 'none';
   YAHOO.util.Dom.get("popupText").innerHTML="";
   return false;
}
