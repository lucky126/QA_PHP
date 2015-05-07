<?php
namespace Home\Model;
use Think\Model;
class TopicinfoModel extends Model {

    /**
     * 得到帖子信息
     * @param $bbsId 帖子ID
     * @return mixed
     */
    public function GetTopicinfo($bbsId)
    {
        $topicInfos = M('Topicinfo');

        $map['BBSStatus'] = array('eq', 0);
        $topicinfo = $topicInfos->where($map)->find($bbsId);

        return $topicinfo;
    }
    /**
     * 得到QA帖子信息
     * @param $topicId 主贴ID
     * @param $CurrentUserId 当前用户ID
     * @param $isAdmin 是否是管理员
     * @return mixed
     */
    public function GetBBSs($topicId,$CurrentUserId, $isAdmin = false)
    {
        $bbss = M('Topicinfo');

        $map['TopicId'] = array('eq', $topicId);
        $map['BBSStatus'] = array('eq', 0);

        //得到主贴
        $list['TopicBBS'] = $bbss->where($map)->where('IsTopic=1')->find();
        //判断是否存在正确答案
        $list['HasAnswer'] = $bbss->where($map)->where('IsAnswer=1')->Count();
        //得到正确答案
        $list['Answer'] = $bbss->where($map)->where('IsAnswer=1')->find();
        //得到非标准答案帖子个数
        $list['AnswerCnt'] = $bbss->where($map)->where('IsTopic = 0 AND IsAnswer = 0')->Count();
        //得到非标准答案帖子
        $list['OtherBBSs'] = $bbss->where($map)->where('IsTopic = 0 AND IsAnswer = 0')->order('PostTime')->select();
        //是否结贴
        $list['IsFinish'] = 0;
        if ($list['HasAnswer']  == 1 || $list['TopicBBS']['IsFinish'] == 1 || $list['TopicBBS']['IsLock'] == 1) {
            $list['IsFinish'] = 1;
        }

        //非管理员处理逻辑
        if(!$isAdmin)
        {
            //判断正确答案是否允许所有人查阅
            if ($list['HasAnswer']  == 1  && $list['Answer']['answermode'] == 1 && $CurrentUserId !=  $list['TopicBBS']['postuserid']) {
                trace('1');
                $list['Answer']['content'] = '***仅对发帖人公开***';
            }

                //判断帖子是否被隐藏
            foreach ($list['OtherBBSs'] as $k => $v) {

                if ($list['IsFinish'] == 0 && $CurrentUserId == $v['postuserid']) {
                    $list['OtherBBSs'][$k]['IsOwner'] = 1;
                } else {
                    $list['OtherBBSs'][$k]['IsOwner'] = 0;
                }
                if ($v['displaymode'] == 1 && $CurrentUserId != $v['postuserid']) {
                    $list['OtherBBSs'][$k]['content'] = '***该内容已被管理员设置为隐藏***';
                }
            }
        }


        return $list;
    }

    /**
     * 计算论坛帖子总数
     * @param $topicId 主贴ID
     * @return mixed
     */
    public function GetBBSsCount($topicId)
    {
        $bbss = M('Topicinfo');

        $map['TopicId'] = array('eq', $topicId);
        $map['BBSStatus'] = array('eq', 0);

        return $bbss->where($map)->count();
    }

    /**
     * 得到论坛帖子信息
     * @param $topicId 主贴ID
     * @param $pageIndex 页码
     * @return mixed
     */
    public function GetBBSsByPage($topicId,$pageIndex)
    {
        $bbss = M('Topicinfo');
        $pageSize = 10;

        $map['TopicId'] = array('eq', $topicId);
        $map['BBSStatus'] = array('eq', 0);

        $list = $bbss->where($map)->order(array('PostTime'=>'asc'))->page($pageIndex.','.$pageSize)->select();

        $i=1;
        foreach($list as $k => $v)
        {
            $list[$k]['CntText'] = getDiscussCount($i + ($pageIndex - 1) * $pageSize);
            $list[$k]['Cnt'] = $i;
            $i++;
        }

        return $list;
    }

    /**
     * 判断是否可以回帖
     * @param $topicId 主贴ID
     * @param $userId 用户ID
     */
    public function CanReplay($topicId,$userId)
    {
        $bbss = M('Topicinfo');
        $topics = M('Topic');

        $map['TopicId'] = array('eq', $topicId);
        $map['BBSStatus'] = array('eq', 0);

        //如果是发帖人，或者存在当前用户的回帖，或者锁定帖子，或者帖子完结，或者已经存在正确答案则不允许回复
        $alreadyReply = $bbss->where($map)->where('PostUserId = ' . $userId . ' AND IsTopic = 0')->Count();
        $poster = $bbss->where($map)->where('PostUserId = ' . $userId . ' AND IsTopic = 1')->Count();
        $hasAnswer = $bbss->where($map)->where('IsAnswer = 1')->Count();
        $isFinish = $topics->where('id = ' . $topicId . ' AND IsFinish = 1')->Count();
        $isLock = $topics->where('id = ' . $topicId . ' AND IsLock = 1')->Count();

        $CanReplay = true;
        if ($alreadyReply > 0 || $poster == 1 || $isFinish > 0 || $isLock > 0 || $hasAnswer > 0) {
            $CanReplay = false;
        }

        return $CanReplay;
    }

    /**
     * 判断是否可以编辑回帖
     * @param $topicId 主贴ID
     * @param $bbsId 帖子ID
     * @param $userId 用户ID
     */
    public function CanEditReplay($topicId,$bbsId,$userId)
    {
        $bbss = M('Topicinfo');
        $topics = M('Topic');

        $map['id'] = array('eq', $bbsId);
        $map['BBSStatus'] = array('eq', 0);

        //如果不是发帖人，或者锁定帖子，或者帖子完结，或者已经存在正确答案则不允许回复
        $poster = $bbss->where($map)->Count();
        $hasAnswer = $bbss->where('TopicId = '.$topicId.' AND IsAnswer = 1 AND BBSStatus = 0')->Count();
        $isFinish = $topics->where('id = ' . $topicId . ' AND IsFinish = 1')->Count();
        $isLock = $topics->where('id = ' . $topicId . ' AND IsLock = 1')->Count();

        $CanEditReplay = true;
        if ($poster == 0 || $isFinish > 0 || $isLock > 0 || $hasAnswer > 0) {
            $CanEditReplay = false;
        }

        return $CanEditReplay;
    }
}