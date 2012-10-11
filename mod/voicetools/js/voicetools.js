var $ = YAHOO.util.Dom.get;

function testServerConfiguration(url, servername, username, password) {
    var aurl = url+"?user="+username+"&pass="+password+"&server="+servername;
    var callback = {
        success: function(o) {
            if (o.responseText != "ok" && o.responseText != '')
                displayPopup(o.responseText);
            else if (o.responseText == '')
                displayPopup(M.str.voicetools.wrongconfigurationURLunavailable);
            else
                document.forms.adminsettings.submit();
        },
        failure: function(o) { }
    }

    var transaction = YAHOO.util.Connect.asyncRequest('GET', aurl, callback, null);
}

function CheckConfiguration(){  
    serverName = document.forms.adminsettings.s__voicetools_servername;
    adminUserName = document.forms.adminsettings.s__voicetools_adminusername;
    adminPassword = document.forms.adminsettings.s__voicetools_adminpassword;
     
    if(serverName.value.length==0 || serverName.value == null)
    {
        return displayPopup(M.str.voicetools.wrongconfigurationURLunavailable);
    }
    if(adminUserName.value.length==0 || adminUserName.value == null)
    {
        return displayPopup(M.str.voicetools.emptyAdminUsername);
    }
    if(adminPassword.value.length==0 || adminPassword.value == null)
    {
        return displayPopup(M.str.voicetools.emptyAdminPassword);
    }
    if (serverName.value.charAt(serverName.value.length-1) == '/') 
    {
        return displayPopup(M.str.voicetools.trailingSlash);
    } 

    if (!serverName.value.match('http://') && !serverName.value.match('https://')) 
    {
        return displayPopup(M.str.voicetools.trailingHttp);
    } 
    //check if the api account filled is correct and allowed
    testServerConfiguration(M.cfg.wwwroot+"/mod/voicetools/testConfig.php",serverName.value,adminUserName.value,adminPassword.value);     
}
    
function displayPopup(errorText){
   $('popup').style.display = 'block';
   $('hiddenDiv').style.display = 'block';
   $("popupText").innerHTML=errorText;
   return false;
}
    
    
function undisplayPopup(){
   $('popup').style.display = 'none';
   $('hiddenDiv').style.display = 'none';
   $("popupText").innerHTML="";
   return false;
}

