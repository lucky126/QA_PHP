<?php
namespace Admin\Controller;
use Think\Controller;
class TopicController extends BaseController {

    /**
     * 帖子信息
     */
    public function index(){
        $topicId = I('get.id');
        $pageIndex = I('get.pageIndex');
        //得到主贴数据
        $topics = D('Home/Topic');
        $data = $topics->GetTopic($topicId);
        if($data == NULL)
        {
            $this->error('指定的主题不存在',U('/','',''),3);
        }

        //得到从帖
        if($data['topictype']==1)
        {   //QA
            $bbsList = D('Home/Topicinfo')->GetBBSs($topicId, session('User')['UserID']);
        }else{
            $bbsList = D('Home/Topicinfo')->GetBBSsByPage($topicId, $pageIndex);
            //建立数组
            $reply = array('Title' => '发表回复',
                'SubmitButtonValue' => ' 提交回复',
                'IsShowCloseButton' => 0,
                'CloseJSFunction' => '',
                'ActionName' => 'GetQAReplySave',
                'TopicId' => $topicId,
                'BoardId' => $data['boardid'],
                'BBSId' => 0,
                'ReplyContent' => '');
            $this->assign('reply',$reply);
        }

        $this->assign('data',$data);
        $this->assign('bid',$data['boardid']);
        $this->assign('keywords',explode(',',$data['keywords']));
        $this->assign('bbss',$bbsList);
        $this->assign('tid',$topicId);
       // $this->assign('reply',$this->GetReplayMode($topicId));

        $this->assign('title','帖子管理');
        $this->display();
    }

    /**
     * 删除帖子
     */
    public  function Delete()
    {
        header("Content-type: text/html;charset=utf-8");
        $data = D('Topic');
        $board = D('Board');

        $data->startTrans();
        $result = 1;
        foreach(I('post.') as $k =>$v)
        {
            if($k != 'AllCheck' && $result == 1) {

                $child = $data->where('id='.$v)->count();

                $data->id = $v;
                $data->TopicStatus = 1;
                $data->UpdateTime = datetime();
                $result = $data->save();

                $board->where('id='.I('post.BoardId'))->setDec('TopicNum');
                $board->where('id='.I('post.BoardId'))->setDec('PostNum',($child+1));
            }
        }

        if ($result) {
            $data->commit();
            $this->success('操作成功！', U('Board/Board',array(id => I('get.bid')),''), 3);
        } else {
            $data->rollback();
            $this->error('写入错误！');
        }
    }

    /**
     * 得到置顶设置信息
     * @param $boardId 版区ID
     * @param $topicId 主贴ID
     */
    public function ListTop($boardId,$topicId)
    {
        layout(false);
        $data = D('Home/Topic')->GetTopic($topicId);

        //得到版区内所有置顶信息
        $topics = M('Topic');
        $map['BoardId'] = array('eq', $boardId);
        $map['TopicStatus'] = array('eq', 0);
        $list = $topics->where($map)->field('TopLevel')->select();
        $arr = ',';
        foreach($list as $k => $v)
        {
            if($v['toplevel'] > 0) {
                $arr .= $v['toplevel'] . ',';
            }
        }

        $this->assign('topicId',$topicId);
        $this->assign('levelHtml',$this->getTopLevelOption($data['toplevel'],$arr));
        $this->display();
    }

    /**
     * 得到置顶列表html内容
     * @param $currentLevel 当前置顶等级
     * @param $useLevels 已经被使用的置顶等级
     * @return string
     */
    private function getTopLevelOption($currentLevel,$useLevels)
    {
        $html = '<select id="TopLevel" name="TopLevel" class="form-control"><option value="0">不置顶</option>';
        for($i = 1; $i < 6; $i++) {
            $isCheck = '';
            if ($i == $currentLevel) {
                $isCheck = ' selected';
            }
            $levelName = $i;

            if(strpos($useLevels,$i.',') > 0)
            {
                $levelName .= '(已设置)';
            }

            $html .= '<option value="' . $i . '" ' . $isCheck . '> ' . $levelName . '</opiton>';
        }
        $html .= '</select>';
        return $html;
    }

    /**
     * 保存置顶等级
     */
    public function SetTop()
    {
        $topicId = I('TopicId');
        $TopLevel = I('TopLevel');

        header("Content-type: text/html;charset=utf-8");
        $data = D('Topic');

        $data->TopLevel = $TopLevel;
        $data->UpdateTime = datetime();
        $result = $data->where('id=' . $topicId)->save();

        if ($result) {
            $returnInfo = $TopLevel;
        } else {
            $returnInfo = '-1';
        }
        header("Content-type: text/html;charset=utf-8");
        $this->ajaxReturn($returnInfo,'eval');
    }

    /**
     * 设置精华
     */
    public function SetDigest()
    {
        $topicId = I('get.TopicId');

        $topic = D('Home/Topic')->GetTopic($topicId);
        $data = M('Topic');

        $newDigest = $topic['isdigest'] == 1 ? 0 : 1;
        $data->IsDigest = $newDigest;
        $data->id = $topicId;
        $result = $data->save();

        if ($result) {
            $returnInfo = '0';
        } else {
            $returnInfo = '-1';
        }

        header("Content-type: text/html;charset=utf-8");
        $this->ajaxReturn($returnInfo,'eval');
    }

    /**
     * 设置锁定
     */
    public function SetLock()
    {
        $topicId = I('get.TopicId');

        $topic = D('Home/Topic')->GetTopic($topicId);
        $data = M('Topic');

        $newLock = $topic['islock'] == 1 ? 0 : 1;
        $data->IsLock = $newLock;
        $data->id = $topicId;
        $result = $data->save();

        if ($result) {
            $returnInfo = '0';
        } else {
            $returnInfo = '-1';
        }

        header("Content-type: text/html;charset=utf-8");
        $this->ajaxReturn($returnInfo,'eval');
    }

    /**
     * 录入正确答案
     */
    public function GetAnswer()
    {
        layout(false);
        $topicId = I('get.TopicId');
        $topic = D('Home/Topic')->GetTopic($topicId);

        $this->assign('BoardId',$topic['boardid']);
        $this->assign('TopicId',$topic['id']);
        $this->assign('AnswerMode',$this->getAnswerModeOption());
        $this->display();
    }

    /**
     * 保存正确答案
     */
    public function GetAnswerSave()
    {
        header("Content-type: text/html;charset=utf-8");
        $bbs = M("Topicinfo");
        $topic = M("Topic");
        $board = M("Board");
        $user = M('User');
        //判断主贴
        if($topic->where('id='.I('post.TopicId').' AND TopicStatus = 0')->Count()==0)
        {
            $this->error('主贴不存在',U('Board',array('id' => I('post.BoardId')),''),3);
        }

        if (true) {
            $topic->startTrans();
            //topicinfo
            $topic->id=I('post.TopicId');
            $topic->IsFinish = 1;
            $topic->IsLock = 1;
            $topic->LastPostTime = datetime();
            $topic->LastPostUserId = session('Admin')['UserID'];;
            $topic->LastPostUserName = session('Admin')['NickName'];
            $topic->UpdateTime = datetime();
            $result = $topic->save();

            $bbs->BoardId = I('post.BoardId');
            $bbs->Content = I('ReplyContent','',false);
            $bbs->PostUserId = session('Admin')['UserID'];
            $bbs->PostUserName = session('Admin')['NickName'];
            $bbs->PostTime = datetime();
            $bbs->PostIp = get_client_ip();
            $bbs->IsTopic = 0;
            $bbs->IsAnswer = 1;
            $bbs->DisplayMode = 0;
            $bbs->AnswerMode = I('post.AnswerMode');
            $bbs->BBSStatus = 0;
            $bbs->TopicId = I('post.TopicId');
            $result = $bbs->add();

            $topic->where('id='.I('post.TopicId'))->setInc('Child');
            $board->where('id='.I('post.BoardId'))->setInc('PostNum');

            if ($result){
                $topic->commit();
                $returnInfo = '0';
            }else{
                $topic->rollback();
                $returnInfo = '-1';
            }
        } else {
            $returnInfo = '-1';
        }

        $this->ajaxReturn($returnInfo,'eval');
    }

    /**
     * 得到答案显示方式html
     * @return string
     */
    private function getAnswerModeOption()
    {
        $html = '';
        foreach (C('GC.ModeNameDesc') as $k => $v) {
            $html .= '<input type="radio" name="AnswerMode" id="AnswerMode" value="' . $k . '" > ' . $v . '  ';
        }

        return $html;
    }
}