<?php
/*
Plugin Name: Hacklog ZimageZ
Plugin URI: http://ihacklog.com/?p=5072
Description: upload image to ZimageZ via WordPress.
Version: 1.0.0
Author: <a href="http://ihacklog.com/">荒野无灯</a>
Author URI: http://ihacklog.com/
*/

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

/*
 Copyright 2011  荒野无灯

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

define('HACKLOG_ZIMAGEZ_LOADER', __FILE__);
require plugin_dir_path(__FILE__) . '/hacklogzz.class.php';
hacklogzz::init();