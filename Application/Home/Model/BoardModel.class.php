<?php
namespace Home\Model;
use Think\Model;
class BoardModel extends Model {

    /**
     * 查询板块
     * @param $isLogin 是否登陆
     * @return mixed
     */
    public function getParentBoards($isLogin)
    {
        $boards = M('board');

        $map['ParentID'] = array('eq', 0);
        if(!$isLogin) {
            $map['IsPublic'] = array(array('eq',1));
        }

        $list = $boards->where($map)->order('BoardOrder')->select();

        return $list;
    }

    /**
     * 查询版区
     * @param $parentId 父版区ID
     * @return mixed
     */
    public function getBoardsByParentID($parentId)
    {
        $boards = M('board');

        $map['ParentID'] = array('eq', $parentId);

        $list = $boards->where($map)->order('BoardOrder')->select();

        return $list;
    }

    /**
     * 查询指定版区
     * @param $boardId 版区ID
     * @return mixed
     */
    public function getBoard($boardId)
    {
        $boards = M('board');

        $board = $boards->find($boardId);

        return $board;
    }

    /**
     * 判断是否可以访问版区
     * @param $boardId 版区id
     * @param $isLogin 是否登陆
     */
    public function canVisitBoard($boardId, $isLogin)
    {
        $board = $this->getBoard($boardId);
        if($board==NULL) {
            return false;
        }

        if(!$isLogin && $board['ispublic'] == 0)
        {
            return false;
        }

        return true;
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