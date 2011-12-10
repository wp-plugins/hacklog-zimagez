<?php
/**
 * $Id$
 * $Revision$
 * $Date$
 * @package Hacklog ZimageZ
 * @encoding UTF-8 
 * @author 荒野无灯 <HuangYeWuDeng> 
 * @link http://ihacklog.com 
 * @copyright Copyright (C) 2011 荒野无灯 
 * @license http://www.gnu.org/licenses/
 */

/** Load WordPress Administration Bootstrap */
define( 'IFRAME_REQUEST' , true );

$bootstrap_file = dirname(dirname(dirname(dirname(__FILE__)))). '/wp-admin/admin.php' ;
if (file_exists( $bootstrap_file ))
{
	require $bootstrap_file;
}
else
{
	echo '<p>Failed to load bootstrap.</p>';
	exit();
}

/*Check Whether User Can upload_files*/
if (!current_user_can('upload_files'))
	wp_die(__('You do not have permission to upload files.'));

if( !hacklogzz::get_session_key(hacklogzz::get_opt('login'),hacklogzz::get_opt('password')) || (-1 == hacklogzz::get_session_key(hacklogzz::get_opt('login'),hacklogzz::get_opt('password'))) )
{
	wp_die(__('Authentication failed!Please check your <strong>login</strong> and <strong>password</strong>!', hacklogzz::textdomain) );
}

@header('Content-Type: ' . get_option('html_type') . '; charset=' . get_option('blog_charset'));

################################################################################
// REPLACE ADMIN URL
################################################################################

if (function_exists('admin_url'))
{
	wp_admin_css_color('classic', __('Blue'), admin_url("css/colors-classic.css"), array('#073447', '#21759B', '#EAF3FA', '#BBD8E7'));
	wp_admin_css_color('fresh', __('Gray'), admin_url("css/colors-fresh.css"), array('#464646', '#6D6D6D', '#F1F1F1', '#DFDFDF'));
}
else
{
	wp_admin_css_color('classic', __('Blue'), get_bloginfo('wpurl') . '/wp-admin/css/colors-classic.css', array('#073447', '#21759B', '#EAF3FA', '#BBD8E7'));
	wp_admin_css_color('fresh', __('Gray'), get_bloginfo('wpurl') . '/wp-admin/css/colors-fresh.css', array('#464646', '#6D6D6D', '#F1F1F1', '#DFDFDF'));
}

wp_enqueue_script('common');
wp_enqueue_script('jquery-color');


// do not show the admin_bar 
show_admin_bar( false );
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php do_action('admin_xml_ns'); ?> <?php language_attributes(); ?>>
	<head>
		<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
		<title><?php bloginfo('name') ?> &rsaquo; hacklog-ZimageZ &#8212; WordPress</title>
<?php
wp_enqueue_style('global');
wp_enqueue_style('wp-admin');
wp_enqueue_style('colors');
wp_enqueue_style('media');
?>
		<script type="text/javascript">
			//<![CDATA[
			function addLoadEvent(func) {if ( typeof wpOnload!='function'){wpOnload=func;}else{ var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}}
			//]]>
		</script>
<?php
do_action('admin_print_styles');
do_action('admin_print_scripts');
do_action('admin_head');

?>


	<script type="text/javascript">
					/* <![CDATA[ */
					function insert_into_post(html)
					{
					var win = window.dialogArguments || opener || parent || top;
					win.send_to_editor(html+"\n\r");
					};
					/* ]]> */
	</script>
		
	</head>
	<body id="media-upload" style="width:640px;height:400px;">
		<?php
		if( isset($_POST['do_upload']))
		{
			$html = hacklogzz::do_upload();
			echo '<div class="media-item" style="margin:20px auto;">';
			echo '<div style="margin:0 200px;">';
			echo $html;
			echo '<p class="submit">';
			echo '<input type="hidden" id="zimagez_img" name="zimagez_img" value=\'' .$html. '\' />';
			echo '<input onclick="insert_into_post(document.getElementById(\'zimagez_img\').value);" type="submit" id="insertzimagez" class="button button-primary" name="insertzimagez" value="' . __('Insert into post', hacklogzz::textdomain) . '" /></p>';
			echo '<br /><a href="'. $_SERVER['PHP_SELF'] .'">Upload more?</a>';
			echo '</div>';
			echo '</div>';
			
		}
		else
		{
			hacklogzz::print_upload_form($_SERVER['PHP_SELF']);
		}
		
		?>

<?php
do_action('admin_footer');
do_action('admin_print_footer_scripts');
do_action("admin_footer-" . $GLOBALS['hook_suffix']);
?>
				
	</body>
</html>

