<?php
/*
Plugin Name: Retaggr
Plugin URI: http://www.retaggr.com/
Description: Retaggr - Adds Retaggr social/business cards to your comments
Author: Retaggr
Version: 1.15
Author URI: http://www.retaggr.com/
 * Min WP Version: 2.0.4
 * Max WP Version: 2.7.1
*/


add_action('admin_head', 'ri_css');
add_action('wp_head', 'ri_css');
add_action('the_author', 'ri_author');

function ri_author($author){
	if (is_feed()) return $author;
	
	$email = get_the_author_email();
	if ( !empty( $email ) ) {
		$md5 = md5( strtolower( $email ) );
		$ret = "<span class='retaggrCard md5_" . $md5  . "' >" . $author . "</span>";
		return $ret;
	}
	
	
	return $author;
}


function ri_css() {
	$plugindir = get_option('siteurl') . '/wp-content/plugins/' . dirname(plugin_basename(__FILE__));
	$ri_css = $plugindir . '/retaggr.css';
	
?>
<link rel="stylesheet" type="text/css" href="<?php echo $ri_css; ?>" />
<?php

}


function ri_addCommentHint($postID){
	echo "<div class='retaggrInfo'><a target='_blank' href='http://www.retaggr.com/WhatIs'><img alt='retaggr' title='This site is Retaggr enabled' src='http://content.retaggr.com/static/retaggrEnabled.gif' /></a></div>";
}


add_action('comment_form', 'ri_addCommentHint');
add_action('init', 'ri_set_filter');

function ri_set_filter() {
	add_filter('get_comment_author_link', 'ri_retaggrcomment');
}


function ri_defaults() {
	return array(
		'ri_siteid' => ''
	);
}

// return array of plugin options, using defaults if necessary:
function ri_get_options() {
	$ri_siteid = get_option('ri_siteid');
	
	$defaults = ri_defaults();
	if(empty($ri_siteid)) $ri_siteid = $defaults['ri_siteid'];
		
	return array(
		'ri_siteid' => $ri_siteid
	);
}




function ri_retaggrcomment($text) {
	if (is_feed()) return $text;

	
	global $comment;
	// emit retaggr card if email present
	
	if (!(empty($comment->comment_author_email) ) ){
		$md5 = md5( strtolower( $comment->comment_author_email ) );

		$text  = "<span class='retaggrCard md5_" . $md5  . "' >" . $text . "</span>";
	}


	return $text;
}


function ri_wp_footer(){
	$opts = ri_get_options();
	$ri_siteid = $opts['ri_siteid'];
	echo "<script language='javascript' src='http://www.retaggr.com/Script/GetScript.ashx?siteID=" . $ri_siteid . "'></script>";
}

add_action('wp_footer', 'ri_wp_footer');

add_action('admin_menu', 'ri_opt_menu');

function ri_opt_menu() {
	add_options_page('retaggr', 'retaggr', 'manage_options', 'retaggroptions', 'reIdent_options_page');
	
}

add_action('admin_menu', 'ri_opt_menu');
add_action('admin_notices', 'ri_admin_notices');

function ri_admin_notices(){
	$opts = ri_get_options();
	//$ri_opt_siteid = $opts['ri_siteid'];
	
	if (empty($_POST['ri_siteid']) && empty($opts['ri_siteid'])){
	?>
	<div class="error">
		One more step needed to enable Retaggr. Please visit the Retaggr section in your Wordpress settings.
	</div>
	<?php
	}
}
	
// Udate options page:
function reIdent_options_page() {
	// defaults
	
	if( $_POST[ 'ri_submitted' ] == 'Y' ) {
		check_admin_referer('update-options');
		// Read their posted value
		$ri_siteid = $_POST['ri_siteid'];
		
		update_option( 'ri_siteid', $ri_siteid );
		

?>
<div class="updated"><p><strong><?php _e('Options saved.'); ?></strong></p></div>
<?php
	} // endif
	
	$opts = ri_get_options();

	// Get existing option values:
	$ri_opt_siteid = $opts['ri_siteid'];
	
// Admin form:
?>
<div class="wrap">
<h2><?php _e( 'Retaggr Plugin Options'); ?></h2>


<form id="reIdent" name="ri_opts" method="post"  >
<p class="submit">
<input type="submit" name="Submit" value="<?php _e('Update Options') ?>" />
</p>

<?php wp_nonce_field('update-options') ?>
<input type="hidden" name="ri_submitted" value="Y">

<fieldset id="general">
<legend><?php _e("General Options"); ?></legend>

<?php if (empty($ri_opt_siteid)){ ?>

	<p class="error">
		Hi! 
		To get Retaggr to work, you need a Retaggr site ID.  <br /><br />You can get this site ID (and configure options about how you want Retaggr to work on your site) <a href="http://www.retaggr.com/GetSiteID" target="_blank">here</a>
	</p>
	

<?php } ?>


<p>
<label for="ri_siteid"><?php _e("retaggr Site ID:"); ?></label>
<input type="text" name="ri_siteid" id="ri_siteid" value="<?php echo $ri_opt_siteid; ?>" size="40">

</p>

<?php if (!empty($ri_opt_siteid)){ ?>

<br />

<p>



<a href="http://www.retaggr.com/Site/List">Change other settings at Retaggr</a>


</p>



<?php } ?>


</fieldset>

<p class="submit">
<input type="submit" name="Submit" value="<?php _e('Update Options' ) ?>" />
</p>

</form>
</div>

<?php
 
}


?>
