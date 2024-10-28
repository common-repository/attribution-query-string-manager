<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* start link_tracking_qs_filter */
function AQSM_LinkTrackingQSFilterInit(){
/* temporary data */
	//$temporarySessionData = array("c"=>null,"b"=>null,"vx"=>null,"d"=>null);
	//$temporarySessionMetaData=array("c"=>array("append"=>true,"default"=>"cbc"),"b"=>array("append"=>false,"default"=>"qqr"),"vx"=>array("append"=>false,"default"=>"qqr"),"d"=>array("append"=>false));
	//$postOverrides = array("vx"=>"bb");

	/* end temporary data */


	// Get Options
	$cookieLife = get_option( 'aqsm-cookie-life' );

	// Init Session
	if ( !session_id() ){
		session_start();
	}

	// Create the session array and validate it's values
	if(!is_array($_SESSION['AQSM_TrackingQSVars']) || $_SESSION['AQSM_TrackingQSVars']['crc']!=crc32(json_encode($_SESSION['AQSM_TrackingQSVars']))){
		unset($_SESSION['AQSM_TrackingQSVars']);
		// Get the allowed varaibles from the database
		$_SESSION['AQSM_TrackingQSVars']['allowedVariables']=array();
		$_SESSION['AQSM_TrackingQSVars']['allowedVariablesMeta']=json_decode(get_option( 'aqsm-allowableFields' ),true);// This is a placeholder
		$_SESSION['AQSM_TrackingQSVars']['targetURLs']=json_decode(get_option( 'aqsm-targetURLs' ),true); // this is a placeholder
		$_SESSION['AQSM_TrackingQSVars']['allowedVariablesConfirmedDefaults']=array();

		// Apply Defaults
		if(is_array($_SESSION['AQSM_TrackingQSVars']['allowedVariablesMeta'])){
			foreach($_SESSION['AQSM_TrackingQSVars']['allowedVariablesMeta'] as $key => $value){
				if(isset($value['default'])){
					$_SESSION['AQSM_TrackingQSVars']['allowedVariables'][$key]=(string)$value['default'];
				}else{
					$_SESSION['AQSM_TrackingQSVars']['allowedVariables'][$key]="";
				}
			}
		}
		// Unify the formatting of the target urls
		if(is_array($_SESSION['AQSM_TrackingQSVars']['targetURLs'])){
			foreach($_SESSION['AQSM_TrackingQSVars']['targetURLs'] as $key => $targetURL){
				$targetURL = trim($targetURL);
				$targetURL = preg_replace("|^.*\://|","/",$targetURL);

				if(substr($targetURL,0,2)=="//"){
					$targetURL= substr($targetURL,2);
				}
				if(substr($targetURL,0,1)=="/"){
					$targetURL= substr($targetURL,1);
				}

				if(substr($targetURL,-1)=="/"){
					$targetURL = substr($targetURL,0,-1);
				}

				// Create regex search string
				//$targetURLrg = "(<a.*href=\".*".preg_quote($targetURL)."[^\"]*\")i";

				//$_SESSION['AQSM_TrackingQSVars']['targetURLsRG'][$key]=$targetURLrg;
				$_SESSION['AQSM_TrackingQSVars']['targetURLs'][$key] = $targetURL;
			}
		}
		// checksum the array to resist tampering
		$_SESSION['AQSM_TrackingQSVars']['crc'] = crc32(json_encode($_SESSION['AQSM_TrackingQSVars']));
	}// finished creating session array

	if(isset($_COOKIE['AQSM_ContentQSVars']) && is_string($_COOKIE['AQSM_ContentQSVars'])){
		$theseCookies = json_decode(base64_decode($_COOKIE['AQSM_ContentQSVars']),true);
		$_SESSION['AQSM_TrackingQSVars']['allowedVariablesConfirmedDefaults'] = $theseCookies['allowedVariablesConfirmedDefaults'];
	}else{
		$theCookies = array();
	}

	// Populate the QS var array
	foreach ($_SESSION['AQSM_TrackingQSVars']['allowedVariables'] as $key => $value){

		// Apply Client Cookie Values
		if(isset($theseCookies['vars'][$key])){
			$_SESSION['AQSM_TrackingQSVars']['allowedVariables'][$key]=AQSM_UpdateContentQSVar($key,AQSM_XSSCleanser($theseCookies['vars'][$key]));
		}


		// Apply URL Values
		if(isset($_REQUEST[$key])){
			// Scan for cross site scripting attacks
			$_SESSION['AQSM_TrackingQSVars']['allowedVariables'][$key]=AQSM_UpdateContentQSVar($key,AQSM_XSSCleanser($_REQUEST[$key]));
			}

	
	}

	$newCookie['vars']=$_SESSION['AQSM_TrackingQSVars']['allowedVariables'];
	$newCookie['allowedVariablesConfirmedDefaults']=$_SESSION['AQSM_TrackingQSVars']['allowedVariablesConfirmedDefaults'];

	// Update Cookiets
	setcookie("AQSM_ContentQSVars", base64_encode(json_encode($newCookie)), time()+$cookieLife, "/", str_ireplace("https://","",str_ireplace('http://','',get_bloginfo('url'))),false,true);


}// end LinkTrackingQSFilterInit
