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
iframe_header( __('Hacklog ZimageZ',hacklogzz::textdomain), false );
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
iframe_footer();
?>