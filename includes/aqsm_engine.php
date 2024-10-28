<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function AQSM_LinkTrackingQSFilter($content){
	if(is_array($_SESSION['AQSM_TrackingQSVars']['targetURLs']) and is_array($_SESSION['AQSM_TrackingQSVars']['allowedVariables'])){
		foreach($_SESSION['AQSM_TrackingQSVars']['allowedVariables'] as $key=>$value){

			$metafieldname = 'aqsm-allowableFields-'.$key;
			$override = get_post_meta( get_the_ID(),$metafieldname,true);


			// Apply post overrides
			if($override!="" && $override!=null){
				$thisPageVariables[$key]=$override;
			}else{
				$thisPageVariables[$key]=AQSM_UpdateContentQSVar($key,$value);
			}

		}

		$html = new simple_html_dom();
		$html->load($content);

		// Update all <a> tags
		$aLinks = $html->find('a');
		foreach($aLinks as $aLink){
			foreach($_SESSION['AQSM_TrackingQSVars']['targetURLs'] as $aTargetUrl){
				$ModMe=false;
				if(strpos($aLink->href,$aTargetUrl)!==false){$ModMe = true;}
				if(strpos($aLink->href,"http://".$aTargetUrl)!==false){$ModMe = true;}
				if(strpos($aLink->href,"https://".$aTargetUrl)!==false){$ModMe = true;}
				if($ModMe == true){
					$aLink->href = AQSM_ReplaceQSInLinks($aLink->href,$thisPageVariables);
				}

			}
		}


		// Update all <form> tags
		$muffins = $html->find('form');
		foreach($muffins as $muffin){
			foreach($_SESSION['AQSM_TrackingQSVars']['targetURLs'] as $aTargetUrl){
				$ModMe=false;
				if(strpos($muffin->action,$aTargetUrl)==1){$ModMe = true;}
				if(strpos($muffin->action,"//".$aTargetUrl)!==false){$ModMe = true;}
				if(strpos($muffin->action,"http://".$aTargetUrl)!==false){$ModMe = true;}
				if(strpos($muffin->action,"https://".$aTargetUrl)!==false){$ModMe = true;}
				if($ModMe == true){

					// Remove any hardcoded query string values from the form that are under management
					$muffin->action = AQSM_StripQSLinksFromString($muffin->action,$thisPageVariables);

					foreach($thisPageVariables as $key => $value){
							$extant = false;
							$formFields = $muffin->find('input');
							// Look for field in the form to see if it already exists
							foreach($formFields as $formField){
								if($formField->id == $key || $formField->name==$key){
									// Field exists in the form - update it
									$formField->value = $value;
									$extant = true;
								}
							}

							unset($formFields);
							if(!$extant){
								// Field does not exist
								$append = $muffin->first_child();
								$append->outertext = "<input type=\"hidden\" name=\"$key\" value=\"$value\">\n".$append->outertext;
							}
					}
				}

			}
		}

/*
		// Get a list of all URL's that need to be updated
		$urlsToScanFor = array();
		foreach($_SESSION['AQSM_TrackingQSVars']['targetURLsRG'] as $targetURLrg){

			preg_match_all($targetURLrg,$content,$urlToScanFor,PREG_PATTERN_ORDER);

			$urlsToScanFor=array_merge($urlsToScanFor,$urlToScanFor[0]);

		}
		unset($urlActionItems);
		$urlActionItems=array();
		$urlActionItems['original'] = null;

		// For each URL, update the QS vars and then replace in the content
		foreach($urlsToScanFor as $url){

			$urlActionItems['original'][]="(".preg_quote($url).")i";
			$urlActionItems['new'][]="".AQSM_ReplaceQSInLinks($url, $thisPageVariables)."";
		}


		if(is_array($urlActionItems['original'])){
			ksort($urlActionItems['original']);
			ksort($urlActionItems['new']);

			$content = preg_replace($urlActionItems['original'],$urlActionItems['new'],$content);
		}
*/
	$content = $html->save();
	$html->clear();
	}
	return $content;
}// end AQSM_LinkTrackingQSFilter

add_filter('the_content', 'AQSM_LinkTrackingQSFilter');





function AQSM_XSSCleanser($filhlyValue){
		if(strlen($filhlyValue) != strlen(strip_tags($filhlyValue))){ 
			// Void the variable content if there is html
			$filhlyValue = "";
		}else{
			$notAllowed = array(" ","'");  //remove any disallowed characters here
			$filhlyValue = str_replace($notAllowed, "", $filhlyValue);
		}
	return $filhlyValue;
	}// end AQSM_XSSCleanser



function AQSM_StripQSLinksFromString ($patient,$AQSM_thisContentQSVars){

//echo "\n\n PT= $patient \n\n";
	// Prep String for surgery
	if(substr($patient,-1)=="\""){
		$patient = substr($patient,0,-1);
		}

	// Split the patient
	$patientOrgans = explode("?",$patient);

	if(count($patientOrgans)<=1){
		$patientOrgans[1]="";
	}

	// Clean the organs
	$patientOrgans[1]=str_ireplace("&#038;","&",$patientOrgans[1]);
	$patientOrgans[1]=str_ireplace("&amp;","&",$patientOrgans[1]);


	// Separate the organs
	$patientOrgansKeys = array();
	$organism = array();
	$patientOrgansKeys = explode("&",$patientOrgans[1]);
	foreach($patientOrgansKeys as $organ){
		$organDiscrete = explode("=",$organ);
		if(count($organDiscrete)==2){
			$organism[$organDiscrete[0]]=$organDiscrete[1];
		}
	}

	// Determine which organs are kept and which are replaced
	foreach($AQSM_thisContentQSVars as $key => $value){
		unset($organism[$key]);
	}

	// Assemble the new organism
	foreach($organism as $organName =>$organ){
		if($organ!=""){
		$newOrganism[] = $organName."=".$organ;
		}
	}

	// Determine whether a trailing / needs to be added to the domain name for consistency
	if(strripos($patientOrgans[0],"://")!==false && substr_count($patientOrgans[0],"/")==2 && substr_count($patientOrgans[0],"?")==0){
		$patientOrgans[0] .= "/";
		}

	//Append New Keys
	if(is_array($newOrganism)){
		$newKeysString = implode($newOrganism,"&");
	}else{
		$newKeyString = $newOrganism;
	}	


	$patient = AQSM_joinQSToLink($patientOrgans[0],$newKeysString);


	// Re-encode the &'s
	//$patient .= "\"";
	//while($patient != preg_replace("#(&)([^\#].*)#i","&#038;$2",$patient)){
	//	$patient = preg_replace("#(&)([^\#].*)#i","&#038;$2",$patient);
	//}

	return $patient;

}// end AQSM_StripQSLinksFromString



function AQSM_ReplaceQSInLinks($patient,$AQSM_thisContentQSVars){

	// Prep String for surgery
	if(substr($patient,-1)=="\""){
		$patient = substr($patient,0,-1);
		}

	// Split the patient
	$patientOrgans = explode("?",$patient);

	if(count($patientOrgans)<=1){
		$patientOrgans[1]="";
	}

	// Clean the organs
	$patientOrgans[1]=str_ireplace("&#038;","&",$patientOrgans[1]);
	$patientOrgans[1]=str_ireplace("&amp;","&",$patientOrgans[1]);


	// Separate the organs
	$patientOrgansKeys = array();
	$organism = array();
	$patientOrgansKeys = explode("&",$patientOrgans[1]);
	foreach($patientOrgansKeys as $organ){
		$organDiscrete = explode("=",$organ);
		if(count($organDiscrete)==2){
			$organism[$organDiscrete[0]]=$organDiscrete[1];
		}
	}

	// Determine which organs are kept and which are replaced
	foreach($AQSM_thisContentQSVars as $key => $value){
		unset($organism[$key]);
		if($value !== null){
			$organism[$key]=urlencode($value);
		}
	}

	// Assemble the new organism
	foreach($organism as $organName =>$organ){
		if($organ!=""){
		$newOrganism[] = $organName."=".$organ;
		}
	}

	// Determine whether a trailing / needs to be added to the domain name for consistency
	if(strripos($patientOrgans[0],"://")!==false && substr_count($patientOrgans[0],"/")==2 && substr_count($patientOrgans[0],"?")==0){
		$patientOrgans[0] .= "/";
		}

	//Append New Keys
	if(is_array($newOrganism)){
		$newKeysString = implode($newOrganism,"&");
	}else{
		$newKeyString = $newOrganism;
	}	


	$patient = AQSM_joinQSToLink($patientOrgans[0],$newKeysString);


	// Re-encode the &'s
	//$patient .= "\"";
	//while($patient != preg_replace("#(&)([^\#].*)#i","&#038;$2",$patient)){
	//	$patient = preg_replace("#(&)([^\#].*)#i","&#038;$2",$patient);
	//}

	return $patient;
	}



function AQSM_joinQSToLink($patient,$organ){
	if($organ != ""){
		if(strrpos($patient,"?")==false){
			$patient .= "?$organ";
		} elseif(substr($patient,-1)=="?" || substr($patient,-1)=="&" || substr($patient,-6)=="&#038;") {
			$patient .= "$organ";
		}else{
			$patient .= "&$organ";
		}
	}
	return $patient;
}//AQSM_joinQSToLink


function AQSM_UpdateContentQSVar($key,$value){
	if($_SESSION['AQSM_TrackingQSVars']['allowedVariables'][$key]===null){
		 return $value;

	}else{

		if($_SESSION['AQSM_TrackingQSVars']['allowedVariablesMeta'][$key]['append']==false){

			return $value;
		}else{

			if($value!=$_SESSION['AQSM_TrackingQSVars']['allowedVariablesMeta'][$key]['default']){
				$values=explode(",",$_SESSION['AQSM_TrackingQSVars']['allowedVariables'][$key]);
				$values2 = explode(",",$value);
				if(is_array($values)){
					if(is_array($values2)){
						$values = array_merge($values,$values2);
					}else{
						if(!in_array($value,$values)){
							array_unshift($values,$value);
						}
					}
					$values=array_unique($values);
				}else{
					$values[0] = $value;
				}

				 $untrimmed = implode(",",$values);
				return trim($untrimmed,",");
			}else{

				$_SESSION['AQSM_TrackingQSVars']['allowedVariablesConfirmedDefaults'][]=$key;
				$_SESSION['AQSM_TrackingQSVars']['allowedVariablesConfirmedDefaults']=array_unique($_SESSION['AQSM_TrackingQSVars']['allowedVariablesConfirmedDefaults']);
				return $value;
			}
		}
	}
}// AQSM_UpdateContentQSVar
