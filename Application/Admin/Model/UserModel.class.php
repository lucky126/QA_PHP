<?php
namespace Admin\Model;
use Think\Model;
class UserModel extends Model {
    protected $_validate = array(
        array('LoginName','require','请输入登录名！'),
        array('NickName','require','请输入昵称！'),
        array('Password','require','请输入密码！'),
        array('RealName','require','请输入真实姓名！'),

        array('LoginName','/^\w{6,50}$/','登录名必须6个字母以上，50个字母以下',0,'regex',3),
        array('NickName','/^[a-z0-9\x{4e00}-\x{9fa5}]{2,50}$/u','昵称必须50个字母以下',0,'regex',3),
        array('Password','/^\w{6,50}$/','密码必须6个字母以上，50个字母以下',0,'regex',3),
        array('RealName','/^[a-z0-9\x{4e00}-\x{9fa5}]{2,50}$/u','真实姓名必须50个字母以下',0,'regex',3),
        // 在新增的时候验证字段是否唯一
        array('LoginName','','登录名已经存在！',0,'unique',1),
        array('NickName','','昵称已经存在！',0,'unique',1),
    );

    public function Login($userName,$password)
    {
        $user = M('user');

        $map['LoginName']=array('eq',$userName);
        $map['UserLevel']=array('gt',1);
        $map['UserStatus']=array('eq',0);
        $cnt = $user->where($map)->Count();

        $result = array('Success' => 0,
            'UserID' => 0,
            'UserNickName' => '',
            'ErrorMsg' =>  '');

        if($cnt < 1)
        {
            //指定账户不存在
            $result['ErrorMsg'] = '指定账户不存在';
        }else{
            //匹配密码
            $data = $user->where($map)->select();
            //dump($data);
            if($data[0]['password'] != $password)
            {
                //密码错误
                $result['ErrorMsg'] = '密码错误';
            }
            else
            {
                //更新登录
                $user->LastLoginTime = datetime();
                $user->LoginCnt ++;
                $updateResult = $user->where('id='.$data[0]['id'])->save();

                $result['UserNickName'] = $data[0]['nickname'];
                $result['UserID'] = $data[0]['id'];
                $result['Success'] = 1;
            }
        }

        return $result;
    }

    /**
     * 获取用户列表
     * @param $isGetAdmin 是否获取管理员
     * @return mixed
     */
    public function userList($isGetAdmin)
    {
        $Users = M('user');

        $map['UserStatus'] = array('eq', 0);
        if($isGetAdmin) {
            $map['UserLevel'] = array(array('gt',1),array('lt',4));
        }else{
            $map['UserLevel'] = array('elt', 1);
        }

        $list = $Users->where($map)->select();
        //文字转换
        foreach ($list as $k => $v) {
            $list[$k]['UserLevelTxt'] = C('GC.LevelNameDesc')[$v['userlevel']];
            $list[$k]['GenderTxt'] = C('GC.GenderNamDesc')[$v['gender']];
        }

        return $list;
    }

    /**
     * 获得用户列表
     * @param $userLevel 用户等级
     * @param string $field 筛选显示的字段
     * @param string $userlist 筛选的用户id列表
     * @return mixed 用户列表
     */
    public function getUser($userLevel, $field = '', $userlist = '')
    {
        $Users = D('user');

        $map['UserLevel'] = array('gt',$userLevel);

        if($userlist != '') {
            $map['id'] = array('in', $userlist);
        }

        if($field == '') {
            $list = $Users->where($map)->select();
        }else{
            $list = $Users->where($map)->field($field)->select();
        }

        return $list;
    }

    /**
     * 得到用户列表
     * @param string $userList 以选择用户列表
     * @return string 用户列表
     */
    public function getUserForBoardMaster($userList = '')
    {
        $data = $this->getUser(1,'id,realname,nickname');

        $html = '';
        foreach ($data as $k => $v) {
            $selected = '';
            if(stripos(','.$userList.',', $v['id']) > 0)
            {
                $selected = ' selected';
            }
            $html .= ' <option value="'.$v['id'].'" '.$selected.'>'.$v['realname'].'('.$v['nickname'].')'.'</option>';
        }

        return $html;
    }
}