<?php
namespace Admin\Controller;
use Think\Controller;
class UserController extends BaseController {

    /**
     * 用户列表
     */
    public function UserList()
    {
        $this->CommonList(false);
    }

    /**
     * 管理员列表
     */
    public function ManagerList()
    {
        $this->CommonList(true);
    }

    /**
     * 获取用户列表
     * @param $isGetAdmin 是否查询管理员
     */
    private function CommonList($isGetAdmin)
    {
        $data = D('user');
        $list = $data->userList($isGetAdmin);
        $title = $isGetAdmin?'管理员':'用户';
        $title .= '列表';

        $this->assign('list',$list);
        $this->assign('title',$title);
        $this->assign('userType',$isGetAdmin);

        $this->display('User');
    }

    /**
     * 显示用户信息
     */
    public function ShowUser()
    {
        $this->GetUserInfo(false);
    }

    /**
     * 编辑用户信息
     */
    public function EditUser()
    {
        if(!IS_POST) {
            $this->GetUserInfo(true);
        }else{
            header("Content-type: text/html;charset=utf-8");
            $data = D('user');
            if ($data->create()) {
                $data->Password = getEncPassword(I('post.Password'));
                $data->updateTime=datetime();
                $result = $data->save();

                if($result) {
                    if($_POST['userType']==1) {
                        $this->success('操作成功！', U('ManagerList'), 3);
                    }else{
                        $this->success('操作成功！', U('UserList'), 3);
                    }
                } else {
                    $this->error('写入错误！');
                }

            } else {
                $this->error($data->getError());
            }
        }
    }

    /**
     * 获得用户信息
     * @param $isEdit 是否编辑
     */
    public function GetUserInfo($isEdit)
    {
        $id = I('get.id');
        $userType = I('get.usertype');
        $data = M('user');
        $usr = $data->find($id);

        $title = $isEdit?'编辑':'查看';
        $title .= '用户信息';

        $this->assign('data',$usr);
        $this->assign('title',$title);
        $this->assign('userType',$userType);
        $this->assign('userLevelHtml',$this->getUserLevelOption($usr['userlevel']));
        $this->assign('genderHtml',getGenderOption($usr['gender']));

        if($isEdit) {
            $this->display('EditUser');
        }else{
            $this->display('ShowUser');
        }
    }

    /**
     * 新增用户
     */
    public function AddUser()
    {
        if(!IS_POST) {
            $title = '新增用户信息';
            $this->assign('title',$title);
            $this->assign('userLevelHtml',$this->getUserLevelOption(0));
            $this->assign('genderHtml',getGenderOption(0));
            $this->display();
        }else {
            header("Content-type: text/html;charset=utf-8");
            $data = D('user');
            if ($data->create()) {
                $data->Password = getEncPassword(I('post.Password'));
                $data->AddTime=datetime();
                $data->AddIp=get_client_ip();
                $result = $data->add();

                if($result) {
                    if($_POST['userType']==1) {
                        $this->success('操作成功！', U('ManagerList'), 3);
                    }else{
                        $this->success('操作成功！', U('UserList'), 3);
                    }
                } else {
                    $this->error('写入错误！');
                }

            } else {
                $this->error($data->getError());
            }
        }
    }

    /**
     * 删除用户
     */
    public function Delete()
    {
        foreach($_POST as $k => $v)
        {
            if(strpos($k,"_")>0) {
                $id = substr($k,strpos($k,"_")+1);

                $User = M("User"); // 实例化User对象
                // 要修改的数据对象属性赋值
                $data['UserStatus'] = -1;
                $User->where('id='.$id)->save($data); // 根据条件更新记录
            }
        }

        if($_POST['userType']==1) {
            $this->success('操作成功！', U('ManagerList'), 3);
        }else{
            $this->success('操作成功！', U('UserList'), 3);
        }
    }

    /**
     * 修改密码
     */
    public function ChangePwd()
    {
        if (!IS_POST) {
            $title = '修改密码';
            $this->assign('title', $title);
            $this->display();
        } else {
            header("Content-type: text/html;charset=utf-8");
            $data = D('user');

            $checkMap['Password'] =  getEncPassword(I('OldPassword'));
            $checkMap['id'] = session('Admin')['UserID'];
            if ($data->where($checkMap)->count() != 1) {
                $this->error('密码输入有误！');
            }

            if (I('NewPassword') != I('RePassword')) {
                $this->error('两次密码输入不一致！');
            }

            $data->Password = getEncPassword(I('NewPassword'));
            $data->updateTime=datetime();
            $result = $data->where('id=' . session('Admin')['UserID'])->save();

            if ($result) {
                $this->success('操作成功！', U('/Admin/'), 3);
            } else {
                $this->error('写入错误！');
            }
        }
    }

    /**
     * 获得用户等级html显示内容
     * @return string
     */
    private function getUserLevelOption($userLevel)
    {
        $html = '';

        foreach(C('GC.LevelNameDesc') as $k=>$v) {
            if ($k > 1) {
                $isCheck = '';
                if ($k == $userLevel) {
                    $isCheck = 'checked';
                }
                $html .= '<input type="radio" name="UserLevel" id="UserLevel" class="cbr cbr-blue" value="' . $k . '" ' . $isCheck . '> ' . $v . '  ';
            }
        }

        return $html;
    }

}