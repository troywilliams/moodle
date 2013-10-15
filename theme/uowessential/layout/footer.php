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
 * @package   theme_uowessential
 * @author     Teresa Gibbison (The University of Waikato)
 * @author     Based on 'Essential' theme, written by Julian (@moodleman) Ridden
 * @author     which in turn is based on code originally written by G J Bernard, Mary Evans, Bas Brands, Stuart Lamour and David Scotson.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
  

$footerl = 'footer-left';
$footerm = 'footer-middle';
$footerr = 'footer-right';
$hasfooterleft = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('footer-left', $OUTPUT));
$hasfootermiddle = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('footer-middle', $OUTPUT));
$hasfooterright = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('footer-right', $OUTPUT));
*/
$hascopyright = (empty($PAGE->theme->settings->copyright)) ? false : $PAGE->theme->settings->copyright;
$hasfootnote = (empty($PAGE->theme->settings->footnote)) ? false : $PAGE->theme->settings->footnote;

?>
<div class="row-fluid">
    <div class="span3">
        <div class="landesk">Find help via the <a href="online.waikato.ac.nz/wcel/services/moodle/help/" title="Link to Moodle help files" target="_blank">Moodle help files</a> or<br />
            <a href="https://landesk.waikato.ac.nz/" title="Log a job with the University Helpdesk" target="_blank">log a job with the Helpdesk</a>
        </div>
    </div>
    <div class="span3">
        <a class="getintouch" href="http://www.waikato.ac.nz/contacts" title="Contact the University of Waikato" target="_blank">Get in Touch with Us:</a>
    </div>
    <div class="span3">
        <div class="country">In New Zealand<br/>
            <strong> 0800 WAIKATO</strong>
        </div>
    </div>
    <div class="span3">
        <div class="country">International<br/>
            <strong>+64 7 856 2889</strong>
        </div>
    </div>
 </div>

	<div class="footerlinks row-fluid">
    	
 <?php if ($hascopyright) {
        echo '<p class="copy">&copy; '.date("Y").' '.$hascopyright.'</p>';
    } ?>
    
    <?php if ($hasfootnote) {
        echo '<div class="footnote">'.$hasfootnote.'</div>';
    } ?>
	</div>
	

