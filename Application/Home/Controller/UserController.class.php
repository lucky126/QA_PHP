<?php
namespace Home\Controller;
use Think\Controller;
class UserController extends Controller {
    public function index(){
        $id = I('get.id');
        $data = M('user');
        $usr = $data->find($id);

        $this->assign('data',$usr);

        $this->display();
    }

    /**
     * 登录
     */
    public function Login()
    {
        if(!IS_POST) {
            session('User',NULL);
            $this->assign('Error','');
            $this->display();
        }else{
            layout(false);
            $user = D('user');
            $Password = getEncPassword(I('Password'));
            $data = $user->Login(I('post.UserName'), $Password);

            //更新记录，创建session
            if ($data['Success']) {
                //success
                session('User',array('UserID' => $data['UserID'],'NickName' => $data['UserNickName']));

                $this->success('登陆成功',U('/'),3);
            } else {
                $this->error($data['ErrorMsg'],U('User/Login'),3);
            }
            $this->display();
        }
    }

    /**
     * 注销
     */
    public function Logout()
    {
        session('User',NULL);
        $this->success('注销成功',U('/'),0);
    }

    /**
     * 注册
     */
    public function Register(){
        if(!IS_POST) {
            $this->assign('genderHtml', getGenderOption(0));

            $this->display();
        }else{
            header("Content-type: text/html;charset=utf-8");
            $data = D('user');
            if ($data->create()) {
                $data->Password = getEncPassword(I('Password'));
                $data->AddTime = datetime();
                $data->AddIp = get_client_ip();
                $data->UserLevel = 0;
                $result = $data->add();

                if($result) {
                    $this->success('注册成功！', U('/'), 3);
                } else {
                    $this->error('注册失败！');
                }

            } else {
                $this->error($data->getError());
            }
        }
    }
}