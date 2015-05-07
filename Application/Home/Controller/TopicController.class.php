<?php
namespace Home\Controller;
use Think\Controller;
class TopicController extends Controller {

    /**
     * 主贴信息
     */
    public function index(){
        $topicId = I('get.id');
        $pageIndex = I('get.p',1);
        //得到主贴数据
        $topics = D('Topic');
        $data = $topics->GetTopic($topicId);
        if($data == NULL)
        {
            $this->error('指定的主题不存在',U('/','',''),3);
        }
        //无权访问
        if(!D('board')->canVisitBoard($data['boardid'],session('?User')))
        {
            $this->error('您无权访问该版区',U('/'),3);
        }
        //得到从帖
        if($data['topictype']==1)
        {   //QA
            $bbsList = D('Topicinfo')->GetBBSs($topicId, session('User')['UserID']);
        }else{
            $bbsList = D('Topicinfo')->GetBBSsByPage($topicId, $pageIndex);

            $count = D('Topicinfo')->GetBBSsCount($topicId);// 查询满足要求的总记录数
            //page
            $Page = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
            $Page->setConfig('prev','<i class="fa-angle-left"></i>');
            $Page->setConfig('next','<i class="fa-angle-right"></i>');
            $show = $Page->show();// 分页显示输出
            $this->assign('page',$show);// 赋值分页输出
        }

        $this->assign('data',$data);
        $this->assign('bid',$data['boardid']);
        $this->assign('keywords',explode(',',$data['keywords']));
        $this->assign('bbss',$bbsList);
        $this->assign('tid',$topicId);
        $this->assign('reply',$this->GetReplayMode($topicId));
        $this->assign('nav',D('Topic')->getNav($data['boardid'],$topicId));
        $this->display();
    }

    /**
     * 获得回复帖子模型
     * @param $topicId 主贴ID
     * @return array
     */
    private function GetReplayMode($topicId)
    {
        //用户id
        $userId = session('User')['UserID'];
        //是否可以回复，首先必须登录
        $IsCanReply = session('?User');
        //得到帖子信息
        $topics = D('Topic');
        $data = $topics->GetTopic($topicId);
        //如果是QA则必须满足若干条件
        if($data['topictype'] == 1)
        {
            $IsCanReply= $IsCanReply && D('Topicinfo')->CanReplay($topicId,$userId);
        }

        if($IsCanReply) {
            //建立数组
            $replay = array('Title' => '发表回复',
                'SubmitButtonValue' => ' 提交回复 ',
                'IsShowCloseButton' => 0,
                'CloseJSFunction' => '',
                'ActionName' => 'GetQAReplySave',
                'PostUserId' => session('User')['UserID'],
                'TopicId' => $topicId,
                'BoardId' => $data['boardid'],
                'BBSId' => 0,
                'ReplyContent' => '');
        }else{
            $replay = NULL;
        }

        return $replay;
    }

    /**
     * 发帖
     */
    public function NewTopic(){

        if(!IS_POST) {
            $bid = I('get.boardid');
            $this->assign('bid', $bid);
            //得到版区数据
            $boards = D('board');
            $data = $boards->getBoard($bid);
            $this->assign('nav',D('Topic')->getNav($bid,NULL,'我的提问'));

            //版区不存在
            if($data == NULL)
            {
                $this->error('指定的版区不存在',U('/'),3);
            }

            $this->display();
        }else{
            header("Content-type: text/html;charset=utf-8");

            $topic = M("Topic");;
            // 在模型中启动事务
            $topic->startTrans();
            // 进行相关的业务逻辑操作
            $bbs = M("Topicinfo");
            $board = M("Board");
            $user = M('User');

            if (true) {
                $topic->BoardId = I('post.BoardId');
                $topic->TopicType = M('Board')->find(I('post.BoardId'))['boardtype'];
                $topic->Title = I('Title','','htmlspecialchars');
                $topic->Keywords = I('keywords','','htmlspecialchars');
                $topic->PostUserId = session('User')['UserID'];
                $topic->PostTime = datetime();
                $topic->PostIp = get_client_ip();
                $topic->PostUserName = session('User')['NickName'];
                $topic->TopicStatus = 0;
                $topic->IsFinish = 0;
                $topic->IsLock = 0;
                $topic->IsDigest = 0;
                $topic->TopLevel = 0;
                $topic->Child = 0;
                $topic->Hits = 0;

                $bbs->BoardId = I('post.BoardId');
                $bbs->Content = I('Content','','htmlspecialchars');
                $bbs->PostUserId = session('User')['UserID'];
                $bbs->PostUserName = session('User')['NickName'];
                $bbs->PostTime = datetime();
                $bbs->PostIp = get_client_ip();
                $bbs->IsTopic = 1;
                $bbs->IsAnswer = 0;
                $bbs->DisplayMode = 0;
                $bbs->AnswerMode = 0;
                $bbs->BBSStatus = 0;

                $tid = $topic->add();
                $bbs->TopicId = $tid;
                $result = $bbs->add();

                $board->where('id='.I('post.BoardId'))->setInc('TopicNum');
                $board->where('id='.I('post.BoardId'))->setInc('PostNum');

                $user->where('id='.session('User')['UserID'])->setInc('TopicCnt');

                if ($result){
                    // 提交事务
                    $topic->commit();
                    $this->success('操作成功！', U('Index/Board',array(id=>I('post.BoardId'))), 3);
                }else{
                    // 事务回滚
                    $topic->rollback();
                    $this->error('写入错误！');
                }
            } else {
                $this->error($topic->getError());
            }
        }
    }

    /**
     * 高级搜索
     */
    public function SearchInfo()
    {
        $boardId = I('BoardId');

        $this->assign('bid',$boardId);
        $this->assign('SearchScopeHtml',$this->getSearchScope($boardId));
        $this->assign('SearchTypeHtml',$this->getSearchType());
        $this->assign('PostTimeHtml',$this->getPostTime());
        $this->assign('nav',D('Topic')->getNav($boardId,0,'高级搜索'));

        $this->display();
    }

    /**
     * 获得搜索范围html显示内容
     * @param $boardId 版区ID
     * @return string
     */
    private function getSearchScope($boardId)
    {
        $html = '<select name="SearchScope" id="SearchScope"><option value="0">请选择</option>';

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
            if($boardId==$v['id']){
                $selected = ' selected';
            }
            $html.='<option value="'.$v['id'].'" '.$selected.'>|-'.$depthName.$v['boardname'].'</option>';
        }

        $html .= '</select>';

        return $html;
    }

    /**
     * 获得搜索类型html显示内容
     * @return string
     */
    private function getSearchType()
    {
        $html = '';

        foreach(C('GC.SearchTypeNameDesc') as $k=>$v) {
            $html .= '<input type="radio" name="SearchType" id="SearchType" value="' . $k . '" > ' . $v . '  ';
        }

        return $html;
    }

    /**
     * 获得发帖时间html显示内容
     * @return string
     */
    private function getPostTime()
    {
        $html = '';

        foreach(C('GC.PostTimeTypeNameDesc') as $k=>$v) {
            $html .= '<input type="radio" name="PostTime" id="PostTime" value="' . $k . '" > ' . $v . '  ';
        }

        return $html;
    }

    /**
     * 搜索结果
     */
    public function SearchResult()
    {
        $searchContext = I('post.SearchContext');
        $boardId = I('post.BoardId');
        $SearchScope = I('post.SearchScope');
        $SearchType = I('post.SearchType');
        $PostTime = I('post.PostTime');

        $searchMode = array(
            'SearchContext' => $searchContext,
            'SearchScope' => $SearchScope==''?$boardId:$SearchScope,
            'SearchType' => $SearchType==''?0:$SearchType,
            'PostTime' =>  $PostTime==''?0:$PostTime,
        );

        $searchMode['SearchTypeText'] = C('GC.SearchTypeNameDesc')[$searchMode['SearchType']];
        $searchMode['PostTimeText'] = C('GC.PostTimeTypeNameDesc')[$searchMode['PostTime']];
        $searchMode['BoardName'] = D('board')->getBoardName($boardId);

        $topics = D('Topic');
        $data = $topics->GetSearchResult($searchMode);

        $this->assign('data',$data);
        $this->assign('bid',$boardId);
        $this->assign('Search',$searchMode);
        $this->assign('nav',D('Topic')->getNav($boardId,0,'搜索结果'));

        $this->display();
    }
}