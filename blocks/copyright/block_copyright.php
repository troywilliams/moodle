<?php 
class block_copyright extends block_base {
  function init() {
    $this->title   = get_string('copyright', 'block_copyright');
	}

function get_content() {
    if ($this->content !== NULL) {
      return $this->content;
    }
 
    $this->content         =  new stdClass;
    $this->content->text   = 'The resources within this Moodle paper may be used only for the University\'s educational purposes.  Some resources may include 
	extracts of copyright works copied under copyright licences. You may not copy or distribute any of these resources to any other person. As these resources have 
	been provided to you in electronic form you may only print them for your own use. You may not make a further copy of any of these resource for any other purpose. 
	Failure to comply with the terms of this warning may expose you to legal action for copyright infringement and/or disciplinary action by the University.';
    return $this->content;
  }
  
function has_config() {return false;}
}   // Here's the closing curly bracket for the class definition
    // and here's the closing PHP tag from the section above.
?>