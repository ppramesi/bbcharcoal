<?php
/*
Plugin Name: Domain Mirror
Plugin URI: http://mcaleavy.org/code/domain-mirror/
Description: Allows Wordpress to be used with mirrored domains. Go to the <a href="options-general.php?page=AA-DomainMirror/WP-DomainMirror.php">options page</a> to configure.
Author: Dave McAleavy
Version: 1.1
Author URI: http://mcaleavy.org
*/ 

/*  Copyright 2007  David McAleavy  http://mcaleavy.org/contact/

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/* Change History
 *
 * v1.0 - 22/05/2007
 * 		Initial release.
 *
 * v1.1 - 30/05/2007
 *		Added Tagline to options.
 *		Added "Get Current Domain" button.
 *		Added "X" clear line buttons.
 */
			
function wpdm_getAdminOptions() {
		
	$default_options = array();
	$wpdm_options = array();
	$options = array();
			
	require ('config.inc.php');
			
	$options = unserialize(get_option('wpdm_options'));

	// set up defaults
			
	if ($options == '') {  // There are no options set
	
		add_option('wpdm_options', serialize($default_options));
		$options = $default_options;
	}
	elseif (count($options) == 0) { // All options are blank

		update_option('wpdm_options', serialize($default_options));
		$options = $default_options;
	}
		
				
	// Count the domains and extract the fields we're interested in

	$count = 0;
					
	foreach ($options as $k => $v) {

		if ( substr($k, 0, 7) == 'domain_' ) {
			$wpdm_options[$k] = $v;
			$count = $count + 1;
		}

		if ( substr($k, 0, 9) == 'blogname_' ) {
			$wpdm_options[$k] = $v;
		}
		
		if ( substr($k, 0, 16) == 'blogdescription_' ) {
			$wpdm_options[$k] = $v;
		}				
					
		if ( substr ($k, 0, 8) == 'siteurl_' ) {
			$wpdm_options[$k] = $v;
		}

		if ( substr ($k, 0, 5) == 'home_' ) {
			$wpdm_options[$k] = $v;
		}
	}
			
	$wpdm_options['count'] = $count;		
	return ($wpdm_options);
}
		
// The Admin Screen		
function wpdm_admin() {

 	if($_POST["wpdm_action"] == "Save Changes") {

				$options = array();
				$count = 0;

				foreach ($_POST as $k => $v) {
				
						if ( substr($k, 0, 12) == 'wpdm_domain_' ) {
							$count = $count + 1;
							$options['domain_'.$count] = $v;
						}
					
						if ( substr($k, 0, 14) == 'wpdm_blogname_' ) {
							$options['blogname_'.$count] = $v;
						}
					
						if ( substr($k, 0, 21) == 'wpdm_blogdescription_' ) {
							$options['blogdescription_'.$count] = $v;
						}
					
						if ( substr ($k, 0, 13) == 'wpdm_siteurl_' ) {
							$options['siteurl_'.$count] = $v;
						}
						
						if ( substr ($k, 0, 10) == 'wpdm_home_' ) {
							$options['home_'.$count] = $v;
						}
				}
				if (get_option('wpdm_options') == '') {
					add_option('wpdm_options', serialize($options));
				}
				else {
					update_option('wpdm_options', serialize($options));
				}
				?>
				<div id="message" class="updated fade"><p><strong><?php _e('Changes saved.') ?></strong></p></div>
				<?php
	}
		
	$options = array();
	$options = wpdm_getAdminOptions();

 	if($_POST["wpdm_action"] == "Add New Domain") {

 		$options['count'] = $options['count'] + 1;
 	
 		$options['domain'.$options['count']] = '';
		$options['blogname'.$options['count']] = '';
		$options['blogdescription'.$options['count']] = '';
		$options['siteurl'.$options['count']] = 'http://';
		$options['home'.$options['count']] = 'http://';
 	
		?>
			<div id="message" class="updated fade"><p><strong><?php _e('Blank domain added. Edit details and save to complete.') ?></strong></p></div>
		<?php
	} 

?>

<script type="text/javascript">
function wpdm_get_domain(number) {
	document.getElementById( 'wpdm_domain_' + parseInt(number)).value = "<?php echo $_SERVER['SERVER_NAME']?>";
	document.getElementById( 'wpdm_blogname_' + parseInt(number)).value = "<?php echo get_option('blogname') ?>";
	document.getElementById( 'wpdm_blogdescription_' + parseInt(number)).value = "<?php echo get_option('blogdescription') ?>";
	document.getElementById( 'wpdm_siteurl_' + parseInt(number)).value = "<?php echo get_option('siteurl') ?>";
	document.getElementById( 'wpdm_home_' + parseInt(number)).value = "<?php echo get_option('home') ?>";
}

function wpdm_clear_all(number) {
	document.getElementById( 'wpdm_domain_' + parseInt(number)).value = "";
	document.getElementById( 'wpdm_blogname_' + parseInt(number)).value = "";
	document.getElementById( 'wpdm_blogdescription_' + parseInt(number)).value = "";
	document.getElementById( 'wpdm_siteurl_' + parseInt(number)).value = "http://";
	document.getElementById( 'wpdm_home_' + parseInt(number)).value = "http://";
}

</script>

	<div class="wrap">
    	<h2>Domain Mirror</h2>
    	
		<p><a href="../wp-content/plugins/AA-DomainMirror/Help.html" target="_blank">Local Help</a> | <a href="http://mcaleavy.org/code/domain-mirror/" target="_blank">Plugin Homepage</a> | <a href="http://mcaleavy.org" target="_blank">Author Homepage</a></p>
		<p>Current domain: <b><?php _e($_SERVER['SERVER_NAME']);?></b></p>
      	
      	<fieldset class="options">
      	<legend>Domains</legend>
      	<form method="post" action="">
		<hr>

<?php
	for ($count = 1; $count <= $options['count']; $count++) {
?>

        <div id='Domain<?php echo $count; ?>'>
        <h3>Domain #<?php echo $count; ?></h3>
	
		<table class="optiontable">
			<tr>
				<th scope="row">
					Domain:
				</th>
				<td>
					www.<input type="text" id="wpdm_domain_<?php echo $count; ?>" name="wpdm_domain_<?php echo $count; ?>" value="<?php print $options['domain_'.$count]; ?>" size="36" />
					<input type="button" onclick="void(document.getElementById( 'wpdm_domain_<?php echo $count; ?>' ).value = '');" value="X" />

				</td>
			</td>
			<tr>
				<th scope="row">
					Weblog title:
				</th>
				<td>
					<input type="text" id="wpdm_blogname_<?php echo $count; ?>" name="wpdm_blogname_<?php echo $count; ?>" value="<?php print $options['blogname_'.$count]; ?>" size="40" />
					<input type="button" onclick="void(document.getElementById( 'wpdm_blogname_<?php echo $count; ?>' ).value = '');" value="X" />
				</td>
				<td>[dmBlogTitle]</td>
			</td>
			<tr>
				<th scope="row">
					Tagline:
				</th>
				<td>
					<input type="text" id="wpdm_blogdescription_<?php echo $count; ?>" name="wpdm_blogdescription_<?php echo $count; ?>" value="<?php print $options['blogdescription_'.$count]; ?>" size="40" />
					<input type="button" onclick="void(document.getElementById( 'wpdm_blogdescription_<?php echo $count; ?>' ).value = '');" value="X" />
				</td>
				<td>[dmTagLine]</td>
			</td>
			<tr>
				<th scope="row">
					Wordpress address (URL):
				</th>
				<td>
					<input type="text" id="wpdm_siteurl_<?php echo $count; ?>" name="wpdm_siteurl_<?php echo $count; ?>" value="<?php print $options['siteurl_'.$count]; ?>" size="40" />
					<input type="button" onclick="void(document.getElementById( 'wpdm_siteurl_<?php echo $count; ?>' ).value = 'http://');" value="X" />
				</td>
				<td>[dmWpAddr]</td>
			</td>				
			<tr>
				<th scope="row">
					Blog address (URL):
				</td>
				<td>
					<input type="text" id="wpdm_home_<?php echo $count; ?>" name="wpdm_home_<?php echo $count; ?>" value="<?php print $options['home_'.$count]; ?>" size="40" />
					<input type="button" onclick="void(document.getElementById( 'wpdm_home_<?php echo $count; ?>' ).value = 'http://');" value="X" />
				</td>
				<td>[dmBlogAddr]</td>
			</td>

			<tr>
				<th scope="row">
				</td>
				<td align="right">
					Clear all: <input type="button" onclick="void(wpdm_clear_all('<?php echo $count; ?>'))" value="X" />
				</td>
				<td></td>
			</td>
			
		</table>
		<p class="submit">
			<input type="button" onclick="void(document.getElementById( 'Domain<?php echo $count; ?>' ).parentNode.removeChild(document.getElementById( 'Domain<?php echo $count; ?>' )));" value="Delete Domain" />
		</p> 
		<p class="submit">
			<input type="button" onclick="void(wpdm_get_domain('<?php echo $count; ?>'))" value="Get Current Domain" /> 
        </p>        
        <br/><hr>
        </div>

<?php } ?>			

        <p class="submit">
          <input type="submit" name="wpdm_action" value="Add New Domain" /> 
        </p>        
        <p class="submit">
          <input type="submit" name="wpdm_action" value="Save Changes" /> 
        </p>

      </form>
    </fieldset>

  </div>
<?php
}

// Filters & Actions

function wpdm_filter_blogname($content) {

	$current_server = $_SERVER['SERVER_NAME'];
	$options = wpdm_getAdminOptions();

	for ($count = 1; $count <= $options['count']; $count++) {
		if ( $current_server == $options['domain_'.$count] || $current_server == 'www.'.$options['domain_'.$count] ) {
			return ( $options['blogname_'.$count] );
		}
   }
   return $content;
}

function wpdm_filter_siteurl($content) {

	$current_server = $_SERVER['SERVER_NAME'];
	$options = wpdm_getAdminOptions();

	for ($count = 1; $count <= $options['count']; $count++) {
		if ( $current_server == $options['domain_'.$count] || $current_server == 'www.'.$options['domain_'.$count] ) {
			return ( $options['siteurl_'.$count] );
		}
   }
   return $content;
}

function wpdm_filter_home($content) {

	$current_server = $_SERVER['SERVER_NAME'];
	$options = wpdm_getAdminOptions();

	for ($count = 1; $count <= $options['count']; $count++) {
		if ( $current_server == $options['domain_'.$count] || $current_server == 'www.'.$options['domain_'.$count] ) {
			return ( $options['home_'.$count] ); 
		}
   }
   return $content;
}

function wpdm_filter_blogdescription($content) {

	$current_server = $_SERVER['SERVER_NAME'];
	$options = wpdm_getAdminOptions();

	for ($count = 1; $count <= $options['count']; $count++) {
		if ( $current_server == $options['domain_'.$count] || $current_server == 'www.'.$options['domain_'.$count] ) {
			return ( $options['blogdescription_'.$count] ); 
		}
   }
   return $content;
}

function wpdm_conv_tag($content) {
		
	$search = "/\[dmWpAddr\]/";
		
		if (preg_match($search, $content)){
			$replace = get_option('siteurl');
			$content = preg_replace ($search, $replace, $content);
		}

	$search = "/\[dmBlogAddr\]/";
		
		if (preg_match($search, $content)){
			$replace = get_option('home');
			$content = preg_replace ($search, $replace, $content);
		}

	$search = "/\[dmBlogTitle\]/";
		
		if (preg_match($search, $content)){
			$replace = get_option('blogname');
			$content = preg_replace ($search, $replace, $content);
		}

	$search = "/\[dmTagLine\]/";
		
		if (preg_match($search, $content)){
			$replace = get_option('blogdescription');
			$content = preg_replace ($search, $replace, $content);
		}

	return $content;
}

function wpdm_admin_menu() {
  // Add a new menu:
  add_options_page('Domain Mirror Options', 'Domain Mirror', 8, __FILE__, 'wpdm_admin');
}

// Add the hooks:
add_filter('option_blogname', 'wpdm_filter_blogname', 1);
add_filter('option_siteurl', 'wpdm_filter_siteurl', 1);
add_filter('option_home', 'wpdm_filter_home', 1);
add_filter('option_blogdescription', 'wpdm_filter_blogdescription', 1);
add_filter('the_content', 'wpdm_conv_tag'); 
add_filter('the_excerpt', 'wpdm_conv_tag'); 
add_action('admin_menu', 'wpdm_admin_menu');

?>
