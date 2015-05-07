<?php
namespace Admin\Model;
use Think\Model;
class BoardModel extends Model {
    protected $_validate = array(
        array('BoardName','require','版区名称必填！'), //默认情况下用正则进行验证
    );
    protected $_auto = array(
        array('TopicNum','0'),  // 新增的时候把TopicNum字段设置为1
        array('PostNum','0'),  // 新增的时候把TopicNum字段设置为1
    );

    /**
     * 版区分页列表
     * @param $parentid 父版区ID
     * @return mixed 版区列表
     */
    public function pageList($parentid)
    {
        $boards=D('board');
        $list=$boards->where('parentid='.$parentid)->order('BoardOrder')->select();
        //文字转换，标记位判定
        foreach ($list as $k => $v) {
            $list[$k]['boardTypeTxt'] = C('GC.BoardTypeNameDesc')[$v['boardtype']];
            $list[$k]['parentBoardName'] = $this->getBoardName($v['parentid']);
            $list[$k]['canEdit'] = ($v['isleaf'] == 1) && ($v['postnum'] == 0) && ($v['topicnum'] == 0);

            foreach(D('User')->getUser(1,'id,realname,nickname',$v['boardmaster']) as $uk => $uv)
            {
                $list[$k]['masterName'] .= $uv['realname'].',';
            }
            $list[$k]['masterName'] = trim($list[$k]['masterName'],',');
        }
        return $list;
    }

    /**
     * 得到版区名称
     * @param $boardid 版区id
     * @return string 版区名称
     */
    public function getBoardName($boardid)
    {
        $boardname = "根版区";
        if($boardid > 0)
        {
            $board = M('board')->where('id='.$boardid)->find();
            $boardname = $board['boardname'];
        }
        return $boardname;
    }
}