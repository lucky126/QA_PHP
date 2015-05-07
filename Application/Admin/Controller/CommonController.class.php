<?php
namespace Admin\Controller;
use Think\Controller;
class CommonController extends Controller{


    /**
     * 后台登陆
     */
    public function Login()
    {
        if (!IS_POST) {
            layout(false);
            $this->display();
        }else{
            $user = D('user');
            $password = getEncPassword(I('post.passwd'));
            $data = $user->Login(I('post.username'), $password);

            //更新记录，创建session
            if ($data['Success']) {
                //success
                session('Admin',array('UserID' => $data['UserID'],'NickName' => $data['UserNickName']));
            }
            echo $data['Success'];
            // $this->ajaxReturn (json_encode($data));
        }
    }

    /**
     * 注销登录
     */
    public function Logout()
    {
        session('Admin',NULL);
        $this->success('注销成功',U('/Admin/'),0);
    }
}