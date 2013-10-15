<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This is built using the Clean template to allow for new theme's using
 * Moodle's new Bootstrap theme engine
 *
 *
 * @package   theme_uowessential based on theme_essential
 * @copyright 2013 Julian Ridden
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$settings = null;

defined('MOODLE_INTERNAL') || die;


	$ADMIN->add('themes', new admin_category('theme_uowessential', 'uowessential'));

	// "geneicsettings" settingpage
	$temp = new admin_settingpage('theme_uowessential_generic',  get_string('geneicsettings', 'theme_uowessential'));
	
	// Default Site icon setting.
    $name = 'theme_uowessential/siteicon';
    $title = get_string('siteicon', 'theme_uowessential');
    $description = get_string('siteicondesc', 'theme_uowessential');
    $default = 'laptop';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $temp->add($setting);
    
    // Include Awesome Font from Bootstrapcdn
    $name = 'theme_uowessential/bootstrapcdn';
    $title = get_string('bootstrapcdn', 'theme_uowessential');
    $description = get_string('bootstrapcdndesc', 'theme_uowessential');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
	
    // Logo file setting.
    $name = 'theme_uowessential/logo';
    $title = get_string('logo', 'theme_uowessential');
    $description = get_string('logodesc', 'theme_uowessential');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'logo');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // User picture in header setting.
    $name = 'theme_uowessential/headerprofilepic';
    $title = get_string('headerprofilepic', 'theme_uowessential');
    $description = get_string('headerprofilepicdesc', 'theme_uowessential');
    $default = true;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Custom or standard layout.
    $name = 'theme_uowessential/layout';
    $title = get_string('layout', 'theme_uowessential');
    $description = get_string('layoutdesc', 'theme_uowessential');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //Include the Editicons css rules
    $name = 'theme_uowessential/editicons';
    $title = get_string('editicons', 'theme_uowessential');
    $description = get_string('editiconsdesc', 'theme_uowessential');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $temp->add($setting);
    
    //Include the Autohide css rules
    $name = 'theme_uowessential/autohide';
    $visiblename = get_string('autohide', 'theme_uowessential');
    $title = get_string('autohide', 'theme_uowessential');
    $description = get_string('autohidedesc', 'theme_uowessential');
    $setting = new admin_setting_configcheckbox($name, $visiblename, $description, 0);
    $temp->add($setting);
    
    // Performance Information Display.
    $name = 'theme_uowessential/perfinfo';
    $title = get_string('perfinfo' , 'theme_uowessential');
    $description = get_string('perfinfodesc', 'theme_uowessential');
    $perf_max = get_string('perf_max', 'theme_uowessential');
    $perf_min = get_string('perf_min', 'theme_uowessential');
    $default = 'min';
    $choices = array('min'=>$perf_min, 'max'=>$perf_max);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Navbar Seperator.
    $name = 'theme_uowessential/navbarsep';
    $title = get_string('navbarsep' , 'theme_uowessential');
    $description = get_string('navbarsepdesc', 'theme_uowessential');
    $nav_thinbracket = get_string('nav_thinbracket', 'theme_uowessential');
    $nav_doublebracket = get_string('nav_doublebracket', 'theme_uowessential');
    $nav_thickbracket = get_string('nav_thickbracket', 'theme_uowessential');
    $nav_slash = get_string('nav_slash', 'theme_uowessential');
    $nav_pipe = get_string('nav_pipe', 'theme_uowessential');
    $dontdisplay = get_string('dontdisplay', 'theme_uowessential');
    $default = '/';
    $choices = array('/'=>$nav_slash, '\f105'=>$nav_thinbracket, '\f101'=>$nav_doublebracket, '\f054'=>$nav_thickbracket, '|'=>$nav_pipe);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Copyright setting.
    $name = 'theme_uowessential/copyright';
    $title = get_string('copyright', 'theme_uowessential');
    $description = get_string('copyrightdesc', 'theme_uowessential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $temp->add($setting);
    
    // Footnote setting.
    $name = 'theme_uowessential/footnote';
    $title = get_string('footnote', 'theme_uowessential');
    $description = get_string('footnotedesc', 'theme_uowessential');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Custom CSS file.
    $name = 'theme_uowessential/customcss';
    $title = get_string('customcss', 'theme_uowessential');
    $description = get_string('customcssdesc', 'theme_uowessential');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $ADMIN->add('theme_uowessential', $temp);
    
    /* Custom Menu Settings */
    $temp = new admin_settingpage('theme_uowessential_custommenu', get_string('custommenuheading', 'theme_uowessential'));
	            
    //This is the descriptor for the following Moodle color settings
    $name = 'theme_uowessential/mydashboardinfo';
    $heading = get_string('mydashboardinfo', 'theme_uowessential');
    $information = get_string('mydashboardinfodesc', 'theme_uowessential');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Toggle dashboard display in custommenu.
    $name = 'theme_uowessential/displaymydashboard';
    $title = get_string('displaymydashboard', 'theme_uowessential');
    $description = get_string('displaymydashboarddesc', 'theme_uowessential');
    $default = true;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for the following Moodle color settings
    $name = 'theme_uowessential/mycoursesinfo';
    $heading = get_string('mycoursesinfo', 'theme_uowessential');
    $information = get_string('mycoursesinfodesc', 'theme_uowessential');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Toggle courses display in custommenu.
    $name = 'theme_uowessential/displaymycourses';
    $title = get_string('displaymycourses', 'theme_uowessential');
    $description = get_string('displaymycoursesdesc', 'theme_uowessential');
    $default = true;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Set terminology for dropdown course list
	$name = 'theme_uowessential/mycoursetitle';
	$title = get_string('mycoursetitle','theme_uowessential');
	$description = get_string('mycoursetitledesc', 'theme_uowessential');
	$default = 'course';
	$choices = array(
		'course' => get_string('mycourses', 'theme_uowessential'),
		'unit' => get_string('myunits', 'theme_uowessential'),
		'class' => get_string('myclasses', 'theme_uowessential'),
		'module' => get_string('mymodules', 'theme_uowessential')
	);
	$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
	$setting->set_updatedcallback('theme_reset_all_caches');
	$temp->add($setting);
    
    $ADMIN->add('theme_uowessential', $temp);
    
	/* Color Settings */
    $temp = new admin_settingpage('theme_uowessential_color', get_string('colorheading', 'theme_uowessential'));
    $temp->add(new admin_setting_heading('theme_uowessential_color', get_string('colorheadingsub', 'theme_uowessential'),
            format_text(get_string('colordesc' , 'theme_uowessential'), FORMAT_MARKDOWN)));

    // Background Image.
    $name = 'theme_uowessential/pagebackground';
    $title = get_string('pagebackground', 'theme_uowessential');
    $description = get_string('pagebackgrounddesc', 'theme_uowessential');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'pagebackground');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Main theme colour setting.
    $name = 'theme_uowessential/themecolor';
    $title = get_string('themecolor', 'theme_uowessential');
    $description = get_string('themecolordesc', 'theme_uowessential');
    $default = '#30add1';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Main theme Hover colour setting.
    $name = 'theme_uowessential/themehovercolor';
    $title = get_string('themehovercolor', 'theme_uowessential');
    $description = get_string('themehovercolordesc', 'theme_uowessential');
    $default = '#29a1c4';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Footer background colour setting.
    $name = 'theme_uowessential/footercolor';
    $title = get_string('footercolor', 'theme_uowessential');
    $description = get_string('footercolordesc', 'theme_uowessential');
    $default = '#000000';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Footer text colour setting.
    $name = 'theme_uowessential/footertextcolor';
    $title = get_string('footertextcolor', 'theme_uowessential');
    $description = get_string('footertextcolordesc', 'theme_uowessential');
    $default = '#DDDDDD';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Footer Block Heading colour setting.
    $name = 'theme_uowessential/footerheadingcolor';
    $title = get_string('footerheadingcolor', 'theme_uowessential');
    $description = get_string('footerheadingcolordesc', 'theme_uowessential');
    $default = '#CCCCCC';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Footer Seperator colour setting.
    $name = 'theme_uowessential/footersepcolor';
    $title = get_string('footersepcolor', 'theme_uowessential');
    $description = get_string('footersepcolordesc', 'theme_uowessential');
    $default = '#313131';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Footer URL colour setting.
    $name = 'theme_uowessential/footerurlcolor';
    $title = get_string('footerurlcolor', 'theme_uowessential');
    $description = get_string('footerurlcolordesc', 'theme_uowessential');
    $default = '#BBBBBB';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Footer URL hover colour setting.
    $name = 'theme_uowessential/footerhovercolor';
    $title = get_string('footerhovercolor', 'theme_uowessential');
    $description = get_string('footerhovercolordesc', 'theme_uowessential');
    $default = '#FFFFFF';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);



 	$ADMIN->add('theme_uowessential', $temp);
 
 
    /* Slideshow Widget Settings */
    $temp = new admin_settingpage('theme_uowessential_slideshow', get_string('slideshowheading', 'theme_uowessential'));
    $temp->add(new admin_setting_heading('theme_uowessential_slideshow', get_string('slideshowheadingsub', 'theme_uowessential'),
            format_text(get_string('slideshowdesc' , 'theme_uowessential'), FORMAT_MARKDOWN)));
    
    
    // Hide slideshow on phones.
    $name = 'theme_uowessential/hideonphone';
    $title = get_string('hideonphone' , 'theme_uowessential');
    $description = get_string('hideonphonedesc', 'theme_uowessential');
    $display = get_string('display', 'theme_uowessential');
    $dontdisplay = get_string('dontdisplay', 'theme_uowessential');
    $default = 'display';
    $choices = array(''=>$display, 'hidden-phone'=>$dontdisplay);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    /*
     * Slide 1
     */
     
    //This is the descriptor for Slide One
    $name = 'theme_uowessential/slide1info';
    $heading = get_string('slide1', 'theme_uowessential');
    $information = get_string('slideinfodesc', 'theme_uowessential');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);

    // Title.
    $name = 'theme_uowessential/slide1';
    $title = get_string('slidetitle', 'theme_uowessential');
    $description = get_string('slidetitledesc', 'theme_uowessential');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $default = '';
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Image.
    $name = 'theme_uowessential/slide1image';
    $title = get_string('slideimage', 'theme_uowessential');
    $description = get_string('slideimagedesc', 'theme_uowessential');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide1image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Caption.
    $name = 'theme_uowessential/slide1caption';
    $title = get_string('slidecaption', 'theme_uowessential');
    $description = get_string('slidecaptiondesc', 'theme_uowessential');
    $setting = new admin_setting_configtextarea($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // URL.
    $name = 'theme_uowessential/slide1url';
    $title = get_string('slideurl', 'theme_uowessential');
    $description = get_string('slideurldesc', 'theme_uowessential');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    /*
     * Slide 2
     */
     
    //This is the descriptor for Slide Two
    $name = 'theme_uowessential/slide2info';
    $heading = get_string('slide2', 'theme_uowessential');
    $information = get_string('slideinfodesc', 'theme_uowessential');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);

    // Title.
    $name = 'theme_uowessential/slide2';
    $title = get_string('slidetitle', 'theme_uowessential');
    $description = get_string('slidetitledesc', 'theme_uowessential');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $default = '';
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Image.
    $name = 'theme_uowessential/slide2image';
    $title = get_string('slideimage', 'theme_uowessential');
    $description = get_string('slideimagedesc', 'theme_uowessential');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide2image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Caption.
    $name = 'theme_uowessential/slide2caption';
    $title = get_string('slidecaption', 'theme_uowessential');
    $description = get_string('slidecaptiondesc', 'theme_uowessential');
    $setting = new admin_setting_configtextarea($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // URL.
    $name = 'theme_uowessential/slide2url';
    $title = get_string('slideurl', 'theme_uowessential');
    $description = get_string('slideurldesc', 'theme_uowessential');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    /*
     * Slide 3
     */

    //This is the descriptor for Slide Three
    $name = 'theme_uowessential/slide3info';
    $heading = get_string('slide3', 'theme_uowessential');
    $information = get_string('slideinfodesc', 'theme_uowessential');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Title.
    $name = 'theme_uowessential/slide3';
    $title = get_string('slidetitle', 'theme_uowessential');
    $description = get_string('slidetitledesc', 'theme_uowessential');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $default = '';
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Image.
    $name = 'theme_uowessential/slide3image';
    $title = get_string('slideimage', 'theme_uowessential');
    $description = get_string('slideimagedesc', 'theme_uowessential');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide3image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Caption.
    $name = 'theme_uowessential/slide3caption';
    $title = get_string('slidecaption', 'theme_uowessential');
    $description = get_string('slidecaptiondesc', 'theme_uowessential');
    $setting = new admin_setting_configtextarea($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // URL.
    $name = 'theme_uowessential/slide3url';
    $title = get_string('slideurl', 'theme_uowessential');
    $description = get_string('slideurldesc', 'theme_uowessential');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    /*
     * Slide 4
     */
     
    //This is the descriptor for Slide Four
    $name = 'theme_uowessential/slide4info';
    $heading = get_string('slide4', 'theme_uowessential');
    $information = get_string('slideinfodesc', 'theme_uowessential');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);

    // Title.
    $name = 'theme_uowessential/slide4';
    $title = get_string('slidetitle', 'theme_uowessential');
    $description = get_string('slidetitledesc', 'theme_uowessential');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $default = '';
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Image.
    $name = 'theme_uowessential/slide4image';
    $title = get_string('slideimage', 'theme_uowessential');
    $description = get_string('slideimagedesc', 'theme_uowessential');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide4image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Caption.
    $name = 'theme_uowessential/slide4caption';
    $title = get_string('slidecaption', 'theme_uowessential');
    $description = get_string('slidecaptiondesc', 'theme_uowessential');
    $setting = new admin_setting_configtextarea($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // URL.
    $name = 'theme_uowessential/slide4url';
    $title = get_string('slideurl', 'theme_uowessential');
    $description = get_string('slideurldesc', 'theme_uowessential');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    
    $ADMIN->add('theme_uowessential', $temp);
    
    $temp = new admin_settingpage('theme_uowessential_frontcontent', get_string('frontcontentheading', 'theme_uowessential'));
	$temp->add(new admin_setting_heading('theme_uowessential_frontcontent', get_string('frontcontentheadingsub', 'theme_uowessential'),
            format_text(get_string('frontcontentdesc' , 'theme_uowessential'), FORMAT_MARKDOWN)));
    
    // Enable Frontpage Content
    $name = 'theme_uowessential/usefrontcontent';
    $title = get_string('usefrontcontent', 'theme_uowessential');
    $description = get_string('usefrontcontentdesc', 'theme_uowessential');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Frontpage Content
    $name = 'theme_uowessential/frontcontentarea';
    $title = get_string('frontcontentarea', 'theme_uowessential');
    $description = get_string('frontcontentareadesc', 'theme_uowessential');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
        
    $ADMIN->add('theme_uowessential', $temp);
    

	/* Marketing Spot Settings */
	$temp = new admin_settingpage('theme_uowessential_marketing', get_string('marketingheading', 'theme_uowessential'));
	$temp->add(new admin_setting_heading('theme_uowessential_marketing', get_string('marketingheadingsub', 'theme_uowessential'),
            format_text(get_string('marketingdesc' , 'theme_uowessential'), FORMAT_MARKDOWN)));
	
	// Toggle Marketing Spots.
    $name = 'theme_uowessential/togglemarketing';
    $title = get_string('togglemarketing' , 'theme_uowessential');
    $description = get_string('togglemarketingdesc', 'theme_uowessential');
    $display = get_string('display', 'theme_uowessential');
    $dontdisplay = get_string('dontdisplay', 'theme_uowessential');
    $default = 'display';
    $choices = array('1'=>$display, '0'=>$dontdisplay);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Marketing Spot Image Height
	$name = 'theme_uowessential/marketingheight';
	$title = get_string('marketingheight','theme_uowessential');
	$description = get_string('marketingheightdesc', 'theme_uowessential');
	$default = 100;
	$choices = array(50, 100, 150, 200, 250, 300);
	$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
	$temp->add($setting);
	
	//This is the descriptor for Marketing Spot One
    $name = 'theme_uowessential/marketing1info';
    $heading = get_string('marketing1', 'theme_uowessential');
    $information = get_string('marketinginfodesc', 'theme_uowessential');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
	
	//Marketing Spot One.
	$name = 'theme_uowessential/marketing1';
    $title = get_string('marketingtitle', 'theme_uowessential');
    $description = get_string('marketingtitledesc', 'theme_uowessential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_uowessential/marketing1icon';
    $title = get_string('marketingicon', 'theme_uowessential');
    $description = get_string('marketingicondesc', 'theme_uowessential');
    $default = 'star';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_uowessential/marketing1image';
    $title = get_string('marketingimage', 'theme_uowessential');
    $description = get_string('marketingimagedesc', 'theme_uowessential');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'marketing1image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_uowessential/marketing1content';
    $title = get_string('marketingcontent', 'theme_uowessential');
    $description = get_string('marketingcontentdesc', 'theme_uowessential');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_uowessential/marketing1buttontext';
    $title = get_string('marketingbuttontext', 'theme_uowessential');
    $description = get_string('marketingbuttontextdesc', 'theme_uowessential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_uowessential/marketing1buttonurl';
    $title = get_string('marketingbuttonurl', 'theme_uowessential');
    $description = get_string('marketingbuttonurldesc', 'theme_uowessential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for Marketing Spot Two
    $name = 'theme_uowessential/marketing2info';
    $heading = get_string('marketing2', 'theme_uowessential');
    $information = get_string('marketinginfodesc', 'theme_uowessential');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    //Marketing Spot Two.
	$name = 'theme_uowessential/marketing2';
    $title = get_string('marketingtitle', 'theme_uowessential');
    $description = get_string('marketingtitledesc', 'theme_uowessential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_uowessential/marketing2icon';
    $title = get_string('marketingicon', 'theme_uowessential');
    $description = get_string('marketingicondesc', 'theme_uowessential');
    $default = 'star';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_uowessential/marketing2image';
    $title = get_string('marketingimage', 'theme_uowessential');
    $description = get_string('marketingimagedesc', 'theme_uowessential');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'marketing2image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_uowessential/marketing2content';
    $title = get_string('marketingcontent', 'theme_uowessential');
    $description = get_string('marketingcontentdesc', 'theme_uowessential');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_uowessential/marketing2buttontext';
    $title = get_string('marketingbuttontext', 'theme_uowessential');
    $description = get_string('marketingbuttontextdesc', 'theme_uowessential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_uowessential/marketing2buttonurl';
    $title = get_string('marketingbuttonurl', 'theme_uowessential');
    $description = get_string('marketingbuttonurldesc', 'theme_uowessential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for Marketing Spot Three
    $name = 'theme_uowessential/marketing3info';
    $heading = get_string('marketing3', 'theme_uowessential');
    $information = get_string('marketinginfodesc', 'theme_uowessential');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    //Marketing Spot Three.
	$name = 'theme_uowessential/marketing3';
    $title = get_string('marketingtitle', 'theme_uowessential');
    $description = get_string('marketingtitledesc', 'theme_uowessential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_uowessential/marketing3icon';
    $title = get_string('marketingicon', 'theme_uowessential');
    $description = get_string('marketingicondesc', 'theme_uowessential');
    $default = 'star';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_uowessential/marketing3image';
    $title = get_string('marketingimage', 'theme_uowessential');
    $description = get_string('marketingimagedesc', 'theme_uowessential');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'marketing3image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_uowessential/marketing3content';
    $title = get_string('marketingcontent', 'theme_uowessential');
    $description = get_string('marketingcontentdesc', 'theme_uowessential');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_uowessential/marketing3buttontext';
    $title = get_string('marketingbuttontext', 'theme_uowessential');
    $description = get_string('marketingbuttontextdesc', 'theme_uowessential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_uowessential/marketing3buttonurl';
    $title = get_string('marketingbuttonurl', 'theme_uowessential');
    $description = get_string('marketingbuttonurldesc', 'theme_uowessential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    
    $ADMIN->add('theme_uowessential', $temp);

	
	/* Social Network Settings */
	$temp = new admin_settingpage('theme_uowessential_social', get_string('socialheading', 'theme_uowessential'));
	$temp->add(new admin_setting_heading('theme_uowessential_social', get_string('socialheadingsub', 'theme_uowessential'),
            format_text(get_string('socialdesc' , 'theme_uowessential'), FORMAT_MARKDOWN)));
	
    // Website url setting.
    $name = 'theme_uowessential/website';
    $title = get_string('website', 'theme_uowessential');
    $description = get_string('websitedesc', 'theme_uowessential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Facebook url setting.
    $name = 'theme_uowessential/facebook';
    $title = get_string('facebook', 'theme_uowessential');
    $description = get_string('facebookdesc', 'theme_uowessential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Flickr url setting.
    $name = 'theme_uowessential/flickr';
    $title = get_string('flickr', 'theme_uowessential');
    $description = get_string('flickrdesc', 'theme_uowessential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Twitter url setting.
    $name = 'theme_uowessential/twitter';
    $title = get_string('twitter', 'theme_uowessential');
    $description = get_string('twitterdesc', 'theme_uowessential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Google+ url setting.
    $name = 'theme_uowessential/googleplus';
    $title = get_string('googleplus', 'theme_uowessential');
    $description = get_string('googleplusdesc', 'theme_uowessential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // LinkedIn url setting.
    $name = 'theme_uowessential/linkedin';
    $title = get_string('linkedin', 'theme_uowessential');
    $description = get_string('linkedindesc', 'theme_uowessential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Pinterest url setting.
    $name = 'theme_uowessential/pinterest';
    $title = get_string('pinterest', 'theme_uowessential');
    $description = get_string('pinterestdesc', 'theme_uowessential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Instagram url setting.
    $name = 'theme_uowessential/instagram';
    $title = get_string('instagram', 'theme_uowessential');
    $description = get_string('instagramdesc', 'theme_uowessential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // YouTube url setting.
    $name = 'theme_uowessential/youtube';
    $title = get_string('youtube', 'theme_uowessential');
    $description = get_string('youtubedesc', 'theme_uowessential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Skype url setting.
    $name = 'theme_uowessential/skype';
    $title = get_string('skype', 'theme_uowessential');
    $description = get_string('skypedesc', 'theme_uowessential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
 
    // VKontakte url setting.
    $name = 'theme_uowessential/vk';
    $title = get_string('vk', 'theme_uowessential');
    $description = get_string('vkdesc', 'theme_uowessential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting); 
    
    $ADMIN->add('theme_uowessential', $temp);
    
    $temp = new admin_settingpage('theme_uowessential_mobileapps', get_string('mobileappsheading', 'theme_uowessential'));
	$temp->add(new admin_setting_heading('theme_uowessential_mobileapps', get_string('mobileappsheadingsub', 'theme_uowessential'),
            format_text(get_string('mobileappsdesc' , 'theme_uowessential'), FORMAT_MARKDOWN)));
    // Android App url setting.
    $name = 'theme_uowessential/android';
    $title = get_string('android', 'theme_uowessential');
    $description = get_string('androiddesc', 'theme_uowessential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // iOS App url setting.
    $name = 'theme_uowessential/ios';
    $title = get_string('ios', 'theme_uowessential');
    $description = get_string('iosdesc', 'theme_uowessential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for iOS Icons
    $name = 'theme_uowessential/iosiconinfo';
    $heading = get_string('iosicon', 'theme_uowessential');
    $information = get_string('iosicondesc', 'theme_uowessential');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // iPhone Icon.
    $name = 'theme_uowessential/iphoneicon';
    $title = get_string('iphoneicon', 'theme_uowessential');
    $description = get_string('iphoneicondesc', 'theme_uowessential');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'iphoneicon');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // iPhone Retina Icon.
    $name = 'theme_uowessential/iphoneretinaicon';
    $title = get_string('iphoneretinaicon', 'theme_uowessential');
    $description = get_string('iphoneretinaicondesc', 'theme_uowessential');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'iphoneretinaicon');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // iPad Icon.
    $name = 'theme_uowessential/ipadicon';
    $title = get_string('ipadicon', 'theme_uowessential');
    $description = get_string('ipadicondesc', 'theme_uowessential');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'ipadicon');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // iPad Retina Icon.
    $name = 'theme_uowessential/ipadretinaicon';
    $title = get_string('ipadretinaicon', 'theme_uowessential');
    $description = get_string('ipadretinaicondesc', 'theme_uowessential');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'ipadretinaicon');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $ADMIN->add('theme_uowessential', $temp);
    
    /* User Alerts */
    $temp = new admin_settingpage('theme_uowessential_alerts', get_string('alertsheading', 'theme_uowessential'));
	$temp->add(new admin_setting_heading('theme_uowessential_alerts', get_string('alertsheadingsub', 'theme_uowessential'),
            format_text(get_string('alertsdesc' , 'theme_uowessential'), FORMAT_MARKDOWN)));
    
    //This is the descriptor for Alert One
    $name = 'theme_uowessential/alert1info';
    $heading = get_string('alert1', 'theme_uowessential');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Enable Alert
    $name = 'theme_uowessential/enable1alert';
    $title = get_string('enablealert', 'theme_uowessential');
    $description = get_string('enablealertdesc', 'theme_uowessential');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Type.
    $name = 'theme_uowessential/alert1type';
    $title = get_string('alerttype' , 'theme_uowessential');
    $description = get_string('alerttypedesc', 'theme_uowessential');
    $alert_info = get_string('alert_info', 'theme_uowessential');
    $alert_warning = get_string('alert_warning', 'theme_uowessential');
    $alert_general = get_string('alert_general', 'theme_uowessential');
    $default = 'info';
    $choices = array('info'=>$alert_info, 'error'=>$alert_warning, 'success'=>$alert_general);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Title.
    $name = 'theme_uowessential/alert1title';
    $title = get_string('alerttitle', 'theme_uowessential');
    $description = get_string('alerttitledesc', 'theme_uowessential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Text.
    $name = 'theme_uowessential/alert1text';
    $title = get_string('alerttext', 'theme_uowessential');
    $description = get_string('alerttextdesc', 'theme_uowessential');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for Alert Two
    $name = 'theme_uowessential/alert2info';
    $heading = get_string('alert2', 'theme_uowessential');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Enable Alert
    $name = 'theme_uowessential/enable2alert';
    $title = get_string('enablealert', 'theme_uowessential');
    $description = get_string('enablealertdesc', 'theme_uowessential');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Type.
    $name = 'theme_uowessential/alert2type';
    $title = get_string('alerttype' , 'theme_uowessential');
    $description = get_string('alerttypedesc', 'theme_uowessential');
    $alert_info = get_string('alert_info', 'theme_uowessential');
    $alert_warning = get_string('alert_warning', 'theme_uowessential');
    $alert_general = get_string('alert_general', 'theme_uowessential');
    $default = 'info';
    $choices = array('info'=>$alert_info, 'error'=>$alert_warning, 'success'=>$alert_general);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Title.
    $name = 'theme_uowessential/alert2title';
    $title = get_string('alerttitle', 'theme_uowessential');
    $description = get_string('alerttitledesc', 'theme_uowessential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Text.
    $name = 'theme_uowessential/alert2text';
    $title = get_string('alerttext', 'theme_uowessential');
    $description = get_string('alerttextdesc', 'theme_uowessential');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for Alert Three
    $name = 'theme_uowessential/alert3info';
    $heading = get_string('alert3', 'theme_uowessential');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Enable Alert
    $name = 'theme_uowessential/enable3alert';
    $title = get_string('enablealert', 'theme_uowessential');
    $description = get_string('enablealertdesc', 'theme_uowessential');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Type.
    $name = 'theme_uowessential/alert3type';
    $title = get_string('alerttype' , 'theme_uowessential');
    $description = get_string('alerttypedesc', 'theme_uowessential');
    $alert_info = get_string('alert_info', 'theme_uowessential');
    $alert_warning = get_string('alert_warning', 'theme_uowessential');
    $alert_general = get_string('alert_general', 'theme_uowessential');
    $default = 'info';
    $choices = array('info'=>$alert_info, 'error'=>$alert_warning, 'success'=>$alert_general);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Title.
    $name = 'theme_uowessential/alert3title';
    $title = get_string('alerttitle', 'theme_uowessential');
    $description = get_string('alerttitledesc', 'theme_uowessential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Text.
    $name = 'theme_uowessential/alert3text';
    $title = get_string('alerttext', 'theme_uowessential');
    $description = get_string('alerttextdesc', 'theme_uowessential');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
            
    
    $ADMIN->add('theme_uowessential', $temp);
    
    /* Analytics Settings */
    $temp = new admin_settingpage('theme_uowessential_analytics', get_string('analyticsheading', 'theme_uowessential'));
	$temp->add(new admin_setting_heading('theme_uowessential_analytics', get_string('analyticsheadingsub', 'theme_uowessential'),
            format_text(get_string('analyticsdesc' , 'theme_uowessential'), FORMAT_MARKDOWN)));
    
    // Enable Analytics
    $name = 'theme_uowessential/useanalytics';
    $title = get_string('useanalytics', 'theme_uowessential');
    $description = get_string('useanalyticsdesc', 'theme_uowessential');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Google Analytics ID
    $name = 'theme_uowessential/analyticsid';
    $title = get_string('analyticsid', 'theme_uowessential');
    $description = get_string('analyticsiddesc', 'theme_uowessential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Clean Analytics URL
    $name = 'theme_uowessential/analyticsclean';
    $title = get_string('analyticsclean', 'theme_uowessential');
    $description = get_string('analyticscleandesc', 'theme_uowessential');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
        
    $ADMIN->add('theme_uowessential', $temp);

