<?php
namespace Admin\Controller;
use Think\Controller;

class TopicinfoController extends BaseController {

    public function index()
    {
        $this->display('');
    }

    /**
     * 修改答案
     */
    public function GetEditAnswer()
    {
        layout(false);
        $this->assign('reply',$this->GetEditMode(I('get.bbsId')));

        $this->display('GetQAReply');
    }

    /**
     * 获得回复帖子模型
     * @param $bbsId 帖子ID
     * @return array
     */
    private function GetEditMode($bbsId)
    {
        //得到帖子信息
        $topicInfos = D('Home/Topicinfo');
        $data = $topicInfos->GetTopicinfo($bbsId);

        //建立数组
        $reply = array('Title' => '修改正确答案',
            'SubmitButtonValue' => ' 保存修改 ',
            'IsShowCloseButton' => 1,
            'CloseJSFunction' => 'Close()',
            'ActionName' => 'GetEditAnswerSave',
            'TopicId' => $data['topicid'],
            'BoardId' => $data['boardid'],
            'BBSId' => $bbsId,
            'ReplyContent' => $data['content']);

        return $reply;
    }

    /**
     * 保存答案修改
     */
    public function GetEditAnswerSave()
    {
        header("Content-type: text/html;charset=utf-8");

        $topicInfo = M("Topicinfo");
        $topic = M("Topic");
        //判断主贴
        if($topic->where('id='.I('post.TopicId').' AND TopicStatus = 0')->Count()==0)
        {
            $this->error('主贴不存在',U('Board',array('id' => I('post.BoardId')),''),3);
        }

        //判断是否回复过
        //if($topicInfo->where('id ='.I('post.BBSId').' AND PostUserId = '.session('User')['UserID'])->Count() == 0){
        if($topicInfo->where('id ='.I('post.BBSId'))->Count() == 0){
            $this->error('您没有回复该贴',U('Topic/index',array('id' => I('post.TopicId')),''),3);
        }

        if (true) {
            //topicinfo
            $topicInfo->Content = I('ReplyContent','',false);
            $topicInfo->UpdateTime = datetime();
            $result = $topicInfo->where('id='.I('post.BBSId'))->save();

            if ($result){
                $this->success('操作成功！', U('Topic/Index',array(id=>I('post.TopicId'))), 3);
            }else{
                $this->error('写入错误！'.$topicInfo->getError());
            }
        } else {
            $this->error($topicInfo->getError());
        }
    }

    /**
     * 设置为正确答案
     */
    public function SetAnswer()
    {
        $bbsId = I('get.bbsId');

        $bbs = D('Home/Topicinfo')->GetTopicinfo($bbsId);
        $data = M('Topicinfo');

        $data->startTrans();
        //设置答案
        $data->IsAnswer = 1;
        $data->id = $bbsId;
        $result = $data->save();

        if($result)
        {
            //修改主贴标记
            $topic = M('Topic');
            $topic->id = $bbs['topicid'];
            $topic->IsFinish = 1;
            $topic->IsLock = 1;
            $result = $topic->save();
        }

        if ($result) {
            $data->commit();
            $returnInfo = '0';
        } else {
            $data->rollback();
            $returnInfo = '-1';
        }

        header("Content-type: text/html;charset=utf-8");
        $this->ajaxReturn($returnInfo,'eval');
    }

    /**
     * 保存回复
     */
    public function GetQAReplySave()
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
            $topic->IsFinish = 0;
            $topic->IsLock = 0;
            $topic->LastPostTime = datetime();
            $topic->LastPostUserId = session('Admin')['UserID'];
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
            $bbs->IsAnswer = 0;
            $bbs->DisplayMode = 0;
            $bbs->AnswerMode = I('post.AnswerMode');
            $bbs->BBSStatus = 0;
            $bbs->TopicId = I('post.TopicId');
            $result = $bbs->add();

            $topic->where('id='.I('post.TopicId'))->setInc('Child');
            $board->where('id='.I('post.BoardId'))->setInc('PostNum');

            if ($result){
                $topic->commit();
                $this->success('保存成功！', U('Topic/index',array(id => I('post.TopicId'),bid => I('post.BoardId')),''), 3);
            }else{
                $topic->rollback();
                $this->error('写入错误！');
            }
        } else {
            $this->error('写入错误！');
        }
    }

    /**
     * 修改为正确答案
     */
    public function ChangeAnswer()
    {
        $bbsId = I('get.bbsId');
        $userId = 1;

        $bbs = D('Home/Topicinfo')->GetTopicinfo($bbsId);
        $data = M('Topicinfo');

        $data->startTrans();

        //查找原先回答过的答案信息，如果存在则删除，否则就只是修改标记
        $map['TopicId'] = array('eq',$bbs['topicid']);
        $map['IsAnswer'] = array('eq',1);
        $oldAnswer = D('Topicinfo')->where($map)->find();

        if($oldAnswer['postuserid'] ==$userId )
        {
            $data->id = $oldAnswer['id'];
            $result = $data->delete();

            if($result)
            {
                $boards = M('Board');
                $boards->where('id='.$bbs['boardid'])->setDec('PostNum');
            }
        }else{
            $data->IsAnswer = 0;
            $data->id = $oldAnswer['id'];
            $result = $data->save();
        }

        if($result) {
            //设置当前帖子为正确答案
            $data->IsAnswer = 1;
            $data->id = $bbsId;
            $result = $data->save();
        }

        if ($result) {
            $data->commit();
            $returnInfo = '0';
        } else {
            $data->rollback();
            $returnInfo = '-1';
        }

        header("Content-type: text/html;charset=utf-8");
        $this->ajaxReturn($returnInfo,'eval');
    }

    /**
     * 设置隐藏
     */
    public function SetHidden()
    {
        $bbsId = I('get.bbsId');

        $bbs = D('Home/Topicinfo')->GetTopicinfo($bbsId);
        $data = M('Topicinfo');

        $newMode= $bbs['displaymode'] == 1 ? 0 : 1;
        $data->DisplayMode = $newMode;
        $data->id = $bbsId;
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
     * 删除帖子
     */
    public function Delete()
    {
        $bbsId = I('get.bbsId');

        $bbs = D('Home/Topicinfo')->GetTopicinfo($bbsId);
        $data = M('Topicinfo');

        $data->startTrans();

        $data->BBSStatus = 1;
        $data->id = $bbsId;
        $result = $data->save();

        $boards = M('Board');
        $boards->where('id='.$bbs['boardid'])->setDec('PostNum');

        if ($result) {
            $data->commit();
            $returnInfo = '0';
        } else {
            $data->rollback();
            $returnInfo = '-1';
        }

        header("Content-type: text/html;charset=utf-8");
        $this->ajaxReturn($returnInfo,'eval');
    }
}