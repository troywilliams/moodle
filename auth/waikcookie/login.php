<?php
require_once('../../config.php');
$encref = optional_param('encref', null, PARAM_TEXT);

if (!isset($encref)) {
    $encref = base64_encode($CFG->wwwroot . '/login/index.php');
}    

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <title>iWaikato Login</title>
    <meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
    <meta name="DC.Language" content="en" SCHEME="ISO639-1">
    <meta name="DC.Rights" content="http://www.waikato.ac.nz/copyright.shtml">
    <meta name="no-cache" content="">
    <meta name="Expires" content="Thu, 1 Jan 1970 00:00:01 GMT">
    
    <link rel="stylesheet" type="text/css" media="print" href="http://cookie.waikato.ac.nz/css/uow2004_print.css">

    <link rel="stylesheet" href="http://cookie.waikato.ac.nz/css/uow2004_base.css" type="text/css">
    
</head>
<body>
<table width="800" cellpadding="0" cellspacing="0" border="0">
<tr><td colspan='2' align='left'>
<table width='800' cellpadding='0' cellspacing='0' border='0'><tr>
    <td valign='middle' align='center' rowspan='2' width='200' bgcolor='#CC0000'><a href='http://www.waikato.ac.nz' title='To Waikato Home Page'><img src='http://cookie.waikato.ac.nz/images/coa_red.gif' alt='University Crest'  border='0'  vspace='6' hspace='6' width='196' height='60'class='noprint'><img src='http://cookie.waikato.ac.nz/images/coa.gif' alt='The University of Waikato - Te Whare Wananga o Waikato' vspace='20' hspace='20' border='0' width='196' height='60' class='printerOnly'></a></td>
    <td valign='middle' align='center' rowspan='2' width='16' bgcolor='#CC0000' class='noprint'><img src='http://cookie.waikato.ac.nz/images/space.gif' width='16' alt='' height='1'></td>
    <td width='584' ><table border='0' cellspacing='0' cellpadding='0' width='584'>
      <tr><td width='320' align='left' nowrap bgcolor='#CC0000'><h1 class='banner'>Moodle Login</h1></td><td bgcolor='#CC0000' valign='top'>&nbsp;</td>

                <td valign='top' align='right' bgcolor='#CC0000'><img src='http://cookie.waikato.ac.nz/images/nav_end_top_red.gif' alt='' width='49' height='39'></td></tr></table></td></tr>
    <tr class='noprint'><td width='584' align='right' bgcolor='#CC0000' ><table border='0' cellspacing='0' cellpadding='0' width='584'>
    <tr><td align='left' width='535' bgcolor='#CC0000' class='noprint'></td>
    <td width='49' valign='top' align='right' bgcolor='#CC0000'><img src='http://cookie.waikato.ac.nz/images/nav_end_bot_red.gif' width='49' height='39' alt=''></td>
    </tr></table></td></tr></table><table width='740' border='0' class='noprint'><tr><td><table cellpadding='0' cellspacing='0'><tr><td>
<a href='http://www.waikato.ac.nz/' class='utility'>
<img src='http://cookie.waikato.ac.nz/images/crumbs_start.gif' alt='To The University of Waikato Homepage' width='33' height='33' border='0' align='middle'></a></td><td><a href='http://www.waikato.ac.nz/' class='utility'>Waikato Home</a></td></tr></table></td><td align='right'></td></tr></table>
</td></tr>
<tr><td width='15' class='noprint'><img src='http://cookie.waikato.ac.nz/images/space.gif' width='15' alt='' height='1'></td>
<td valign='top'>
<a name="pageopen"></a>
 <p>Moodle is the online teaching and learning platform of The University of Waikato developed and maintained by the Waikato Centre for eLearning (WCEL).</p>

<table border="0" cellpadding="4">
    <tr>
        <td valign="top">
        
			<p class=sidemenutitle style="font-size: 125%">University user login</p>
        <p>Enter your University of Waikato staff or student username and password please login using the login area on the left.<p/>
<ul>
<li>You do not need to add '<tt>@waikato.ac.nz</tt>' to the end of your username</li>
<li>Waikato staff will <strong>never</strong> ask you for your password</li>

<li><b>Remember</b> to click on Logout or exit your web browser when you have finished</li></ul>

<form action="http://cookie.waikato.ac.nz/cgi-bin/WCLogin" name="loginform" method=post enctype=multipart/form-data>
<table cellpadding=3 border=0>
<tr><td align=right style="font-weight: bold">Username</td><td><input type=text name=username value="" size=12 maxlength=8>
<tr><td align=right style="font-weight: bold">Password</td><td><input type=password name=password size=12 maxlength=20>
<tr><td></td><td><input type=submit name=login value=Login>
</table>
<input type=hidden name=http_ref value="http://www.waikato.ac.nz/staff/"><br>
<input type=hidden name=encref value="<?php echo $encref; ?>">
<script language=javascript>this.document.loginform.username.focus()</script>
</form>
<p>Are you a <a href="https://tools.its.waikato.ac.nz/cgi-bin/newuser/newuser">New user?</a></p>
<p><a HREF="http://askme.waikato.ac.nz/">Ask Me</a> if you have problems logging in.</p>

            </td>
        

        <td valign="top" width="50%">
        <p class=sidemenutitle style="font-size: 125%">External user login</p>
   

 <p>If you are not a University of Waikato staff or student, you can use the external user login link below.<p/>
 
 
 <p><a href="<?php echo $CFG->wwwroot .'/login/index.php?manual=true'; ?>">External User Login</a></p>

<p>Please ensure you are familiar with the <a href="<?php echo $CFG->wwwroot .'/auth/waikcookie/technicalRequirements.html'; ?>" target="_blank">Technical Requirement</a> for Moodle to ensure the best learning experience.</p></td>


</tr>
</table>
<br/>
<hr/>

        <div class='noprint'>
        <div class='pagefoot'>
        <table width='730' border='0' cellpadding='0' cellspacing='0'  align='center'>
        <tr>
            <td align='left'> <a href='http://www.waikato.ac.nz/copyright.shtml' class='fine'>COPYRIGHT AND LEGAL STATEMENT</a> 
            | <a href='http://www.waikato.ac.nz/contacts/' class='fine'>ADDRESS</a> 
            | <A HREF='mailto:webmaster@waikato.ac.nz' class='fine'>WEBMASTER</A>  
            | <a href='http://www.waikato.ac.nz/cgi-bin/contact.cgi' class='fine'>CONTACT AUTHOR</a></td>

            <td align='right'><a href='#pageopen' class='fine'><img src='http://cookie.waikato.ac.nz/images/btt.gif' alt='BACK TO TOP' width='85' height='17' border='0'></a></td>
        </tr>
        </table>
        </div></div>
        <p class='fine'>
        <em>The University of Waikato - <span lang='mi'>Te Whare Wananga o Waikato</span></em>
        </p> 
        <div class='printerOnly'>

        <p class='fine'>Page Generated: Fri Nov  9 15:45:02 2007<br>
URL: /cgi-bin/WCLogin<br>
This page has been reformatted for printing</p>
</div>

</td>
</tr>
</table>
</body>
</html>
