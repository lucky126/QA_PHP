<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用入口文件

// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

//define('DIR_SECURE_FILENAME', 'default.html');

define('COMMON_PATH','./Common/');

//define('BIND_MODULE','Admin');
//define('BUILD_CONTROLLER_LIST','Index,User,Board,Topic,BBS');
//define('BUILD_MODEL_LIST','User,Board,Topic,BBS');

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG',true);

//define('BIND_MODULE', 'Board'); // 绑定Home模块到当前入口文件
//define('BIND_CONTROLLER','Index'); // 绑定Index控制器到当前入口文件

// 定义应用目录
define('APP_PATH','./Application/');
define("WEB_ROOT", dirname(__FILE__) . "/Application/");
//查询安装
if (!file_exists(dirname(__FILE__).'/Common/Conf/systemConfig.php')) {
    header("Location: Application/install/");
    exit;
}
// 引入ThinkPHP入口文件
require './ThinkPHP/ThinkPHP.php';
// 亲^_^ 后面不需要任何代码了 就是如此简单