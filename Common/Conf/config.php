<?php
return array(
    // 添加数据库配置信息
    'DB_TYPE'   => 'mysql', // 数据库类型

    // 加载扩展配置文件
    'LOAD_EXT_CONFIG' => array('GC'=>'globalConst','systemConfig'),

    //是否自动开启Session
    'SESSION_AUTO_START' => true,
    //分组
    'APP_GROUP_LIST'    => 'Home,Admin',     //项目分组设定
    'DEFAULT_GROUP'    => 'Home',     //默认分组
    // 显示页面Trace信息
    'SHOW_PAGE_TRACE' =>true,
    //应用调试模式状态
    'APP_STATUS' => 'debug',
    'PAGE_TRACE_SAVE'=>true,

    'URL_HTML_SUFFIX'=>'html',
    //模版
    'LAYOUT_ON'=>true,
    'LAYOUT_NAME'=>'layout',
    //模板编译缓存
    'TMPL_CACHE_ON'         =>  false,        // 是否开启模板编译缓存,设为false则每次都会重新编译
    //URL访问模式
    'URL_MODEL'             =>  2,       // URL访问模式,可选参数0、1、2、3,代表以下四种模式：
);