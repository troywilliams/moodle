function validate(type){
  // name can't be null
  $('isfirst').value = type;
  if( isFormValidated == false)
  { 
    return false;
  }
  
  $("form").submit();
}

function isOk()
{
  if( !$("nameNewResource").value.blank())
  {
    $("advancedOk").removeClassName("regular_btn-submit-disabled");
    $("advancedOk").addClassName("regular_btn-submit");
    $("advancedOk").disabled="";
  } 
  else
  {
    $("advancedOk").addClassName("regular_btn-submit-disabled");
    $("advancedOk").removeClassName("regular_btn-submit");
    $("advancedOk").disabled="true";
  }
}

function isValidate(){
    isFormValidated = true;
    return;
  // name can't be null
  if( $("id_name").value.blank())
  { 
    isFormValidated=false;
    $("id_submitbutton").addClassName("regular_btn-disabled");
    $("id_submitbutton").removeClassName("regular_btn");
    $("id_submitbutton").disabled="true";
    $("id_submitbutton2").addClassName("regular_btn-disabled");
    $("id_submitbutton2").removeClassName("regular_btn");
    $("id_submitbutton2").disabled="true";
    
    return false;
  }
  else if( $("id_resource").value=="empty" || $("id_resource").value=="new")
  {
    isFormValidated=false;  
    $("id_submitbutton").addClassName("regular_btn-disabled");
    $("id_submitbutton").removeClassName("regular_btn");
    $("id_submitbutton").disabled="true";
    $("id_submitbutton2").addClassName("regular_btn-disabled");
    $("id_submitbutton2").removeClassName("regular_btn");
    $("id_submitbutton2").disabled="true";
    return false;  
  }
  isFormValidated=true; 
  $("id_submitbutton").removeClassName("regular_btn-disabled");
  $("id_submitbutton").addClassName("regular_btn");
  $("id_submitbutton").disabled="";
  $("id_submitbutton2").removeClassName("regular_btn-disabled");
  $("id_submitbutton2").addClassName("regular_btn");
  $("id_submitbutton2").disabled="";
}

function popupCancel()
{
    $("popup").style.display="none";
    $("hiddenDiv").style.display="none";
    location.href = M.cfg.wwwroot+"/course/view.php?id="+$F($('mform1')['course']);
}

function popupOk()
{
    $("popup").style.display="none";
    $("hiddenDiv").style.display="none";
    location.href = M.cfg.wwwroot+"/mod/voicepresentation/index.php?id="+$F($('mform1')['course']);
}

function hideCalendarEvent(value)
{
   // if(value=="check")
    //{                              
        if($("id_calendar_event").checked==true)
        {
            value="visible";
        }
        else
        {
            value="hidden";
        }
    //}      
    
    $("calendar").style.visibility=value ;
    $("calendar_extra").style.visibility=value ;
}

function create(name,courseid){
    if($("nameNewResource").value.blank())
        return false;
    $("newPopup").hide();
    $('loading').show();
    createNewResource(M.cfg.wwwroot+"/mod/voicepresentation/manageAction.php","voicetools","presentation",name.value,$F($('mform1')["url_params"]));
    name.value=""; //for the next on
    $('id_name').focus();
}

function LoadNewFeaturePopup(current)
{
    if( current == "new" ){
        $("hiddenDiv").style.height=document.documentElement.clientHeight
        $("hiddenDiv").style.width=document.documentElement.clientWidth
        $("newPopup").show();
        $("hiddenDiv").show();  
        $("nameNewResource").focus();
        var allSelect =  document.getElementsByTagName("select");
        for( i=0;i<allSelect.length;i++)
        {
            allSelect[i].style.visibility="hidden";
        }
    }
}

function onCancelButtonPopup(){
    $('id_resource').selectedIndex=0;
    $('newPopup').hide();
    $('hiddenDiv').hide();
    $('id_name').focus();
    var allSelect =  document.getElementsByTagName("select");
    for( i=0;i<allSelect.length;i++)
    {
        allSelect[i].style.visibility="";
    }
    return false;
}

