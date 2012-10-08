<?php
/**
 * Cronjob for Panopto block
 */
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot .'/local/logger/logger.php');
require_once($CFG->dirroot.'/blocks/moodleblock.class.php');
require_once($CFG->dirroot.'/blocks/panopto/block_panopto.php');

require_login();
$syscontext = context_system::instance();
require_capability('moodle/site:config', $syscontext);
$block = new block_panopto();
$logger =& logger_get_logger('Panopto', 'FolderSync');
$logger->info('Processing Panopto folders...');
$block->full_user_list_sync();
$logger->info('Done.');
