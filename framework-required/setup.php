<?php
if (!defined('ABSPATH')) exit;

//判断框架是否已经加载
if (!class_exists('AYF')) {
    //引入设置框架
    require_once (__DIR__) . '/framework-setup.php';
    //实例化框架方法
    class AYF extends AYA_Framework_Setup
    {
        private static $instance;

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
    //启动
    AYF::instance();
}
