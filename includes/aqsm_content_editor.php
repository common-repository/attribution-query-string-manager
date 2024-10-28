<?php
if ( ! defined( 'ABSPATH' ) ) exit;

	add_action( 'add_meta_boxes','AQSM_meta_box'  );

        function AQSM_meta_box(){
            //We Call this method to add a meta box            
            add_meta_box('aqsm_meta_box'
                ,'Query String Management'
                ,'AQSM_render_meta_box_content','post',"normal","high"
            );
            add_meta_box('aqsm_meta_box'
                ,'Query String Management'
                ,'AQSM_render_meta_box_content','page',"normal","high"
            );
        }

        //This is the code that adds elements to the user interface
        function AQSM_render_meta_box_content( $post ) {            
		?>
		<p>Override the automatic, url-driven, and cached Attribution Query String values.  <strong>NOTE: These settings will override ANY URL, cookie, and/or session derived values on this whole page/post</strong>.</p>
		<?php

		wp_nonce_field( 'AQSM_render_meta_box_content', 'AQSM_inner_custom_box_nonce' );

		$fieldset = json_decode(get_option( 'aqsm-allowableFields' ),true);
		if(is_array($fieldset)){

			foreach($fieldset as $fieldName => $fieldMeta){
				$string =  'aqsm-allowableFields-'.$fieldName;
				$value = get_post_meta( $post->ID,$string, true );

				if( isset($fieldMeta['default']) ){
					$defaultValueDisplay = "<span style=\"font-size: 70%;\">Default:</span> ". $fieldMeta['default'] ;
				}else{
					$defaultValueDisplay = "<span style=\"font-size: 70%;\">Default:</span> None" ;
				}
			?>

				    <label class="aqsm-post-field-label" for="aqsm-allowableFields-<?php echo $fieldName ?>">
					 <?php echo $fieldName; ?>  : 
				    </label>
				    <input type="text"
					   id="aqsm-allowableFields-<?php echo $fieldName ?>"
					   name="aqsm-allowableFields-<?php echo $fieldName ?>"
					   size="45" 
					   value="<?php echo esc_attr( $value )?>"/> <?php echo $defaultValueDisplay; ?>
				<br />

			<?php
			}
		}
        }



/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id The ID of the post being saved.
 */
function AQSM_save_postdata( $post_id ) {

	  /*
	   * We need to verify this came from the our screen and with proper authorization,
	   * because save_post can be triggered at other times.
	   */

	  // Check if our nonce is set.
	  if ( ! isset( $_POST['AQSM_inner_custom_box_nonce'] ) )
	    return $post_id;

	  $nonce = $_POST['AQSM_inner_custom_box_nonce'];

	  // Verify that the nonce is valid.
	  if ( ! wp_verify_nonce( $nonce, 'AQSM_render_meta_box_content' ) )
	      return $post_id;

	  // If this is an autosave, our form has not been submitted, so we don't want to do anything.
	  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
	      return $post_id;

	  // Check the user's permissions.
	  if ( 'page' == $_POST['post_type'] ) {

	    if ( ! current_user_can( 'edit_page', $post_id ) )
		return $post_id;
	  
	  } else {

	    if ( ! current_user_can( 'edit_post', $post_id ) )
		return $post_id;
	  }

	  /* OK, its safe for us to save the data now. */

	$fieldset = json_decode(get_option( 'aqsm-allowableFields' ),true);

		foreach($fieldset as $fieldName => $fieldMeta){
			$string = 'aqsm-allowableFields-'.$fieldName;
			// Sanitize user input.
			$mydata = sanitize_text_field( $_POST[$string] );

			// Update the meta field in the database.
			update_post_meta( $post_id, $string, $mydata );

		}
}// end AQSM_save_postdata

add_action( 'save_post', 'AQSM_save_postdata' );


