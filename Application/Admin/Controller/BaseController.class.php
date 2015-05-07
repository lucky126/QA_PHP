<?php
namespace Admin\Controller;
use Think\Controller;
class BaseController extends Controller{
    /**
    +----------------------------------------------------------
     * 初始化
     * 如果 继承本类的类自身也需要初始化那么需要在使用本继承类的类里使用parent::_initialize();
    +----------------------------------------------------------
     */
    public function _initialize() {
        if(!session('?Admin'))
        {
            redirect(U('Common/login','',''));
        }
        $this->assign('nickname',session('Admin')['NickName']);
        $this->assign('menu',$this->show_menu());
    }

    /**
     * 显示菜单
     * @return mixed
     */
    private function show_menu()
    {
        $menu = C('admin_big_menu');
        $sub = C('admin_sub_menu');

        foreach($menu as $index => $name)
        {
            $menu[$index]['menu_class'] = '';
            $menu_sub = array();
            foreach($sub[$index] as $url => $subname)
            {
                $created_sub['url'] = $url;
                $created_sub['subname'] = $subname;
                $created_sub['sub_class'] = '';
                if($this->getCheckUrl() == $url)
                {
                    $menu[$index]['menu_class'] = ' class="active opened active"';
                    $created_sub['sub_class'] = ' class="active"';
                }
                array_push($menu_sub,$created_sub);
            }
            $menu[$index]['sub'] = $menu_sub;
        }

        return $menu;
    }

    /**
     * 得到菜单高亮检查所用的url
     * @return string
     */
    private function getCheckUrl()
    {
        $url = CONTROLLER_NAME.'/'.ACTION_NAME;

        if('add' == ACTION_NAME && 'Board' == CONTROLLER_NAME )
        {
            $url = 'Board/index';
        }
        if('edit' == ACTION_NAME && 'Board' == CONTROLLER_NAME )
        {
            $url = 'Board/index';
        }
        if('Board' == ACTION_NAME && 'Board' == CONTROLLER_NAME )
        {
            $url = 'Board/SelectBoard';
        }
        if('index' == ACTION_NAME && 'Topic' == CONTROLLER_NAME )
        {
            $url = 'Board/SelectBoard';
        }
        if(I('usertype') == 1 && 'User' == CONTROLLER_NAME)
        {
            $url='User/ManagerList';
        }
        if(I('usertype') == 0 && 'User' == CONTROLLER_NAME)
        {
            $url='User/UserList';
        }
        return $url;
    }
}