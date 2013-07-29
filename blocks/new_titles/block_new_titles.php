<?php
/**
 * This class is for a block which defines a block for display on any Moodle page.
 */
 class block_new_titles extends block_base {

    function init() {
        $this->title = get_string('defaultblocktitle', 'block_new_titles');   
    }
    function preferred_width() {
        return 210;
    }
    function has_config() {
        return true;
    }
    function instance_allow_config() {
        return true;
    }
    function instance_allow_multiple() {
        return false;
    }
    function get_content() {
        global $CFG, $DB;

        if ($this->content !== NULL) {
            return $this->content;
        }

        // initalise block content object
        $this->content = new stdClass;
        $this->content->text   = '';
        $this->content->footer = '';

        if (!$CFG->enablerssfeeds) {
            $this->content->text = '';
            if ($this->page->user_is_editing()) {
                $this->content->text = get_string('disabledrssfeeds', 'block_new_titles');
            }
            return $this->content;
        }

        // initalise block content object
        $this->content = new stdClass;
        $this->content->text   = '';
        $this->content->footer = '';

        if (empty($this->instance)) {
            return $this->content;
        }

        if (!isset($this->config)) {
            // The block has yet to be configured - just display configure message in
            // the block if user has permission to configure it

            if (has_capability('block/new_titles:managefeeds', $this->context)) {
                $this->content->text = get_string('feedsconfigurenewinstance2', 'block_new_titles');
            }

            return $this->content;
        }

        // How many feed items should we display?
        $maxentries = 5;
        if ( !empty($this->config->shownumentries) ) {
            $maxentries = intval($this->config->shownumentries);
        }

        /* ---------------------------------
         * Begin Normal Display of Block Content
         * --------------------------------- */

        $output = '';


        if (!empty($this->config->rssid)) {
            list($rss_ids_sql, $params) = $DB->get_in_or_equal($this->config->rssid);

            $rss_feeds = $DB->get_records_select('block_new_titles', "id $rss_ids_sql", $params);

            $showtitle = false;
            if (count($rss_feeds) > 1) {
                // when many feeds show the title for each feed
                $showtitle = true;
            }

            foreach($rss_feeds as $feed){
                $output.= $this->get_feed_html($feed, $maxentries, $showtitle);
            }
        }

        $this->content->text = $output;

        return $this->content;
    }
    
    /**
     * Returns the html of a feed to be displaed in the block
     *
     * @param mixed feedrecord The feed record from the database
     * @param int maxentries The maximum number of entries to be displayed
     * @param boolean showtitle Should the feed title be displayed in html
     * @return string html representing the rss feed content
     */
    function get_feed_html($feedrecord, $maxentries, $showtitle){
        global $CFG;
        require_once($CFG->libdir.'/simplepie/moodle_simplepie.php');

        $feed = new moodle_simplepie($feedrecord->url);

        if (isset($CFG->block_new_titles_cacheduration)) {
            $feed->set_cache_duration($CFG->block_new_titles_cacheduration);
        } 
        
        if(debugging() && $feed->error()){
            return '<p>'. $feedrecord->url .' Failed with code: '.$feed->error().'</p>';
        }

        $r = ''; // return string

        if(empty($feedrecord->preferredtitle)){
            $feedtitle = $this->format_title($feed->get_title());
        }else{
            $feedtitle = $this->format_title($feedrecord->preferredtitle);
        }

        if($showtitle){
            $r.='<div class="title">'.$feedtitle.'</div>';
        }


        $r.='<ul class="list no-overflow">'."\n";

        $feeditems = $feed->get_items(0, $maxentries);
        foreach($feeditems as $item){
            $r.= $this->get_item_html($item);
        }

        $r.='</ul>';

        if (empty($this->config->title)){
            //NOTE: this means the 'last feed' displayed wins the block title - but
            //this is exiting behaviour..
            $this->title = strip_tags($feedtitle);
        }

        return $r;
    }

    /**
     * Returns the html list item of a feed item
     *
     * @param mixed item simplepie_item representing the feed item
     * @return string html li representing the rss feed item
     */
    function get_item_html($item){

        $link        = $item->get_link();
        $title       = $item->get_title();
        $description = $item->get_description();


        if(empty($title)){
            // no title present, use portion of description
            $title = substr(strip_tags($description), 0, 20) . '...';
        }else{
            $title = break_up_long_words($title, 30);
        }

        if(empty($link)){
            $link = $item->get_id();
        } else {
            //URLs in our RSS cache will be escaped (correctly as theyre store in XML)
            //html_writer::link() will re-escape them. To prevent double escaping unescape here.
            //This can by done using htmlspecialchars_decode() but moodle_url also has that effect
            $link = new moodle_url($link);
        }

        $r = html_writer::start_tag('li');
            $r.= html_writer::start_tag('div',array('class'=>'link'));
                $r.= html_writer::link(clean_param($link,PARAM_URL), s($title), array('onclick'=>'this.target="_blank"'));
            $r.= html_writer::end_tag('div');

        $r.= html_writer::end_tag('li');

        return $r;
    }

    /**
     * Strips a large title to size and adds ... if title too long
     *
     * @param string title to shorten
     * @param int max character length of title
     * @return string title s() quoted and shortened if necessary
     */
    function format_title($title,$max=64) {

        if (textlib::strlen($title) <= $max) {
            return s($title);
        } else {
            return s(textlib::strlen($title,0,$max-3).'...');
        }
    }
    
    /**
     * cron - goes through all feeds and retrieves them with the cache
     * duration set to 0 in order to force the retrieval of the item and
     * refresh the cache
     *
     * @return boolean true if all feeds were retrieved succesfully
     */
    function cron($forced=false) {
        global $CFG, $DB;
        require_once($CFG->libdir.'/simplepie/moodle_simplepie.php');

        // We are going to measure execution times
        $starttime =  microtime();

        // And we have one initial $status
        $status = true;

        // Fetch all new title feeds.
        $rs = $DB->get_recordset('block_new_titles');
        $counter = 0;
        mtrace('');
        foreach ($rs as $rec) {
            mtrace('    ' . $rec->url . ' ', '');
            // Fetch the rss feed, using standard simplepie caching
            // so feeds will be renewed only if cache has expired
            @set_time_limit(60);

            $feed =  new moodle_simplepie();
            // set timeout for longer than normal to be agressive at
            // fetching feeds if possible..
            $feed->set_timeout(40);
            $feed->set_cache_duration(0);
            if (isset($CFG->block_new_titles_cacheduration) && !$forced) {
                $feed->set_cache_duration($CFG->block_new_titles_cacheduration);
            } 
            $feed->set_feed_url($rec->url);
            $feed->init();

            if ($feed->error()) {
                mtrace ('error');
                mtrace ('SimplePie failed with error:'.$feed->error());
                $status = false;
            } else {
                mtrace ('ok');
            }
            $counter ++;
        }
        $rs->close();

        // Show times
        mtrace($counter . ' feeds refreshed (took ' . microtime_diff($starttime, microtime()) . ' seconds)');

        // And return $status
        return $status;
    }
}
