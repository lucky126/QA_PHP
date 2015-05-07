<?php
namespace Admin\Controller;
use Think\Controller;

class BoardController extends BaseController {

    /**
     * 列表
     */
    public function index(){
        $parentid = I('get.parentid','0');
        $boards = D('board');
        $list = $boards->pageList($parentid);

        //判断是否是根节点，只有parentid为0才是
        $this->isRoot = 0;
        if( $parentid == 0) {
            $this->isRoot = 1;
        }

        $pid = 0;
        $BoardName = '系统';
        $boardTypeName = '板块';

        if($parentid>0)
        {
            $board = M('board')->where('id='.$parentid)->find();
            $pid = $board['parentid'];
            $BoardName = $board['boardname'];
            $boardTypeName = '板区';
        }
        $title = $BoardName." 所属".$boardTypeName."列表";

        $this->assign('pid',$pid);
        $this->assign('boardTypeName',$boardTypeName);
        $this->assign('list',$list);// 赋值数据集
        $this->assign('title',$title);
    
        $this->display();
    }

    /**
     * 新增
     */
    public function Add(){
        if(!IS_POST)
        {   //编辑页面
            $parentid = I('get.parentid','0');
            $boardTypeName = "版块";
            $editTypeName = "新增";

            if ($parentid > 0) {
                $board = M('board')->where('id='.$parentid)->find();
                $boardTypeName = "版区";
                if ($board['depth'] > 0) {
                    $boardTypeName = "分类";
                }
            }
            $title = $editTypeName . $boardTypeName;

            $this->assign('masterList', D('User')->getUserForBoardMaster());
            $this->assign('parentName', D('board')->getBoardName($parentid));
            $this->assign('boardTypeHtml',$this->getBoardTypeOption(($parentid == 0),2));
            $this->assign('parentid', $parentid);
            $this->assign('title', $title);
            $this->assign('boardTypeName', $boardTypeName);

            $this->display();
        }else{
            //保存页面
            header("Content-type: text/html;charset=utf-8");
            $data = D('board');
            if ($data->create()) {
                $parentOrder = 0;
                $parentParentStr = '';
                if($data->ParentID>0) {
                    $parent = M('board')->find($data->ParentID);
                    $data->Depth = $parent['depth'] + 1;
                    $data->RootID = $parent['rootid'];
                    $parentOrder = $parent['boardorder'];
                    $parentParentStr = $parent['parentstr'];
                }else{
                    $data->Depth = 0;
                    $data->RootID = 0;
                }

                //新增的版区顺序号取所有同级中最大的加一
                if((int)M('board')->where('ParentID='.$data->ParentID)->count()>0)
                {
                    $maxOrder = M('board')->where('ParentID='.$data->ParentID)->Max('BoardOrder');
                    //如果取得的最大序号所对应的节点还包含子节点，则需要取得该节点所有子节点中最大的一个序号
                    $maxOrderData = M('board')->where('BoardOrder = '.$maxOrder)->find();
                    if($maxOrderData['isleaf'] == 0)
                    {
                        $wherelike['ParentStr'] = array('LIKE',$maxOrderData['parentstr'].'%');
                        $maxOrder = M('board')->where($wherelike)->Max('BoardOrder');
                    }
                }else{
                    $maxOrder = $parentOrder;
                }
                //为此,所有比此顺序号大的均需要加一处理
                M('board')->where('BoardOrder>'.$maxOrder)->setInc('BoardOrder');

                if($data->ParentID>0) {
                    M('board')->where('id='.$data->ParentID)->setField('IsLeaf',0);
                }

                $data->BoardMaster = I('BoardMaster');
                $data->ParentStr = $parentParentStr.','.$data->ParentID;
                $data->BoardOrder = $maxOrder + 1;
                $data->IsPublic = 0;
                $data->IsLeaf = 1;
                if((int)$_POST['IsPublic'][0] == 1)
                {
                    $data->IsPublic = 1;
                }

                $result = $data->add();
                if ($result) {
                    $this->success('操作成功！',U('index'),3);
                } else {
                    $this->error('写入错误！');
                }
            } else {
                $this->error($data->getError());
            }
        }
    }

    /**
     * 修改
     */
    public function Edit(){
        if(!IS_POST) {
            $id = I('get.id','0');
            $boardTypeName = "版块";
            $editTypeName = "修改";

            if ($id > 0) {
                $data = M('board')->find($id);
                $parentid = $data['parentid'];
                if ($parentid > 0) {
                    $board = M('board')->where('id=' . $parentid)->find();
                    $boardTypeName = "版区";
                    if ($board['depth'] > 0) {
                        $boardTypeName = "分类";
                    }
                }

                $this->assign('masterList', D('User')->getUserForBoardMaster($data['boardmaster']));
                $this->assign('parentName', D('board')->getBoardName($parentid));
                $this->assign('boardTypeHtml',$this->getBoardTypeOption(($parentid == 0),$data['boardtype']));
                $this->assign('parentid', $parentid);
                $this->assign('data', $data);
            }
            $title = $editTypeName . $boardTypeName;

            $this->assign('title', $title);
            $this->assign('boardTypeName', $boardTypeName);

            $this->display();
        }else{
            header("Content-type: text/html;charset=utf-8");

            $data = D('board');
            if ($data->create()) {
                $data->IsPublic = 0;
                $data->BoardMaster = I('BoardMaster');
                if((int)$_POST['IsPublic'][0] == 1)
                {
                    $data->IsPublic=1;
                }
                $data->UpdateTime=datetime();
                $result = $data->save();

                if($result) {
                    $this->success('操作成功！',U('index'),3);
                } else {
                    $this->error('写入错误！');
                }

            } else {
                $this->error($data->getError());
            }
        }
    }

    /**
     * 显示版区
     */
    public function Board()
    {
        $id = I('get.id');
        $pageIndex = I('get.p',1);
        $qaType = I('get.qaType',2);
        $board = D('board')->find($id);

        if($board == NULL)
        {
            $this->error('指定的版区不存在',U('/'),3);
        }

        if($board['boardtype']==1)
        {   //QA
            $topicList = D('Home/Topic')->GetTopicsByType($id,$qaType,$pageIndex);
            $count = D('Home/Topic')->GetTopicsCountByType($id,$qaType);// 查询满足要求的总记录数
        }else{
            $topicList = D('Home/Topic')->GetTopics($id,$pageIndex);
            $count = D('Home/Topic')->GetTopicsCount($id);// 查询满足要求的总记录数
        }

        //page
        $Page = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
        $Page->setConfig('prev','<i class="fa-angle-left"></i>');
        $Page->setConfig('next','<i class="fa-angle-right"></i>');
        $show = $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出


        $this->assign('data',$board);
        $this->assign('bid',$id);
        $this->assign('qaType',$qaType);
        $this->assign('topics',$topicList);
        $this->assign('typeNameDesc',C('GC.QATypeNameDesc'));
        $this->assign('title','帖子管理（版区：'.$board['boardname'].'）');
        $this->display();
    }

    /**
     * 帖子管理第一步--版区列表
     */
    public function SelectBoard()
    {
        if(!IS_POST) {
            $this->assign('title', '帖子管理');
            $this->assign('BoardList', $this->getBoardList());
            $this->display();
        }else{
            $boardId = I('post.BoardId');

            if($boardId=='') {
                $this->error('请选择版区！');
            }else {
                $this->redirect('Board', array('id' => $boardId));
            }
        }
    }

    /**
     * 得到版区列表
     * @return string
     */
    private function getBoardList()
    {
        $html = '<select class="form-control" name="BoardId" id="BoardId"><option value="">请选择</option>';

        $boards = D('Board')->order('BoardOrder')->select();

        foreach($boards as $k => $v)
        {
            $depthName = '';
            $depth = 0;
            while($depth < $v['depth'])
            {
                $depthName = "──".$depthName;
                $depth++;
            }
            $selected = '';
            $html.='<option value="'.$v['id'].'">|-'.$depthName.$v['boardname'].'</option>';
        }

        $html .= '</select>';

        return $html;
    }

    /**
     * 显示版区类型html部分
     * @param $isRoot 是否是根节点
     * @param $boardType 版区类型ID
     * @return string html代码
     */
    private function getBoardTypeOption($isRoot,$boardType)
    {
        $html = '';
        if($isRoot==0)
        {
            foreach(C('GC.BoardTypeNameDesc') as $k=>$v)
            {
                if($k>0) {
                    $isCheck = '';
                    if ($k == $boardType) {
                        $isCheck = 'checked';
                    }
                    $html .= '<input type="radio" name="BoardType" id="BoardType" class="cbr cbr-blue" value="'.$k.'" ' . $isCheck . '> ' . $v . '  ';
                }
            }
        }else{
            $html='<input type="radio" name="BoardType" id="BoardType" class="cbr cbr-blue" value="'.$boardType.'" checked> '.C('GC.BoardTypeNameDesc')[$boardType];
        }
        return $html;
    }
}