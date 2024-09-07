<?php

if (!defined('ABSPATH')) exit;

//引入设置框架
require_once (__DIR__) . '/framework-setup.php';

if (!class_exists('AYF')) {
    class AYF extends AYA_Framework_Setup
    {
        private static $instance;
        //实例化
        public static function instance()
        {
            if (is_null(self::$instance)) new self();
        }
        //初始父类方法
        public function __construct()
        {
            parent::__construct();
        }
    }
}

//启动
AYF::instance();
