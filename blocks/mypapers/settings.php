<?php 

$settings->add(new admin_setting_configtext('block_mypapers_url', get_string('mypapersurl', 'block_mypapers'),
                   null, 'http://framework-prod.its.waikato.ac.nz/MyPapers/MyPapers.php', PARAM_URL, 70));

$options = array(0 => 'none', 1 => '1 hour', 3 => '3 hours', 6 => '6 hours', 12 => '12 hours');

$settings->add(new admin_setting_configselect('block_mypapers_cache_lifetime', get_string('cache_lifetime', 'block_mypapers'),
                   null, 0, $options));

?>