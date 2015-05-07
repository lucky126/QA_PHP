<?php
$conf = array(
    'admin_big_menu' => array(
        'Board' => array('版区管理','cog'),
        'Topic' => array('帖子管理','desktop'),
        'User'=>array('用户管理','user'),
    ),
    'admin_sub_menu' => array(
        'Board' => array(
            'Board/index' => '版区维护',
            'Board/Add' => '新增板块',
        ),
        'Topic' => array(
            'Board/SelectBoard' => '帖子维护',
            'Topic/Keywords' => '关键词管理',
        ),
        'User' => array(
            'User/UserList' => '用户列表',
            'User/ManagerList' => '管理员列表',
        ),
    ),
);
return array_merge(include COMMON_PATH.'/Conf/config.php',$conf);