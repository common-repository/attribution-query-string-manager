<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function AQSM_register_setting() {
	register_setting( 'aqsm-settings-group', 'aqsm-cookie-life' ); // Creates setting in DB
	register_setting( 'aqsm-settings-group', 'aqsm-allowableFields' ); // Creates setting in DB
	register_setting( 'aqsm-settings-group', 'aqsm-targetURLs' ); // Creates setting in DB
	add_settings_section( 'aqsm-general-settings', 'General Settings', 'AQSM_general_settings_callback', 'aqsm-settings' ); // Defines the Section & group of settings
	add_settings_field( 'aqsm-cookie-life', 'Cookie Lifespan (seconds)', 'AQSM_cookielifefield_callback', 'aqsm-settings', 'aqsm-general-settings' ); // Creates the field
	//add_settings_field( 'aqsm-allowableFields', 'Query String Variables to Manage', 'AQSM_allowableFields_callback', 'aqsm-settings', 'aqsm-general-settings' ); // Creates the field
	//add_settings_field( 'aqsm-targetURLs', 'Domain Names to Apply Query String Variables To', 'AQSM_targetURL_callback', 'aqsm-settings', 'aqsm-general-settings' ); // Creates the field
}

function AQSM_general_settings_callback() {
    // render group code here
}

function AQSM_cookielifefield_callback() {
    $setting = esc_attr( get_option( 'aqsm-cookie-life' ) );
    echo "<input type='text' name='aqsm-cookie-life' value='$setting' />";
}

function AQSM_allowableFields_callback() {
    //$setting = esc_attr( get_option( 'aqsm-allowableFields' ) );
    //echo "<input type='text' name='aqsm-allowableFields' class=\"aqsm-hiddeninput\" value='$setting' />";
}

function AQSM_targetURL_callback() {
//    $setting = esc_attr( get_option( 'aqsm-targetURLs' ) );
//    echo "<input type='text' name='aqsm-targetURLs' class=\"aqsm-hiddeninput\" value='$setting' />";
}

add_action( 'admin_init', 'AQSM_register_setting' );




add_action('admin_menu', 'AQSM_admin_menu');

function AQSM_admin_menu() {
    $page_title = 'Query String Manager';
    $menu_title = 'Query String Manager';
    $capability = 'manage_options';
    $menu_slug = 'aqsm-settings';
    $function = 'AQSM_settings';
    add_options_page($page_title, $menu_title, $capability, $menu_slug, $function);
}

function AQSM_settings() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }
	?>
	<script type="text/javascript">
	var aqsm_admin = true;
	</script>
	<div class="wrap">
	<?php screen_icon(); ?>
	<h2>Attribution Query String Manager</h2>

	<p>This tool will scan links with the specified domains for the query string variables listed below and ensure that they are updated with the appropriate values. These valued can be defined by:</p>
	<ol>
	<li>Post/Page settings</li>
	<li>URL</li>
	<li>Client Side Cookie</li>
	<li>Server Session Cache</li>
	<li>Defaults (defined below)</li>
	</ol>

	<p>The list above indicates the order of override - in short, settings for a post/page will cause mismatching values set via url to be ignored.  Note: This processing will only apply to material output via the_content() (this includes pages & posts)</p>

	<div id="poststuff">
	<div id="post-body-content">
		<form method="post" action="">

		<fieldset id="aqsm-allowableFields-form">
		<h3>Query String Variables to Manage</h3>
		<p>Add query string variables that are to be placed under management.</p>
		<table class="wp-list-table widefat">
			<thead>
				<tr>
					<th class="manage-column column-columnname">Variable</th>
					<th class="manage-column column-columnname">Default Value</th>
					<th class="manage-column column-columnname">Disable Default<br /> Value</th>
					<th class="manage-column column-columnname">Append*</th>
					<th class="manage-column column-columnname">&nbsp;</th>
				</tr>
			</thead>
			<tbody id="the-list">

			</tbody>
		</table>
		
		<p><a class="button-secondary aqsm-allowableFields-form-add" href="#" title="Add Query String">Add</a></p>
		</fieldset>

		<p>*The "append" setting will cause the engine to CHAIN inputs. So if the cookie says that querystring value "sourcePartner" equals "search" and the URL defines sourcePartner=affiliate, the value passed will be "affiliate,search"</p>

		<fieldset>
		<legend><h3>Domain Names to Apply Query String Variables To</h3></legend>
		<table>
		<tr>
		<th>Domain(s):</th>
		<td>
			<div id="aqsm-targetURLs-form">
				<table>
				<tbody>
				</tbody>
				</table>
			</div><!-- end #aqsm-targetURLs-form -->
		</td>
		</tr>
		</table>

		</fieldset>
		<p><a class="button-secondary aqsm-targetURLs-form-add" href="#" title="Add a Domain">Add</a></p>
		</form>
	</div><!-- end post-body-content-->

	<div id="postbox-container-2" class="postbox-container">
	<form method="post" action="options.php" class="postbox-container" id="postbox-container-2"> 
		<?php settings_fields( 'aqsm-settings-group' ); ?>
		<?php do_settings_sections( 'aqsm-settings' ); ?>
		<input type="text" name="aqsm-allowableFields" id="aqsm-allowableFields" class="aqsm-hiddeninput" value="<?php echo esc_attr( get_option( 'aqsm-allowableFields' ) )?>" />
		<input  type="text" name="aqsm-targetURLs" id="aqsm-targetURLs" class="aqsm-hiddeninput" value="<?php echo esc_attr( get_option( 'aqsm-targetURLs' ) ) ?>" />
		<?php submit_button(); ?>

	</form>
	</div><!-- end id="postbox-container-2" class="postbox-container" -->
	</div><!-- end #poststuff -->
	<?php
}



add_filter('plugin_action_links', 'AQSM_plugin_action_links', 10, 2);

function AQSM_plugin_action_links($links, $file) {
    static $this_plugin;

    if (!$this_plugin) {
        $this_plugin = plugin_basename(__FILE__);
    }

    if ($file == $this_plugin) {
        // The "page" query string value must be equal to the slug
        // of the Settings admin page we defined earlier, which in
        // this case equals "myplugin-settings".
        $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=aqsm-settings">Settings</a>';
        array_unshift($links, $settings_link);
    }

    return $links;
}
