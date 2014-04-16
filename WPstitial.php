<?php
/*
Plugin Name: WPstitial ( Intertestial Ads for WordPress )
Plugin URI: http://wpaid.net/wpstitial-interstitial-ads-for-wordpress
Description: Monetize your WordPress site with full-page interstitial ads
Author: Sam Hagin
Version: 1.0
Author URI: http://wpaid.net
*/

// declare plugin path 
global $wpstitial_path;
$wpstitial_path = plugins_url('/WPstitial/');
define('INTADS_PATH', $wpstitial_path );

//register custom post type 
function wpstitial_admin() {
register_post_type( 'wpstitial',
		array(
			'labels' => array(
				'name' => __( 'WPstitial' ),
				'singular_name' => __( 'WPstitial' )
			),
			'public' => true,
			'has_archive' => true,
			'show_in_nav_menus' => true,
			'supports' => array( 'title', 'editor'),
			'menu_icon' => plugins_url( 'images/wpstitial.png', __FILE__ ),
			'rewrite' => array('slug' => 'wpstitial'),
		)
	);
} 
add_action( 'init', 'wpstitial_admin' );


//add meta box
add_action( 'add_meta_boxes', 'wpstitial_metabox' );

function wpstitial_metabox() {
	add_meta_box( 'wpstitial_metabox', 'WPstitial Options', 'wpstitial_metabox_cb', 'wpstitial', 'side', 'default' );
}

//metabox callback
function wpstitial_metabox_cb() { 
global $post;

// Noncename needed to verify where the data originated
	echo '<input type="hidden" name="wpstitial_noncename" id="wpstitial_noncename" value="' . 
	wp_create_nonce( plugin_basename(__FILE__) ) . '" />';


	// Get ad type
	$wpstitial_type = get_post_meta($post->ID, '_wpstitial_type', true);

	// Get url for add
	$wpstitial_url = get_post_meta($post->ID, '_wpstitial_url', true);
?>

		<label for="wpstitial_type">Type</label>
		<select name="_wpstitial_type" id="wpstitial_type">
			<option value="html" <?php selected( $wpstitial_type, 'html' ); ?>>HTML</option>
			<option value="url" <?php selected( $wpstitial_type, 'url' ); ?>>URL</option>
		</select>
<br>
<label for="wpstitial_url">URL</label>
	<input type="text" name="_wpstitial_url" id="wpstitial_url" value="<? echo $wpstitial_url; ?>" />
<?php }


// save meta data
function wpstitial_metabox_save( $post_id, $post ) {

// verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times
	if ( !wp_verify_nonce( $_POST['wpstitial_noncename'], plugin_basename(__FILE__) )) {
	return $post->ID;
	}

	// Is the user allowed to edit the post or page?
	if ( !current_user_can( 'edit_post', $post->ID ))
		return $post->ID;

	// OK, we're authenticated: we need to find and save the data
	// We'll put it into an array to make it easier to loop though.
	
	$wpstitial_meta['_wpstitial_type'] = $_POST['_wpstitial_type'];
	$wpstitial_meta['_wpstitial_url'] = $_POST['_wpstitial_url'];
	
	// Add values of $events_meta as custom fields
	
	foreach ($wpstitial_meta as $key => $value) { // Cycle through the $events_meta array!
		if( $post->post_type == 'revision' ) return; // Don't store custom data twice
		$value = implode(',', (array)$value); // If $value is an array, make it a CSV (unlikely)
		if(get_post_meta($post->ID, $key, FALSE)) { // If the custom field already has a value
			update_post_meta($post->ID, $key, $value);
		} else { // If the custom field doesn't have a value
			add_post_meta($post->ID, $key, $value);
		}
		if(!$value) delete_post_meta($post->ID, $key); // Delete if blank
	}
		
}

add_action('save_post', 'wpstitial_metabox_save', 1, 2); // save the custom fields

// plugin defaults
function wpstitial_default() {
$wpstitial_view_settings = array(
                'enabled' => '0',
                'title'    => '',
                'logo'     => '',
                'timeout'    => '10', 
                'cookie'     => '',
                'color'    => '3A5CAA',
                'text'     => '000000',
                'button'    => '10AD34',

        );
           add_option( 'wpstitial_view_settings', $wpstitial_view_settings);
}

register_activation_hook(__FILE__, 'wpstitial_default');

//add settings menu
function wpstitial_submenu(){
    add_submenu_page( 'edit.php?post_type=wpstitial', 'Settings', 'Settings', 'manage_options', 'wpstitial-settings', 'wpstitial_view_settings' );
}

add_action( 'admin_menu', 'wpstitial_submenu' );
add_action('admin_init','wpstitial_options');

// register settings
function wpstitial_options() {
        register_setting('wpstitial_view_settings', 'wpstitial_view_settings');
}

// admin panel javascript
function wpstitial_admin_scripts() { 
global $pagenow, $typenow;
if($pagenow == 'edit.php' && $typenow == 'wpstitial' ) {
    wp_enqueue_script('jquery');
    wp_enqueue_script( 'wpstitial_admin', INTADS_PATH.'js/wpstitial_admin.js');
    wp_enqueue_script('jscolor', INTADS_PATH.'jscolor/jscolor.js');
	}
}

add_action( 'admin_enqueue_scripts', 'wpstitial_admin_scripts' );



//settings page
function wpstitial_view_settings(){
if (!current_user_can('manage_options'))  {
global $wpdb;
                wp_die( __('You do not have sufficient permissions to access this page.') );

           } ?>

                <!-- Display Plugin Icon, Header, and Description -->
                <div class="icon32" id="icon-options-general"><br></div>
                <h2>WPstitial Options</h2>
                <p></p>

    <!-- Beginning of the Plugin Options Form -->
                <form method="post" action="options.php">
                <?php settings_fields('wpstitial_view_settings'); ?>
                <?php global $wpstitial_options;
                $wpstitial_options = get_option('wpstitial_view_settings'); ?>
<table id="wpstitial" class="wpstitial" border="0" style="font-size: 14px" cellpadding="20">

<!--Donate-->
<p style="margin-top:15px;">
                        <p style="font-style: italic; font-weight: bold;color: #26779a; font-size: 14px">Need support and more advance features such as custom CSS, custom display options,<br> Google Analytics and much more? Check out <a href="http://wpaid.net/wpstitial-interstitial-ads-for-wordpress/" target="_blank">WPstitial Pro</a><br></p>

                </p>
<!--End-->


<tr><td width:"200">Enable Ads</td><td><span id="wpstitial_radio">
    <?php if( !isset($wpstitial_options['enabled']) ) $wpstitial_options['enabled'] == '0'; ?>
<select name="wpstitial_view_settings[enabled]">
                        <option value='1' <?php selected( $wpstitial_options['enabled'], '1'); ?> >Yes</option>
                        <option value='0' <?php selected( $wpstitial_options['enabled'], '0'); ?> >No</option>
</select>


</td></tr>
<tr><td>Header Message</td><td><input type="text" name="wpstitial_view_settings[title]" value="<?php echo $wpstitial_options['title']; ?>"/>
<br /><span style="color:#666666;margin-left:2px;">Message displayed during ad display</span>
</td></tr>

<tr><td>Logo URL</td><td><input type="text" name="wpstitial_view_settings[logo]" value="<?php echo $wpstitial_options['logo']; ?>"/>
<br /><span style="color:#666666;margin-left:2px;">Specify full url ( including http:// ) to logo ( recommended 150 x 50 px )</span>
</td></tr>

<tr><td>Ad Timeout ( in seconds )</td><td><input type="text" name="wpstitial_view_settings[timeout]" value="<?php echo $wpstitial_options['timeout']; ?>"/>
<br /><span style="color:#666666;margin-left:2px;">Number of seconds ad is displayed</span>
</td></tr> 

<tr><td>
Show Once Every ( in minutes )</td><td><input type="text" name="wpstitial_view_settings[cookie]" value="<?php echo $wpstitial_options['cookie']; ?>"/>
<br /><span style="color:#666666;margin-left:2px;">Leave blank if you wish to show ad once per session</span>
</td></tr>

<tr><td>Header Background Color</td><td><input type="text" class="ctcolor {adjust:false}" name="wpstitial_view_settings[color]" value="<?php echo $wpstitial_options['color']; ?>"/></td></tr>

<tr><td>Text Color</td><td><input type="text" class="ctcolor {adjust:false}" name="wpstitial_view_settings[text]" value="<?php echo $wpstitial_options['text']; ?>"/></td></tr>

<tr><td>Button Color</td><td><input type="text" class="ctcolor {adjust:false}" name="wpstitial_view_settings[button]" value="<?php echo $wpstitial_options['button']; ?>"/></td></tr>

</table>
<p class="submit">
<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>
</form>

<?php }

//main javascript function to load ads
$wpstitial_options = get_option('wpstitial_view_settings');
if(is_array($wpstitial_options)) extract($wpstitial_options);



// get post count
function wpstitial_post_count() {
    global $wpdb;
    $numposts = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'wpstitial' and post_status = 'publish'");
    if (0 < $numposts) $numposts = number_format($numposts);
    return $numposts;
}

// disable ads if published posts is zero
if(wpstitial_post_count() == '0' ) $enabled = '0';

// enabled and cookie is set
if($enabled == '1' && $_COOKIE['int_view'] != '1') {
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Cache-Control: no-cache");
header("Pragma: no-cache");

//add header 
add_action( 'send_headers', 'wpstitial_header_xua' );
function wpstitial_header_xua() {
	header( 'X-UA-Compatible: IE=edge,chrome=1' );
}


// dynamic header css
function wpstitial_head() { 
global $wpstitial_options;
if(is_array($wpstitial_options)) extract($wpstitial_options); ?>



<meta name="robots" content="noindex">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<style type="text/css">
#int_skip {
background: #<?php echo $button; ?>;
height: 25px;
width: 115px;
border-radius: 5px;
color: white;
font-weight: bold;
padding: 5px;
line-height: 10px;
}
#int_header { 
display: block;
background-color: #<?php echo $color; ?>;
}
#int_title, #int_timer, #int_wait {
color: #<?php echo $text; ?>;
font-weight: bold;
}
<?php echo $css; ?>
</style>
<?php echo $analytics; ?>
<?php }

add_action('wp_head', 'wpstitial_head');

//enqueue scripts and css
function wpstitial_scripts() { 
wp_enqueue_style('wpstitial-css', INTADS_PATH.'css/wpstitial.css');
wp_enqueue_script('jquery');
wp_enqueue_script('jquery-cookie', INTADS_PATH.'js/jquery.cookie.js');
}

add_action('wp_enqueue_scripts', 'wpstitial_scripts');

function wpstitial_main() { 
global $wpstitial_options;
if(is_array($wpstitial_options)) extract($wpstitial_options); 
if( empty($cookie) ) $cookie = '0'; ?>

<script>
jQuery(document).ready(function() {
jQuery('body').html("<div id='wpstitial' scroll='no' style='display:block; width:100%; height:100%; position:absolute; background:#FFFFFF; margin-top:0px; margin-left:0px; overflow: hidden; text-align: center'></div>");

// hide scrollbars
jQuery('html').css('overflow','hidden');

jQuery('#wpstitial').append('<span id="int_header"><?php if(!empty($logo)) echo '<img src="'.$logo.'" id="int_logo">'; ?><span id="int_title"><?php echo $title; ?></span><span id="int_wait">Please Wait..</span><br/><span id="int_timer"></span><br/><hr></span>');

//count down timer
var url = window.location.href;
var counter = '<?php echo $timeout; ?>';
var interval = setInterval(function() {
    counter--;
    document.getElementById('int_timer').innerHTML = counter + ' seconds';
    if (counter == 0) {

	var date = new Date();
	var minutes = '<?php echo $cookie; ?>';
	date.setTime(date.getTime() + (minutes * 60 * 1000));

	// set cookie
	if( minutes == '0') { jQuery.cookie("int_view", '1' ,{ path: '/' }); } 
	else { jQuery.cookie("int_view", '1' ,{ path: '/' , expires: date }); }


        //remove timer and display link
        jQuery('#int_wait, #int_timer').html('');
	jQuery('#int_timer').html('<a href="' + url + '" id="int_skip">SKIP AD</a>');
	clearInterval(interval);
    }
}, 1000);


<?php
//get ad from database
$args = array ( 'post_type' => 'wpstitial', 'post_status' => 'publish' , 'orderby' => 'rand' , 'posts_per_page' => 1 ) ;
$result = get_posts($args);
foreach ( $result as $wpstitial ) {
$id = $wpstitial->ID;
$type = get_post_meta( $id, '_wpstitial_type', true );
$url = get_post_meta( $id, '_wpstitial_url', true );
$ad = $wpstitial->post_content;
$ad = str_replace(array("\r","\n"), '', $ad);
$ad = str_replace("'",'"',$ad);
$ad = str_replace("/","\/",$ad);
}
?>

// set javascript variables
var type = '<?php echo $type; ?>';
var ad  = '<?php echo $ad; ?>';
var adurl = '<?php echo $url; ?>';

//display iframe or HTML content
if ( type == 'url' ) {
jQuery('<iframe />', {
name: 'int_frame',
id:   'int_frame',
width: '100%',
height: '100%',
allowTransparency: 'true',
src:  adurl,
}).appendTo('#wpstitial');
}


if (type == 'html' ) {
jQuery('#wpstitial').append('<br><br>' + ad);
}

});
</script>
<?php

if(wp_is_mobile()) {?>
<script>
jQuery(document).ready(function() {
jQuery(window).resize(function() {
var width = jQuery(window).width();
jQuery('#wpstitial').css('width', width + 'px' );
jQuery('#int_title').css('display', 'none' );
	});
jQuery('#int_title').css('display', 'none' );
jQuery('#wpstitial').css('width', width + 'px' );

if(screen.width <= 699 ) {
jQuery('#int_title').css('display', 'none' );
}
});
</script>
<?php 		}
}
add_action('wp_footer','wpstitial_main');
}
