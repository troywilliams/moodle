<?php

    mtrace("Running UoW jobs (check the logs for details) ...");
    require_once($CFG->dirroot.'/local/cronjob/lib.php');
    cronjob_cron();
