<?php

    define('CLI_SCRIPT', true);
    require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
    require_once($CFG->dirroot.'/local/cronjob/lib.php');
    /// !Important: emulate normal session
    cron_setup_user();
    
    mtrace("Running UoW jobs in manual mode (check the logs for details) ...");

    if (!$cronlist = $DB->get_records('uow_cronjob', null, 'nextrun ASC')) {
        return;
    }
    
    foreach ($cronlist as $cron) {
        echo 'Job:'.$cron->id.':'. $cron->name.':'.$cron->filepath."\n";
        cronjob_run_job($cron->id, $cron, true); // manual run.
    }
    
 ?>
