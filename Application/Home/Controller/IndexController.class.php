<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    /**
     * 首页列表
     */
    public function index(){
        $boards = D('board');
        $list = $boards->getParentBoards(session('?User'));
        if($list == NULL)
        {
            $this->error('没有任何版区',U('Common/Error'),3);
        }

        foreach ($list as $k => $v) {
            $list[$k]['children'] = $boards->getBoardsByParentID($v['id']);
        }

        $this->assign('parent',$list);
        $this->assign('bid',0);
        $this->display();
    }

    /**
     * 版区列表
     */
    public function Board()
    {
        $boardId = I('get.id');
        $pageIndex = I('get.p',1);
        $qaType = I('get.qaType',2);
        //得到版区数据
        $boards = D('board');
        $data = $boards->getBoard($boardId);

        //版区不存在
        if($data==NULL)
        {
            $this->error('指定的版区不存在',U('/'),3);
        }
        //无权访问
        if(!$boards->canVisitBoard($boardId,session('?User')))
        {
            $this->error('您无权访问该版区',U('/'),3);
        }

        //得到子节点
        $data['children'] = $boards->getBoardsByParentID($data['id']);

        if($data['boardtype']==1)
        {   //QA
            $topicList = D('Topic')->GetTopicsByType($boardId,$qaType,$pageIndex);
            $count = D('Topic')->GetTopicsCountByType($boardId,$qaType);// 查询满足要求的总记录数
        }else{
            $topicList = D('Topic')->GetTopics($boardId,$pageIndex);
            $count = D('Topic')->GetTopicsCount($boardId);// 查询满足要求的总记录数
        }

        //page
        $Page = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
        $Page->setConfig('prev','<i class="fa-angle-left"></i>');
        $Page->setConfig('next','<i class="fa-angle-right"></i>');
        $show = $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出

        //赋值
        $this->assign('data',$data);
        $this->assign('bid',$boardId);
        $this->assign('qaType',$qaType);
        $this->assign('topics',$topicList);
        $this->assign('typeNameDesc',C('GC.QATypeNameDesc'));
        $this->assign('nav',D('Topic')->getNav($boardId));
        $this->assign('boardmaster',explode(',',$data['boardmaster']));


        $this->display();
    }
}