jQuery(document).ready(function() {

// Init & reload function

// get json object from hidden fields
if(typeof(aqsm_admin)!="undefined"){
	//aqsm-targetURLs
	if(jQuery("#aqsm-targetURLs").attr("value")!==""){
		var aqsmTargetURLs = jQuery.parseJSON(jQuery("#aqsm-targetURLs").attr("value"));

		var appendHTML = "";
		jQuery.each(aqsmTargetURLs, function(index,value){
			appendHTML = appendHTML + "<tr><td><input id=\"aqsm-targetURLs["+ index +"]\" name=\"aqsm-targetURLs["+ index +"]\" type=\"text\" value=\""+value+"\" /></td>\n";
			appendHTML = appendHTML + "<td><a class=\"button-secondary aqsm-targetURLs-form-remove\" href=\"#\" title=\"Remove Query String\">Remove</a></td></tr>\n";
			jQuery("#aqsm-targetURLs-form table tbody").append(appendHTML);
			appendHTML="";
		});
	}

	//aqsm-allowableFields
	if(jQuery("#aqsm-allowableFields").attr("value")!=""){
		var aqsmAllowableFields = jQuery.parseJSON(jQuery("#aqsm-allowableFields").attr("value"));
		var checkboxChecked = "";
		jQuery.each(aqsmAllowableFields, function(index,value){
			appendHTML = appendHTML + "\n<tr>\n<td><input class=\"aqsm-allowableFields-key\" name=\"aqsm-allowableFields[key]\" type=\"text\" value=\""+index+"\" /></td>\n";

			if(typeof(value.default)!=="undefined"){
				appendHTML = appendHTML + "<td><input class=\"aqsm-allowableFields-default\" name=\"aqsm-allowableFields["+ index +"][default]\" type=\"text\" value=\""+value.default+"\" /></td>\n";
				appendHTML = appendHTML + "<td><input class=\"aqsm-allowableFields-disabledDefault\" name=\"aqsm-allowableFields["+ index +"][disableDefault]\" type=\"checkbox\" value=\"append\" /></td>\n";
			}else{
				appendHTML = appendHTML + "<td><input class=\"aqsm-allowableFields-default\" name=\"aqsm-allowableFields["+ index +"][default]\" type=\"text\" value=\"\" disabled /></td>\n";
				appendHTML = appendHTML + "<td><input class=\"aqsm-allowableFields-disabledDefault\" name=\"aqsm-allowableFields["+ index +"][disableDefault]\" type=\"checkbox\" value=\"append\" checked=\"true\" /></td>\n";
			}

			if(typeof(value.append)!=="undefined"){
				if(value.append == true){
					checkboxChecked = "checked=\"true\"";
				}else{
					checkboxChecked = "";
				}
			}
			appendHTML = appendHTML + "<td><input class=\"aqsm-allowableFields-append\" name=\"aqsm-allowableFields["+ index +"][append]\" type=\"checkbox\" value=\"append\" "+ checkboxChecked +" /></td>\n";
			appendHTML = appendHTML + "<td><a class=\"button-secondary aqsm-allowableFields-form-remove\" href=\"#\" title=\"Remove Query String\">Remove</a></td></tr>\n";
			jQuery("#aqsm-allowableFields-form table").append(appendHTML);
			appendHTML="";
		});
	}

	aqsm_BindFieldUpdates();

// Bind Add function to Add Buttons

jQuery(".aqsm-allowableFields-form-add").click(function(){
	var appendHTML = "";
	appendHTML = appendHTML + "\n<tr>\n<td><input class=\"aqsm-allowableFields-key\" name=\"aqsm-allowableFields[key]\" type=\"text\" value=\"\" /></td>\n";
	appendHTML = appendHTML + "<td><input class=\"aqsm-allowableFields-default\" name=\"aqsm-allowableFields[][default]\" type=\"text\" value=\"\" /></td>\n";
	appendHTML = appendHTML + "<td><input class=\"aqsm-allowableFields-disabledDefault\" name=\"aqsm-allowableFields[][disableDefault]\" type=\"checkbox\" value=\"append\" /></td>\n";
	appendHTML = appendHTML + "<td><input class=\"aqsm-allowableFields-append\" name=\"aqsm-allowableFields[][append]\" type=\"checkbox\" value=\"append\" /></td>\n";
	appendHTML = appendHTML + "<td><a class=\"button-secondary aqsm-allowableFields-form-remove\" href=\"#\" title=\"Remove Query String\">Remove</a></td></tr>\n";
	jQuery("#aqsm-allowableFields-form table").append(appendHTML);
	aqsm_BindFieldUpdates();

	jQuery(".aqsm-allowableFields-form-remove").click(function(){
		jQuery(this).parent().parent().empty();
		aqsm_BindFieldUpdates();
		aqsm_scanFieldValues();
	});
});



jQuery(".aqsm-targetURLs-form-add").click(function(){
	var appendHTML = "";
	appendHTML = appendHTML + "<tr><td><input id=\"aqsm-targetURLs[]\" name=\"aqsm-targetURLs[]\" type=\"text\" value=\"\" /></td>\n";
	appendHTML = appendHTML + "<td><a class=\"button-secondary aqsm-targetURLs-form-remove\" href=\"#\" title=\"Remove Query String\">Remove</a></td></tr>\n";

	jQuery("#aqsm-targetURLs-form table tbody").append(appendHTML);
	appendHTML="";
	aqsm_BindFieldUpdates();

	jQuery(".aqsm-targetURLs-form-remove").click(function(){
		jQuery(this).parent().parent().empty();

		aqsm_BindFieldUpdates();
		aqsm_scanFieldValues();

		});
});

// end Init	






// Bind update function to input fields
function aqsm_BindFieldUpdates(){

	jQuery("#aqsm-allowableFields-form input").bind("change paste keyup",function(){
		var inputType = jQuery(this).attr("name").substring(jQuery(this).attr("name").indexOf("][")+1);
		

		// enable/disable the default value field
		if(inputType == "[disableDefault]"){
			if(jQuery(this).prop("checked")){
				jQuery(this).parent().parent().find(".aqsm-allowableFields-default").attr("value","");
				jQuery(this).parent().parent().find(".aqsm-allowableFields-default").prop("disabled",true);
			}else{
				jQuery(this).parent().parent().find(".aqsm-allowableFields-default").removeProp("disabled");
			}	
		}


		delete inputType;

		aqsm_scanFieldValues();
	});

	jQuery("#aqsm-targetURLs-form input").bind("change paste keyup",function(){
		aqsm_scanFieldValues();
	});

}// end aqsm_BindFieldUpdates

function aqsm_scanFieldValues(){
	var replacementAllowableFields ={};
	jQuery("#aqsm-allowableFields-form tr").each(function(index,value){

	// Populate array structure
	jQuery(this).find("input").each(function(index,value){

		var keyname = jQuery(this).parent().parent().find(".aqsm-allowableFields-key").attr("value");

		if(keyname !=""){
			if(typeof(replacementAllowableFields[keyname])=="undefined"){
				replacementAllowableFields[keyname] = {};
			}

		if(jQuery(this).attr("class")=="aqsm-allowableFields-default"){
			if(!jQuery(this).parent().parent().find(".aqsm-allowableFields-disabledDefault").prop("checked")){
				replacementAllowableFields[keyname]["default"]=jQuery(this).attr("value");
			}
		}

		if(jQuery(this).attr("class")=="aqsm-allowableFields-append"){
			if(jQuery(this).prop("checked")){
				replacementAllowableFields[keyname]["append"]=true;
			}else{
				replacementAllowableFields[keyname]["append"]=false;
			}
		}
		}
		});
	});

		jQuery("#aqsm-allowableFields").attr("value",JSON.stringify(replacementAllowableFields));
		replacementAllowableFields=null;

	var newTargetURLs = {};
	var i=0;
	jQuery("#aqsm-targetURLs-form input").each(function(index,value){

		newTargetURLs[i]=jQuery(this).attr("value");
		i++;
	});
	jQuery("#aqsm-targetURLs").attr("value",JSON.stringify(newTargetURLs));
	delete newTargetURLs;

} // end sqsm_scanFieldValues


jQuery(".aqsm-allowableFields-form-remove").click(function(){
	jQuery(this).parent().parent().empty();

	aqsm_BindFieldUpdates();
	aqsm_scanFieldValues();

	});

jQuery(".aqsm-targetURLs-form-remove").click(function(){
	jQuery(this).parent().parent().empty();

	aqsm_BindFieldUpdates();
	aqsm_scanFieldValues();

	});

}
});
