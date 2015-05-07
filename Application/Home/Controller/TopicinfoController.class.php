<?php
namespace Home\Controller;
use Think\Controller;
class TopicinfoController extends Controller {

    public function index(){

    }

    /**
     * 保存回复
     */
    public function GetQAReplySave(){
        header("Content-type: text/html;charset=utf-8");

        $topicInfo = M("Topicinfo");
        $topic = M("Topic");
        $board = M("Board");
        $user = M('User');

        //判断主贴
        if($topic->where('id='.I('post.TopicId').' AND TopicStatus = 0')->Count()==0)
        {
            $this->error('主贴不存在',U('Board',array('id' => I('post.BoardId')),''),3);
        }
        //判断用户
        //判断是否回复过
        if($topic->find(I('post.TopicId'))['boardtype'] == 1 &&
            $topicInfo->where('TopicId='.I('post.TopicId').' AND IsTopic = 0 AND PostUserId = '.session('User')['UserID'])->Count() > 0){
            $this->error('您已经回复过该贴',U('Topic/index',array('id' => I('post.TopicId')),''),3);
        }

        // 在模型中启动事务
        $topicInfo->startTrans();

        if (true) {
            //topicinfo
            $topicInfo->BoardId = I('post.BoardId');
            $topicInfo->TopicId = I('post.TopicId');
            $topicInfo->Content = I('ReplyContent','','htmlspecialchars');
            $topicInfo->PostUserId = session('User')['UserID'];
            $topicInfo->PostUserName = session('User')['NickName'];
            $topicInfo->PostTime = datetime();
            $topicInfo->PostIp = get_client_ip();
            $topicInfo->IsTopic = 0;
            $topicInfo->IsAnswer =  0;
            $topicInfo->DisplayMode = 0;
            $topicInfo->AnswerMode = 0;
            $topicInfo->Status =  0;
            //topic
            $topic->where('id='.I('post.TopicId'))->setInc('Child');
            $topic->LastPostUserId = session('User')['UserID'];;
            $topic->LastPostUserName = session('User')['NickName'];
            $topic->LastPostTime = datetime();
            $topic->where('id='.I('post.TopicId'))->save();
            //board
            $board->where('id='.I('post.BoardId'))->setInc('PostNum');
            //user
            $user->where('id='.session('User')['UserID'])->setInc('PostCnt');

            $result = $topicInfo->add();

            if ($result){
                // 提交事务
                $topicInfo->commit();
                $this->success('操作成功！', U('Topic/Index',array(id=>I('post.TopicId'))), 3);
            }else{
                // 事务回滚
                $topicInfo->rollback();
                $this->error('写入错误！');
            }
        } else {
            $this->error($topicInfo->getError());
        }
    }

    /**
     * 回帖修改保存
     */
    public function SaveReplyEdit()
    {
        header("Content-type: text/html;charset=utf-8");

        $topicInfo = M("Topicinfo");
        $topic = M("Topic");
        $user = M('User');
        //判断主贴
        if($topic->where('id='.I('post.TopicId').' AND TopicStatus = 0')->Count()==0)
        {
            $this->error('主贴不存在',U('Board',array('id' => I('post.BoardId')),''),3);
        }
        //判断用户
        //判断是否回复过
        if($topicInfo->where('id ='.I('post.BBSId').' AND PostUserId = '.session('User')['UserID'])->Count() == 0){
            $this->error('您没有回复该贴',U('Topic/index',array('id' => I('post.TopicId')),''),3);
        }

        if (true) {
            //topicinfo
            $topicInfo->Content = I('ReplyContent','','htmlspecialchars');
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
     * 显示编辑回复区域
     * @param $bbsId 帖子ID
     */
    public function GetEditReply($bbsId)
    {
        layout(false);
        $this->assign('reply',$this->GetEditMode($bbsId));

        $this->display('GetQAReply');
    }

    /**
     * 获得回复帖子模型
     * @param $bbsId 帖子ID
     * @return array
     */
    private function GetEditMode($bbsId)
    {
        //用户id
        $userId = session('User')['UserID'];
        //是否可以回复，首先必须登录
        $IsCanEditReply = session('?User');
        //得到帖子信息
        $topicInfos = D('Topicinfo');
        $data = $topicInfos->GetTopicinfo($bbsId);
        //如果是QA则必须满足若干条件
        $IsCanEditReply= $IsCanEditReply && D('Topicinfo')->CanEditReplay($data['topicid'],$bbsId,$userId);

        if($IsCanEditReply) {
            //建立数组
            $replay = array('Title' => '修改回复',
                'SubmitButtonValue' => ' 保存修改 ',
                'IsShowCloseButton' => 1,
                'CloseJSFunction' => 'Close()',
                'ActionName' => 'SaveReplyEdit',
                'PostUserId' => session('User')['UserID'],
                'TopicId' => $data['topicid'],
                'BoardId' => $data['boardid'],
                'BBSId' => $bbsId,
                'ReplyContent' => $data['content']);
        }else{
            $replay = NULL;
        }

        return $replay;
    }
}