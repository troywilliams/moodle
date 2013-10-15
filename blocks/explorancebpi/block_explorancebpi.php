<?php //$Id: block_explorancebpi.php,v 1.0 2012-01-12 11:00:00 fbotti Exp $

/**
 *
 * @package    moodlecore
 * @subpackage block
 * @copyright  2012 eXplorance inc.
 * @author     Jonathan Lapierre
 * @version    1.0
 */

class block_explorancebpi extends block_base {
//start customisation : adding as per docs.moodle.org/dev/Blocks ~ teresag
    function has_config() {return true;}
   
//end UOW customisation as for docs.moodle.org/dev/Blocks ~teresag
    function init() {
        $this->title = get_string('pluginname','block_explorancebpi');
    }

	  //Automatically called by Moodle as soon as our instance configuration is loaded and available (that is, immediately after init() is called)
		public function specialization() {
		}

    function instance_allow_multiple() {
        return true;
    }
		
    function get_content() {
        global $CFG, $OUTPUT, $USER, $URL2LOWER, $URLMODIFIED, $SUPPORTED, $LAUNCHERONLY, $BLOCKID, $BLOCKIDPOS, $BLOCKHEIGHT,$DefaultHeight,$DefaultHeightFB;
        
				$DefaultHeight = '290';//500
				$DefaultHeightFB = '135';//180
				
				$SUPPORTED = true;
				$LAUNCHERONLY = false;
				
        require_once($CFG->dirroot.'/message/lib.php');

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = '';

				//If the user is logged in we show the block
        if (isloggedin()) {
							
							//If the configuration is not done yet, we display a message stating that it needs to be done to be used.
							if (! empty($this->config->url))
							{
									//Get the URL and transform it to lower for later use
									$URL2LOWER = strtolower($this->config->url);

									//Depending on the link type, we will change the header title value for the user
									switch (! "") 
									{
									    case strpos($URL2LOWER,"reportview.aspx"):
									        $this->title.= ' - '.get_string('reportviewtitle','block_explorancebpi');
									        break;
									    case strpos($URL2LOWER,"taskview.aspx"):
									        $this->title.= ' - '.get_string('taskviewtitle','block_explorancebpi');
									        break;
									    case strpos($URL2LOWER,"launcher.aspx"):
									        $LAUNCHERONLY = true;
									        if(strpos($URL2LOWER,"feedback") != false){
									        	$this->title.= ' - '.get_string('feebackviewtitle','block_explorancebpi');
									        }else if(strpos($URL2LOWER,"subjectmanagement") != false){
									        	$this->title.= ' - '.get_string('subjectmanagementtitle','block_explorancebpi');
									        }else{									        	
									        }		
							        
									        break;
										case strpos($URL2LOWER,"fbview.aspx"):
											$this->title.= ' - '.get_string('feebackviewtitle','block_explorancebpi');
											break;
									    case strpos($URL2LOWER,"subjectview.aspx"): 
									        $this->title.= ' - '.get_string('subjectmanagementtitle','block_explorancebpi');
									        break;
											default:
											       $SUPPORTED = false;											       
											       $this->title.= ' - '.get_string('unsupported','block_explorancebpi');
									}

									//If the link is supported, we proceed with the block layout
									if ($SUPPORTED)
									{       
                             if(strpos($URL2LOWER,"subjectview") != false){
                             
									        	      //replace the &userid= with &userid=ACTUALUSER.                                  
                                  $URLMODIFIED =  str_ireplace("&userid=", "&userid=".randomizeQueryString($USER->username), $this->config->url);
                                  
									            }elseif (strpos($URL2LOWER,"fbview") != false){
                              
                                  $URLMODIFIED =  str_ireplace("&userid=", "&userid=".randomizeQueryString($USER->username), $this->config->url);
                                  
                              }else{
                                                                                
                                   //replace the &userid= with &userid=ACTUALUSER.
                                   $URLMODIFIED =  str_ireplace("&userid=", "&userid=".randomizeQueryString($USER->username), $this->config->url);
                                   
                               }
										
	
										//replace the &lng= with &lng=ACTUALCULTURE (FOR FEEDBACK)
										$URLMODIFIED =  str_ireplace("&lng=", "&lng=".$USER->lang, $URLMODIFIED);
	
										//replace the &culture= with &lng=ACTUALCULTURE (FOR REPORT AND TASKS)
										$URLMODIFIED =  str_ireplace("&culture=", "&culture=".$USER->lang, $URLMODIFIED);
	
										//replace the &firstname= with &firstname=ACTUALUSERFN (FOR REPORT AND TASKS)
										$URLMODIFIED =  str_ireplace("&firstname=", "&firstname=".$USER->firstname, $URLMODIFIED);
	
										//replace the &lastname= with &lastname=ACTUALUSERLN (FOR REPORT AND TASKS)
										$URLMODIFIED =  str_ireplace("&lastname=", "&lastname=".$USER->lastname, $URLMODIFIED);
	
										//Define mar
										$BLOCKIDPOS = strpos($URLMODIFIED,"blockid");
										$ArrBlockID = array();
										$ArrBlockID = split("&",substr($URLMODIFIED, $BLOCKIDPOS+8,1000),5);
										$BLOCKID = $ArrBlockID[0];										
										
										//Si il s'agit d'un launcher seulement nous affichons un texte descriptif et un bouton afin d'aller a la page applicative.
										if ($LAUNCHERONLY)
										{																					
											$URLMODIFIED =  str_ireplace("fbview", "launcher", $URLMODIFIED);
									  }
									  
									  //If config-height is empty, we put the default heigth constant
										if($LAUNCHERONLY) { $BLOCKHEIGHT = $DefaultHeightFB; } else { $BLOCKHEIGHT = $DefaultHeight; }
										
			 						  if (is_numeric($this->config->height))
										{
										 		$BLOCKHEIGHT = $this->config->height;
										}

			  						$this->content->text.= "<div style=\"\" class=\"userinfoblock\"><iframe frameBorder=\"0\" name=\"name_bpiframe_".$BLOCKID."\" id=\"id_bpiframe_".$BLOCKID."\" src=\"".$URLMODIFIED."\" style=\"border: 0px solid #333;background-color:#FFF;\" width=\"100%\" height=\"".$BLOCKHEIGHT."\"></iframe></div>";																						
			            }                                                                                                                                                                                                                                               
			            else
			            {
			            	$this->content->text.= "<div style=\"color: #FF3333;\">".get_string('unsupported','block_explorancebpi')."</div>";
			            }

							}
							else
							{
									$this->content->text.= "<div style=\"color: #FF3333;\">".get_string('notconfigured','block_explorancebpi')."</div>";
							}
        }
        

        return $this->content;
    }

}


//+--------------------------------------------------------------------
    //| e n c r y p t N u m b e r s 
    //+--------------------------------------------------------------------
    // Auteur: Yannick Bacon Villemaire
    // Compagnie: Thoran inc. 
    // Date: 20/03/2012
    // 
    /// <summary>
    /// Encrypt un nombre en ajoutant une suite de 3 random numbers et de 4 random numbers en avant de chaque chiffre du nombre
    /// 
    /// </summary>
    /// <param name="num">ex: 12</param>
    /// <returns>ex: 321 1 4321 2 (sans les espaces)</returns>

    function randomizeQueryString($num){
        $numstr = array();
        $nbr = strlen($num);
        $outstr = "";
        
        for($i = 0; $i < $nbr; $i++){
            $numstr[$i] = substr($num, $i, 1);
        }
        
        for($j = 0; $j < $nbr; $j++){
            //even number - on ajoute un random numer de 3 chiffres en avant
            if ($j % 2 == 0){
                $outstr .= getRandomURLStringBySize(3).encryptString($numstr[$j]);
            }
            //odd number - on ajoute un random numer de 4 chiffres en avant
            else
            {
                $outstr .= getRandomURLStringBySize(4).encryptString($numstr[$j]);
            }
        }
        return $outstr;
    }

    
    //+--------------------------------------------------------------------
    //| g e t R a n d o m S t r i n g B y S i z e 
    //+--------------------------------------------------------------------
    // Auteur: Yannick Bacon Villemaire
    // Compagnie: Thoran inc. 
    // Date: 20/03/2012
    // 
    /// <summary>
    /// Génère un string à partir d'une série de charactères préalablement définis
    /// 
    /// </summary>
    /// <param name="randompool">string qui contient les random characters qui pourraient apparaitre dans le random string</param>
    /// <param name="size">la taille du string</param>
    /// <returns></returns>
    function getRandomStringBySize($randompool, $size){

        $str = "";
        
        for($i = 0; $i < $size; $i++){
            //randomizer - le seed est différent à chaque itération
            usleep(10000);
            
            //string.charAt(random.next(0, string.length));     //obtient un random character dans randompool
            $str .= $randompool[rand(0, (strlen($randompool) - 1))];
        }
        
        return $str;
    }
    
    
    //+--------------------------------------------------------------------
    //| g e t R a n d o m S u b j e c t V i e w S t r i n g B y S i z e 
    //+--------------------------------------------------------------------
    // Auteur: Yannick Bacon Villemaire
    // Compagnie: Thoran inc. 
    // Date: 20/03/2012
    // 
    /// <summary>
    /// Function qui définit le random pool characters pour la function getRandomStringBySize.
    /// Ce random pool est utilisé pour le URL randomizer des blocks
    /// </summary>
    /// <param name="size"></param>
    /// <returns></returns>
    function getRandomURLStringBySize($size)
    {
        $randompool = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-0123456789";
        return getRandomStringBySize($randompool, $size);
    }
    
    
    //+--------------------------------------------------------------------
    //| e n c r y p t S t r i n g 
    //+--------------------------------------------------------------------
    // Auteur: Yannick Bacon Villemaire
    // Compagnie: Thoran inc. 
    // Date: 21/03/2012
    // 
    /// <summary>
    /// Function d'encryption qui map un string à un set de string prédéfini 
    /// 
    /// </summary>
    /// <param name="c"></param>
    /// <returns></returns>
    function encryptString($c){
        $characterspool = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-0123456789";
        $mapping = "iGFvTSI32oefCBrQP9-_lbcyWXLK6YVjADsROEz5mndxwqUN810hkauHZMJ74tgp";
        
        return mappingChar($c, $characterspool, $mapping);
    }
    
    
    //+--------------------------------------------------------------------
    //| m a p p i n g C h a r 
    //+--------------------------------------------------------------------
    // Auteur: Yannick Bacon Villemaire
    // Compagnie: Thoran inc. 
    // Date: 21/03/2012
    // 
    /// <summary>
    /// Function qui map un char à un set de chars prédéfini 
    /// 
    /// </summary>
    /// <param name="str"></param>
    /// <param name="characterspool">string source qui contient </param>
    /// <param name="mapping"></param>
    /// <returns></returns>
    function mappingChar($str, $characterspool, $mapping){
        
        $index = strpos($characterspool, $str);
        //si le char se trouve dans le characterspool, on retourne son mapping
        if($index !== false){
            return $mapping[$index];
        }
        else //si le char ne se retrouve pas dans le characterspool, on retourne le char original
        {
            return $str;
        }
    }
?>
