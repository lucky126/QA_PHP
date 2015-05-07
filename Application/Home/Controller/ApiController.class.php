<?php
namespace Home\Controller;
use Think\Controller;
class ApiController extends Controller {
    /**
     * 首页列表
     */
    public function index(){
        $boards = D('board');
        $list = $boards->getParentBoards(session('?User'));
        if($list == NULL)
        {
           //no board
        }

        foreach ($list as $k => $v) {
            $list[$k]['children'] = $boards->getBoardsByParentID($v['id']);
        }
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:'GET'");
        header("Access-Control-Max-Age:'60'");
        $this->ajaxReturn($list);
    }
}