<?php
 
/**
 * This block 
 */
 
class block_mypapers extends block_list {

    function init() {
        $this->title = get_string('title', 'block_mypapers');
    }

    function get_content() {
        global $CFG, $USER, $DB;

        $content = NULL;
        $currentyear = date('Y', time());
        
        if (!function_exists('curl_init') ) {
            error_log(get_string('nocurl', 'mnet', '', NULL, true));
            return false;
        }
       
        if (empty($CFG->block_mypapers_url)) {
            print_error('MyPapers URL not defined!');
        }

        /*if ($this->content !== NULL) {
            return $this->content;
        }*/

        if (!empty($CFG->block_mypapers_cache_lifetime)) { // using cache.
            $cache = $DB->get_record('block_mypapers_cache', array('userid'=>$USER->id));
            if (empty($cache)) {
                $cache = new object();
                $cache->userid = $USER->id;
                $this->content = $this->harvest_from_html();
                $cache->content =  serialize($this->content);
                $cache->timecached = time();
                $cache->id = $DB->insert_record('block_mypapers_cache', $cache);
                if (empty($cache->id)) {
                   print_error('failed insert');
                }
            }
            // Do we need a fresh copy of content or use cache, based on config setting.
            if (time() > ($cache->timecached + ($CFG->block_mypapers_cache_lifetime * 60 * 60 ))) {

                if (debugging()) {
                    //trigger_error("block : mypapers : getting fresh copy of content from framework server and storing to cache", E_USER_NOTICE);
                }

                $this->content = $this->harvest_from_html();
                $cache->content =  serialize($this->content);
                $cache->timecached = time();
                if (!$DB->update_record('block_mypapers_cache', $cache)) {
                    print_error('failed update');
                }
            } else {

                if (debugging()) {
                    //trigger_error("block : mypapers : using cached copy of content from moodle", E_USER_NOTICE);
                }
                $this->content = unserialize($cache->content); // use cached content.
            }
            return $this->content;
        }
    
        if (debugging()) {
            //trigger_error("block : mypapers : getting fresh copy direct from framework server", E_USER_NOTICE);
        }
        return $this->content = $this->harvest_from_html(); // get content directly from framework server and return.
    }

    function instance_allow_config() {
        return true;
    }

    function has_config() {
        return true;
    }

    function harvest_from_html(){
        global $CFG, $USER, $OUTPUT;

        $icon  = '<img src="' . $OUTPUT->pix_url('i/course') . '" class="icon" alt="" />&nbsp;';

        $content = new stdClass;
        $content->items = array();
        $content->icons = array();
        $content->footer = '';

        $url = null;
        // Test settings
        if (isset($CFG->block_mypapers_testurl)) {
            $username = isset($CFG->block_mypapers_testusername) ? $CFG->block_mypapers_testusername : $USER->username;
            $year = isset($CFG->block_mypapers_testyear) ? $CFG->block_mypapers_testyear : date('Y', time());
            $url = $CFG->block_mypapers_testurl . '?username='
                    . $username
                    . '&enryear='
                    . $year;

        // Production
        } else {
            $url = $CFG->block_mypapers_url . '?username='
                    . $USER->username
                    . '&enryear='
                    . date('Y', time());
        }
        //echo '<strong>'.$url.'</strong>';
        
        // get WaikCookie, we need to pass this through curl.
        $wcookie = $_COOKIE['WaikCookie'];
        // Using cURL to fetch html ul from url.
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_COOKIE, "WaikCookie=$wcookie");
        $html = curl_exec($ch);
        curl_close($ch);

        // Need to rip hrefs and label text from inside each li.
        $pattern = '/<a href=\'(.*)\'>(.*)<\/a>/iU';
        if (! preg_match_all($pattern, $html, $courses)) {

            $content->items[] = $html; // NO courses!
            $content->icons[] = '';

        } else {
            // Cycle matches to create content object;
            for ($i = 0; $i < count($courses[0]); $i++) {
                $label = $courses[2][$i];
                $link = $courses[1][$i];
                $content->items[] = "<a title=\"" . format_string($label) . "\" ".
                               "href=\"$link\">" . format_string($label) . "</a>";
                $content->icons[] = $icon;
            }
        }
        return $content;

    }

}

?>