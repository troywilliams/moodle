<?php
require_once("lib/panopto_data.php");

class block_panopto extends block_base
{
	var $blockname = "panopto";
	
	// Set system properties of plugin.
	function init()
	{
            global $CFG;
            $this->blockname = get_class($this);
            $this->title = get_string('pluginname', $this->blockname);
        }

        // Block has global config (display "Settings" link on blocks admin page)
	function has_config()
	{
  		return true;
	}
        
        /**
        * Set the applicable formats for this block to all
        * @return array
        */
        function applicable_formats() {
            return array('site' => false, 'course' => true);
        }
    	
    // Block has per-instance config (display edit icon in block header)
	function instance_allow_config()
	{
		return true;
	}
	
	// Save per-instance config in custom table instead of mdl_block_instance configdata column
	function instance_config_save($data, $nolongerused = false) {
		global $COURSE;
		if (!empty($data->mappedfolder)) {

                //return panopto_data::set_panopto_course_id($COURSE->id, $data->panopto_course_publicid);
                return panopto_data::set_panopto_course_id($COURSE->id, $data->mappedfolder);
		} else {
			// If server is not set globally, there will be no other form values to push into config.
			return true;
		}
	}
        
        function instance_delete() {
            global $DB;
            return true;
        }
        
	// Generate HTML for block contents
	function get_content()
	{
		global $CFG, $COURSE, $USER;
		
                // context
                $context = get_context_instance(CONTEXT_COURSE, $COURSE->id);
                
		if ($this->content !== NULL)
        {
            return $this->content;
        }

        $this->content = new stdClass;
        
        // Construct the Panopto data proxy object
        $panopto_data = new panopto_data($COURSE->id);

        if(empty($panopto_data->servername) || empty($panopto_data->instancename) || empty($panopto_data->applicationkey))
        {
			$this->content->text = "Global block configuration is incomplete.<br /><br />Please contact your system administrator.";
			$this->content->footer = "";
			
        	return $this->content;
        }

        try
        {
            if(!$panopto_data->sessiongroup_id)
			{
				$this->content->text .= "No CourseCast course selected.";
			}
	        else
	        {
        		// Get course info from SOAP service.
	        	$course_info = $panopto_data->get_course();
		        // CourseCast course was deleted, or an exception was thrown while retrieving course data.
	        	if($course_info->Access == "Error")
		        {
		        	$this->content->text .= "<span class='error'>Error retrieving CourseCast course.</span>";
		        }
				else
				{
		        	// SSO form passes instance name in POST to keep URLs portable.
		        	$this->content->text .= "
		        		<form name='SSO' method='post'>
							<input type='hidden' name='instance' value='$panopto_data->instancename' />
						</form>";
		        	
		        	$this->content->text .= "<div><b>Live Sessions</b></div>";
					$live_sessions = $panopto_data->get_live_sessions();
			        if(!empty($live_sessions))
			        {
			        	$i = 0;
				        foreach($live_sessions as $live_session)
				        {
				        	// Alternate gray background for readability.
				        	$altClass = ($i % 2) ? "listItemAlt" : "";
				        	
				        	$live_session_display_name = s($live_session->Name);
					        $this->content->text .= "<div class='listItem $altClass'>
														$live_session_display_name
													 	<span class='nowrap'>
															[<a href='javascript:launchNotes(\"$live_session->LiveNotesURL\")'
																>take notes</a
													 	  	 >]";
							if($live_session->BroadcastViewerURL)
							{
						        $this->content->text .= "
	        											 	[<a href='$live_session->BroadcastViewerURL' onclick='return startSSO(this)'
														 		>watch live</a
													 	  	 >]";
							}
							$this->content->text .= "
												 	  	 </span>
													</div>";
							$i++;
						}
			        }
			        else
			        {
			        	$this->content->text .= "<div class='listItem'>No live sessions.</div>";
			        }
			        
		        	$this->content->text .= "<div class='sectionHeader'><b>Completed Recordings</b></div>";
			        $completed_deliveries = $panopto_data->get_completed_deliveries();
			        if(!empty($completed_deliveries))
			        {
			        	$i = 0;
			        	foreach($completed_deliveries as $completed_delivery)
			        	{
			        		// Collapse to 3 lectures by default
			        		if($i == 3)
			        		{
			        			$this->content->text .= "<div id='hiddenLecturesDiv'>";
			        		}
        		        	
			        		// Alternate gray background for readability.
				        	$altClass = ($i % 2) ? "listItemAlt" : "";
			        		
			        		$completed_delivery_display_name = s($completed_delivery->DisplayName); 
					        $this->content->text .= "<div class='listItem $altClass'>
					        							<a href='$completed_delivery->ViewerURL' onclick='return startSSO(this)'>
				        									$completed_delivery_display_name
					        							</a>
				        							</div>";
        					$i++;
			        	}
			        	
			        	// If some lectures are hidden, display "Show all" link.
			        	if($i > 3)
			        	{
			        		$this->content->text .= "</div>";
			        		$this->content->text .= "<div id='showAllDiv'>";
			        		$this->content->text .= "[<a id='showAllToggle' href='javascript:toggleHiddenLectures()'>show all</a>]";
			        		$this->content->text .= "</div>";
			        	}
			        }
			        else
			        {
			        	$this->content->text .= "<div class='listItem'>No completed recordings.</div>";
			        }
			        
			        if($course_info->AudioPodcastURL)
			        {
				        $this->content->text .= "<div class='sectionHeader'><b>Podcast Feeds</b></div>
				        						 <div class='listItem'>
				        						 	<img src='$CFG->wwwroot/blocks/panopto/images/feed_icon.gif' />
				        							<a href='$course_info->AudioPodcastURL'>Audio Podcast</a>
				        							<span class='rssParen'>(</span
				        								><a href='$course_info->AudioRssURL' target='_blank' class='rssLink'>RSS</a
			        								><span class='rssParen'>)</span>
		                        				 </div>";
				        if($course_info->VideoPodcastURL)
				        {
				        	$this->content->text .= "
				        						 <div class='listItem'>
			        								<img src='$CFG->wwwroot/blocks/panopto/images/feed_icon.gif' />	
				        						 	<a href='$course_info->VideoPodcastURL'>Video Podcast</a>
				        							<span class='rssParen'>(</span
				        								><a href='$course_info->VideoRssURL' target='_blank' class='rssLink'>RSS</a
			        								><span class='rssParen'>)</span>
		                        				 </div>";
				        }
			        }                               
                                
			        if(has_capability('moodle/block:edit', $context))
			        {
				        $this->content->text .= "<div class='sectionHeader'><b>Links</b></div>
				        						 <div class='listItem'>
				        							<a href='$course_info->CourseSettingsURL' onclick='return startSSO(this)'
				        								>Course Settings</a>
			        							 </div>\n";
				        $system_info = $panopto_data->get_system_info();
				        $this->content->text .= "<div class='listItem'>
				        							Download Recorder
					        							<span class='nowrap'>
					        								(<a href='$system_info->RecorderDownloadUrl'>Windows</a>
								   							| <a href='$system_info->MacRecorderDownloadUrl'>Mac</a>)</span>
			        							</div>";
			        }
			        
					$this->content->text .= '
						<script type="text/javascript">
			                // Function to pop up CourseCast live note taker.
			                function launchNotes(url)
			        		{
								// Open empty notes window, then POST SSO form to it.
								var notesWindow = window.open("", "CourseCastNotes", "width=500,height=800,resizable=1,scrollbars=0,status=0,location=0");
								document.SSO.action = url;
								document.SSO.target = "CourseCastNotes";
								document.SSO.submit();
			
								// Ensure the new window is brought to the front of the z-order.
								notesWindow.focus();
							}
							
							function startSSO(linkElem)
							{
								document.SSO.action = linkElem.href;
								document.SSO.target = "_blank";
								document.SSO.submit();
								
								// Cancel default link navigation.
					  			return false;
					  		}
					  		
					  		function toggleHiddenLectures()
					  		{
					  			var showAllToggle = document.getElementById("showAllToggle");
					  			var hiddenLecturesDiv = document.getElementById("hiddenLecturesDiv");
					  			
					  			if(hiddenLecturesDiv.style.display == "block")
					  			{
					  				hiddenLecturesDiv.style.display = "none";
					  				showAllToggle.innerHTML = "show all";
					  			}
					  			else
								{
					  				hiddenLecturesDiv.style.display = "block";
					  				showAllToggle.innerHTML = "show less";
					  			}
					  		}
				    	</script>';
		        }
	        }
        }
        catch(Exception $e){
        	$this->content->text .= "<br><br><span class='error'>Error getting CourseCast course content.</span>";
        }
		$this->content->footer = '';

        return $this->content;
    }
    /**
     * Syncs a user list from Moodle to Panopto for particular course
     *
     * Nasty hack users will go through into public folders as well. Would be
     * nice to use new API 4.x
     *
     * @global object $DB
     * @param int $courseid
     * @return boolean sucess
     */
    function sync_user_list_for_courseid($courseid) {
        global $DB;

        $coursefoldermap = $DB->get_record('block_panopto_foldermap', array('moodleid'=>$courseid), '*', MUST_EXIST);
        $courseexists = $DB->record_exists('course', array('id'=>$courseid));
        if (!$courseexists) {
            mtrace('[courseid#'.$courseid.'] does not exist, deleting');
            $DB->delete_records('block_panopto_foldermap', array('moodleid'=>$courseid));
            return false;
        }
        $coursecontext = context_course::instance($courseid);
        // check if block on exists
        $params = array('blockname'=>'panopto', 'parentcontextid'=>$coursecontext->id);
        if (!$DB->record_exists('block_instances', $params)) {
            mtrace('[courseid#'.$courseid.'] block deleted');
            $DB->set_field('block_panopto_foldermap', 'syncuserlist', 0, array('moodleid'=>$courseid));
            return;
        }

        $mappings = $DB->get_records('block_panopto_foldermap', array('panopto_id'=>$coursefoldermap->panopto_id), 'id ASC');
        $master = array_shift($mappings); // pulling first created record, only way.

        $panoptodata = new panopto_data($master->moodleid);
        $provisioninginfo = $panoptodata->get_provisioning_info();

        mtrace('[courseid#'.$coursefoldermap->moodleid.']'.
               '[master courseid#'.$master->moodleid.']'.
               '{'.$coursefoldermap->panopto_id.'}'.
               ' '.$provisioninginfo->LongName);

        $provisioneddata = $panoptodata->provision_course($provisioninginfo);
        if (empty($provisioneddata)) {
            mtrace("ERROR: could not sync users to panopto folder: ".$panoptocourse->PublicID);
            return false;
        } else {
            $DB->set_field('block_panopto_foldermap', 'syncuserlist', 0, array('moodleid'=>$courseid));
            if (CLI_SCRIPT) {
                mtrace('creators');
                foreach($provisioninginfo->Instructors as $staff) {
                    mtrace($staff->UserKey);
                }
                mtrace('viewers');
                foreach($provisioninginfo->Students as $student) {
                    mtrace($student->UserKey);
                }
            }
        }
        return true;
    }
    /**
     * This function will do sync all courses that have been marked for a
     * usersync. The eventslib is used to mark a course for usersync via
     * user_enrolled and user_unenrolled events. Can force full sync on
     * all folders using $fullforce param.
     *
     * This function replaces the cron.
     *
     * Should be called by cli script or cronjob script.
     *
     * @global object $DB
     * @param boolean $fullforce - all
     * @return boolean
     */
    function full_user_list_sync($fullforce=false) {
        global $DB;
        $params = array();
        if (!$fullforce) {
            $params = array('syncuserlist'=>1);
        }
        $mappings = $DB->get_records('block_panopto_foldermap', $params);
        foreach ($mappings as $mapping) {
            $this->sync_user_list_for_courseid($mapping->moodleid);
        }
        return true;
    }
    /**
     * Cron always returns true.
     * @return boolean
     */
    function cron() {
        return true;
    }
}

?>