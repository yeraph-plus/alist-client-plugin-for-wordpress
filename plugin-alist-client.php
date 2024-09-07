<?php
if (!defined('ABSPATH')) exit;

/**
 * Plugin Name: AIYA-CMS 文件提取（Alist API）
 * Plugin URI: https://www.yeraph.com/
 * Description: 基于 Alist API 提取 Alist 服务器中的文件
 * Version: 1.0.0
 * Author: Yeraph Studio
 * Author URI: https://www.yeraph.com/
 * License: GPLv3 or later
 * Requires at least: 6.1
 * Tested up to: 6.5
 * Requires PHP: 7.4
 */

define('AYA_ALIST_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AYA_ALIST_PLUGIN_PATH', plugin_dir_path(__FILE__));

//引入Alist客户端组件
require_once AYA_ALIST_PLUGIN_PATH . 'lib/Http_Request.php';
require_once AYA_ALIST_PLUGIN_PATH . 'lib/Alist.php';
require_once AYA_ALIST_PLUGIN_PATH . 'lib/Alist_UpFile.php';
//设置页面
require_once AYA_ALIST_PLUGIN_PATH . 'plugin-build-framework-options.php';
//插件文件
require_once AYA_ALIST_PLUGIN_PATH . 'inc/method-public.php';
require_once AYA_ALIST_PLUGIN_PATH . 'inc/method-upload.php';
require_once AYA_ALIST_PLUGIN_PATH . 'inc/method-rest-api.php';
require_once AYA_ALIST_PLUGIN_PATH . 'inc/method-short-code.php';
