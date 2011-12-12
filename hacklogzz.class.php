<?php
/**
 * $Id$
 * $Revision$
 * $Date$
 * @package Hacklog ZimageZ
 * @encoding UTF-8
 * @author 荒野无灯　<HuangYeWuDeng>
 * @link http://ihacklog.com
 * @copyright Copyright (C) 2011 荒野无灯
 * @license http://www.gnu.org/licenses/
 */
class hacklogzz 
{
	const textdomain = 'hacklogzz';
	const opt = 'hacklogzz';
	const input_name = 'hacklog_zimagez_upload';
	//max filesize is 4MiB,1024*1024*4
	const max_filesize = 4194304;
	private static  $_opts=array(
			'login'=>'',
			'password'=>'',
			'timeout'=>60,
			);

	function init() {
		self::$_opts = get_option(self::opt,self::$_opts);
		register_activation_hook(HACKLOG_ZIMAGEZ_LOADER, array(__CLASS__, 'my_activation'));
		register_deactivation_hook(HACKLOG_ZIMAGEZ_LOADER, array(__CLASS__, 'my_deactivation'));
		// add editor button
		add_action('media_buttons',  array(__CLASS__,'add_media_button'), 20);
		//menu
		add_action('admin_menu', array(__CLASS__, 'plugin_menu'));
	}
	
	public static function get_opt($key,$default='')
	{
		$opts = get_option(self::opt,self::$_opts);
		return !empty($opts[$key]) ? $opts[$key] : $default;
	}
	/**
	 * do the stuff once the plugin is installed
	 * @static
	 * @return void
	 */
	public static function my_activation()
	{
		add_option(self::opt, self::$_opts);
	}
	
	/**
	 * do cleaning stuff when the plugin is deactivated.
	 * @static
	 * @return void
	 */
	public static function my_deactivation()
	{
		delete_option(self::opt);
	}
	
	public static function add_media_button()
	{
		$url = WP_PLUGIN_URL . '/hacklog-zimagez/hacklogzz-upload.php?TB_iframe=true&width=640&height=400';
		$admin_icon = WP_PLUGIN_URL . '/hacklog-zimagez/images/icon.png';
		if (is_ssl())
			$url = str_replace('http://', 'https://', $url);
		echo '<a href="' . $url . '" class="thickbox" title="' . __('Hacklog ZimageZ', self::textdomain) . '"><img src="' . $admin_icon . '" alt="' . __('Add Download', self::textdomain) . '"></a>';
	}
	
	/**
	 * add menu page
	 * @see http://codex.wordpress.org/Function_Reference/add_options_page
	 * @static
	 * @return void
	 */
	public static function plugin_menu()
	{
		add_options_page(__('Hacklog ZimageZ Options', self::textdomain), __('Hacklog ZimageZ', self::textdomain), 'manage_options', HACKLOG_ZIMAGEZ_LOADER, array(__CLASS__, 'plugin_options')
		);
	}
	
	
	public static function get_image_url($filename,$type='orig')
	{
		$url = 'http://www.zimagez.com/';
		switch($type)
		{
			case 'thumb':
				$url .= "miniature/{$filename}.jpg";
				break;
			case 'orig':
			default:
				$url .= "zimage/{$filename}.php";
				break;
		}
		return $url;
	}
	
	public static function get_image_html($filename)
	{
		$_POST['title'] = empty($_POST['title'])? $filename : $_POST['title'];
		$_POST['title'] = htmlspecialchars($_POST['title']);
		return '<a href="'. self::get_image_url($filename,'orig'). 
		'" target="_blank" title="'. $_POST['title']. '"><img src="'. self::get_image_url($filename,'thumb'). '" alt="'. $_POST['title']. '" /></a>';
	}
	
	public static function get_session_key($login,$password)
	{
		include_once(ABSPATH . WPINC . '/class-IXR.php');
		include_once(ABSPATH . WPINC . '/class-wp-http-ixr-client.php');
		$irx_client = new WP_HTTP_IXR_Client('http://www.zimagez.com/apiXml.php',false,false,60);
		if ($irx_client->query(
				'apiXml.xmlrpcLogin',
				$login,
				str_rot13(strrev($password))
		))
		
		{
			return $irx_client->getResponse();
		}
		else
		{
			return -1;
		}
	}
	
	public static function do_upload()
	{
		include_once(ABSPATH . WPINC . '/class-IXR.php');
		include_once(ABSPATH . WPINC . '/class-wp-http-ixr-client.php');		
		$irx_client = new WP_HTTP_IXR_Client('http://www.zimagez.com/apiXml.php',false,false,self::get_opt('timeout'));
		if ($irx_client->query(
				'apiXml.xmlrpcLogin',
				self::get_opt('login'),	
				str_rot13(strrev(self::get_opt('password')))
				))

		{
			$session_key = $irx_client->getResponse();
			if( UPLOAD_ERR_OK == $_FILES[self::input_name]['error'])
			{
				if( filesize($_FILES[self::input_name]['tmp_name']) > self::max_filesize )
				{
					wp_die(sprintf(__('Error!The image file size is > %d'),self::max_filesize));
				}
				$irx_client->query('apiXml.xmlrpcUpload',base64_encode(file_get_contents($_FILES[self::input_name]['tmp_name'])),$_FILES[self::input_name]['name'],$_POST['title'],$_POST['file_des'],	"$session_key");
				$image_filename = $irx_client->getResponse();
				return self::get_image_html($image_filename);
			}
			else
			{
				wp_die(__('Upload failed!'));
			}
		}
		else
		{
			wp_die(__('Authentication failed!'));
		}
	}
	
	public static function get_max_excution_time()
	{
		return ini_get('max_execution_time');
	}
	
	public static function get_max_input_time()
	{
		return ini_get('max_input_time');
	}
	
	
	public static function get_max_upload_size()
	{
		$upload_maxsize = self::get_ini_size(ini_get('upload_max_filesize'));
		$post_maxsize = self::get_ini_size(ini_get('post_max_size'));
		if ($upload_maxsize < $post_maxsize)
			return $upload_maxsize;
		else
			return $post_maxsize;
	}
	
	
	/**
	 * if the php.ini size option value is not numeric
	 * @param int or string $value
	 * @return int
	 */
	public static function get_ini_size($value)
	{
		if (!is_numeric($value))
		{
			if (strpos($value, 'M') !== false)
			{
				$value = intval($value) * 1024 * 1024;
			}
			elseif (strpos($value, 'K') !== false)
			{
				$value = intval($value) * 1024;
			}
			elseif (strpos($value, 'G') !== false)
			{
				$value = intval($value) * 1024 * 1024 * 1024;
			}
		}
		return $value;
	}
	
	public static function format_filesize($size) {
		$sizes = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
		if ($size == 0) {
			return('n/a');
		} else {
			return (round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $sizes[$i]);
		}
	}
		
	public static function print_upload_form($action)
	{
		?>
		<form method="post"
			  action="<?php echo $action; ?>"
			  enctype="multipart/form-data">
			  <input type="hidden" name="MAX_FILE_SIZE"
											 value="<?php echo self::get_max_upload_size(); ?>" />
			<div class="wrap">
				<div id="icon-hacklog-zimage" class="icon32"><br />
				</div>
				<h2><?php _e('Upload image to ZimageZ',self::textdomain); ?></h2>
				<table class="form-table">
					<tr>
						<td valign="top"><strong><?php _e('File:', hacklogzz::textdomain) ?></strong></td>
						<td>
							<!-- Upload File --> 
							<input type="file" name="<?php echo self::input_name;?>" size="60" dir="ltr" />&nbsp;&nbsp;
 							<br />
							<small><?php printf(__('Maximum file size is <strong>%s</strong>.', hacklogzz::textdomain), hacklogzz::format_filesize(self::get_max_upload_size())); ?></small>
							<small><?php printf(__('Maximum upload time is <strong>%s seconds</strong>.', hacklogzz::textdomain), self::get_max_input_time()); ?></small>
						</td>
					</tr>
					<tr>
						<td><strong><?php _e('Title:', hacklogzz::textdomain); ?></strong></td>
						<td><input type="text" size="50" maxlength="200" name="title" /></td>
					</tr>
					<tr>
						<td valign="top"><strong><?php _e('File Description:', hacklogzz::textdomain); ?></strong></td>
						<td><textarea rows="5" cols="50" name="file_des"></textarea></td>
					</tr>
				
					<tr>
						<td colspan="2" align="center"><input type="submit" name="do_upload"
															  value="<?php _e('Upload', hacklogzz::textdomain); ?>"
															  class="button" />&nbsp;&nbsp;<input type="button" name="cancel"
															  value="<?php _e('Cancel', hacklogzz::textdomain); ?>"
															  class="button" onclick="javascript:history.go(-1)" /></td>
					</tr>
				</table>
			</div>
		</form>
		<?php
	}

	
	private static function show_message($message, $type = 'e')
	{
		if (empty($message))
			return;
		$font_color = 'e' == $type ? '#FF0000' : '#4e9a06';
		$html = '<!-- Last Action --><div id="message" class="updated fade"><p>';
		$html .= "<span style='color:{$font_color};'>" . $message . '</span><br />';
		$html .= '</p></div>';
		echo $html;
	}
	
	
	private static function update_options()
	{
		$opts = get_option(self::opt);
		$keys = array_keys(self::$_opts);
		foreach( $keys as $k)
		{
			if( !empty($_POST[$k]))
			{
				if( 'timeout' == $k)
				{
					$_POST[$k] = $_POST[$k] > 0 ? $_POST[$k] : 60;
					$_POST[$k] = (int) $_POST[$k];
				}
				$opts[$k] = addslashes($_POST[$k]);
			}
		}
		return update_option(self::opt,$opts);
	}
	/**
	 * option page
	 * @static
	 * @return void
	 */
	public static function plugin_options()
	{
		$msg = '';
		$msg_type = 'm';
		//update options
		if (isset($_POST['submit']))
		{
			$_POST['login'] = trim($_POST['login']);
			$_POST['password'] = trim($_POST['password']);
			if (self::update_options())
			{
				$msg = __('Options updated.', self::textdomain);
				$msg_type = 'm';
			}
			else
			{
				$msg = __('Nothing changed.', self::textdomain);
				$msg_type = 'e';
			}
			if( !self::get_session_key($_POST['login'],$_POST['password']) || (-1 == self::get_session_key($_POST['login'],$_POST['password'])) )
			{
				$msg .= '<br />' . __('Authentication failed!', self::textdomain);
			}
			else
			{
				$msg .= '<br />' . __('Authenticated successfully.', self::textdomain);
			}
		}
		?>
	<div class="wrap">
	<?php screen_icon(); ?>
	<h2> <?php _e('Hacklog ZimageZ Options', self::textdomain) ?></h2>
	<?php
	self::show_message($msg, $msg_type);
	?>
	<form name="form1" method="post"
	action="<?php echo admin_url('options-general.php?page=' . plugin_basename(HACKLOG_ZIMAGEZ_LOADER)); ?>">
	<table width="100%" cellpadding="5" class="form-table">
	<tr valign="top">
	<th scope="row"><label for="login"><?php _e('Login', self::textdomain) ?>:</label></th>
	<td>
	<input name="login" type="text" class="regular-text" size="100" id="login"
	value="<?php echo self::get_opt('login'); ?>"/>
	<span class="description"><?php _e('your User ID on <a href="http://en.zimagez.com" target="_blank">ZimageZ.com</a>', self::textdomain) ?></span>
	</td>
	</tr>

	<tr valign="top">
	<th scope="row"><label for="password"><?php _e('Password', self::textdomain) ?>:</label></th>
	<td>
	<input name="password" type="password" class="regular-text" size="60" id="password"
	value="<?php echo self::get_opt('password'); ?>"/>
	<span class="description"><?php _e('the password.', self::textdomain) ?></span>
	</td>
	</tr>
	<tr valign="top">
	<th scope="row"><label for="timeout"><?php _e('Timeout', self::textdomain) ?>:</label></th>
	<td>
	<input name="timeout" type="text" class="small-text" size="30" id="timeout"
	value="<?php echo self::get_opt('timeout'); ?>"/>
	<span class="description"><?php _e('XMLRPC connection timeout.', self::textdomain) ?></span>
	</td>
	</tr>
	
	</table>
	<p class="submit">
	<input type="submit" class="button-primary" name="submit"
	value="<?php _e('Save Options', self::textdomain) ?> &raquo;"/>
	</p>
	</form>
	</div>
	<div class="wrap">
<p align="center">powered by <a href="http://ihacklog.com/?p=5072" target="_blank">Hacklog<br />
</a>
<a href="http://www.zimagez.com/" title="Hebergement d'images" target="_blank"><img src="http://www.zimagez.com/images/bannieres/zimagez-80x15-01a.png" alt="ZimageZ: Hebergeur d'images" width="80" height="15" border="0" /></a>
</p>
	
	</div>
	<?php
	}
}//end class