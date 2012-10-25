
function init_advanced_assign() {

    // Get the parent node
    var frm = document.getElementById('advanced-assign-form');

    // Attach an onChange event to every checkbox in in the form
    var checkboxes = YAHOO.util.Dom.getElementsBy(isCheckbox, 'input' , 'advanced-assign-form', onChangeAction);
}


isCheckbox = function(elm) {
    if (elm.type == 'checkbox') {
        return(true);
    }
    return(false);
};

onChangeAction = function(elm) {
    //YAHOO.util.Event.addListener(elm, "change", onChangeCallback);
    YAHOO.util.Event.addListener(elm, "click", onChangeCallback);
}

onChangeCallback = function(e) {
    var elm = YAHOO.util.Event.getTarget(e);
    updateCounts(elm)
}


function updateCounts(elm) {
    var regex = /\[(.*)\]\[(.*)\]/;
    var matches = elm.name.match(regex);
    var userid = matches[2];
    var groupid = matches[1];
    var groupCnt = document.getElementById('user_grp_cnt_'+userid);
    var userCnt = document.getElementById('grp_user_cnt_'+groupid);

    if (elm.checked) {
        groupCnt.innerHTML++;
        userCnt.innerHTML++;
    }
    else {
        groupCnt.innerHTML--;
        userCnt.innerHTML--;
    }

}

YAHOO.util.Event.onDOMReady(init_advanced_assign);
