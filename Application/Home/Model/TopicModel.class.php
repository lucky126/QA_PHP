<?php
namespace Home\Model;
use Think\Model;
class TopicModel extends Model {

    /**
     * 得到主贴信息
     * @param $topicId 主贴ID
     * @return mixed
     */
    public function GetTopic($topicId)
    {
        $topics = M('Topic');

        $map['TopicStatus'] = array('eq', 0);
        $topic = $topics->where($map)->find($topicId);

        $topic['getLock'] = $this->getLock($topic['islock']);
        $topic['getTopLevel'] = $this->getTopLevel($topic['toplevel']);
        $topic['getDigest'] = $this->getDigest($topic['isdigest']);
        $topic['getTopicFinish'] = $this->getTopicFinish($topic['isfinish']);

        return $topic;
    }

    /**
     * 查询主贴列表
     * @param $boardId 版区ID
     * @param int $pageIndex 页码
     * @return mixed
     */
    public function GetTopics($boardId, $pageIndex = 1)
    {
        $topics = M('Topic');

        $map['BoardId'] = array('eq', $boardId);
        $map['TopicStatus'] = array('eq', 0);

        $list = $topics->where($map)->order(array('PostTime' => 'desc', 'TopicType' => 'desc'))->page($pageIndex . ',10')->select();

        foreach ($list as $k => $v) {
            $list[$k]['getLock'] = $this->getLock($v['islock']);
            $list[$k]['getTopLevel'] = $this->getTopLevel($v['toplevel']);
            $list[$k]['getDigest'] = $this->getDigest($v['isdigest']);
            $list[$k]['getTopicFinish'] = $this->getTopicFinish($v['isfinish']);
        }

        return $list;
    }

    /**
     * 计算主贴总数
     * @param $boardId 版区ID
     * @return mixed
     */
    public function GetTopicsCount($boardId)
    {
        $topics = M('Topic');

        $map['BoardId'] = array('eq', $boardId);
        $map['TopicStatus'] = array('eq', 0);

        return $topics->where($map)->count();
    }

    /**
     * 搜索
     * @param $searchMode 搜索模型
     * @param int $pageIndex 页码
     */
    public function GetSearchResult($searchMode, $pageIndex = 1)
    {
        $boards = M('Board');
        $topics = M('Topic');

        if($searchMode['SearchScop'] > 0 && $boards->where('id='.$searchMode['SearchScop'])->Count() == 0)
        {
            return NULL;
        }

        //依据版区确定搜索范围
        $topicMap['TopicStatus'] = array('eq' , 0);
        if ($searchMode['SearchScop'] > 0){
            $topicMap['BoardId'] = array('eq' , $searchMode['SearchScop']);
        }
        switch ($searchMode['SearchType']) {
            case 0:
                $map['Title'] = array('LIKE' , '%'.$searchMode['SearchContext'].'%');
                $map['Keywords'] = array('LIKE' , '%'.$searchMode['SearchContext'].'%');
                $map['_logic'] = 'or';
                $topicMap['_complex'] = $map;
                break;
            case 1:
                $infoMap['Content'] = array('LIKE' , '%'.$searchMode['SearchContext'].'%');
                $infos = M('Topicinfo')->where($infoMap)->field('TopicId')->select();
                foreach($infos as $k => $v)
                {
                    $ids .= $v['topicid'].',';
                }
                $topicMap['id'] = array('IN',trim($ids,','));
                break;
            case 2:
                $infoMap['Content'] = array('LIKE' , '%'.$searchMode['SearchContext'].'%');
                $infoMap['IsAnswer'] = array('eq', 1);
                $infos = M('Topicinfo')->where($infoMap)->field('TopicId')->select();
                foreach($infos as $k => $v)
                {
                    $ids .= $v['topicid'].',';
                }
                $topicMap['id'] = array('IN',trim($ids,','));
                break;
            default:
                break;
        }

        //依据时间确定
        switch ($searchMode['PostTime'])
        {
            case 0:
                $PostTimeSearch = 'TIMESTAMPDIFF(DAY ,PostTime,now()) <= 7';
                break;
            case 1:
                $PostTimeSearch = 'TIMESTAMPDIFF(MONTH  ,PostTime,now()) <= 1';
                break;
            case 2:
                $PostTimeSearch = 'TIMESTAMPDIFF(MONTH  ,PostTime,now()) <= 3';
                break;
            case 3:
                $PostTimeSearch = 'TIMESTAMPDIFF(MONTH  ,PostTime,now()) <= 6';
                break;
            default:
                break;
        }

        $result = $topics->where($topicMap)->where($PostTimeSearch)->select();

        foreach ($result as $k => $v) {
            $result[$k]['getLock'] = $this->getLock($v['islock']);
            $result[$k]['getTopLevel'] = $this->getTopLevel($v['toplevel']);
            $result[$k]['getDigest'] = $this->getDigest($v['isdigest']);
            $result[$k]['getTopicFinish'] = $this->getTopicFinish($v['isfinish']);
        }

        return $result;
    }

    /**
     * 查询QA帖子列表
     * @param $boardId 版区ID
     * @param $qaType 类型
     * @param int $pageIndex 页码
     * @return mixed
     */
    public function GetTopicsByType($boardId, $qaType, $pageIndex = 1)
    {
        $topics = M('Topic');

        $map['BoardId'] = array('eq', $boardId);
        $map['TopicStatus'] = array('eq', 0);

        switch($qaType)
        {
            case 0:
                $map['IsDigest'] = array('eq',1);
                break;
            case 1:
                $map['IsFinish'] = array('eq',1);
                break;
            case 2:
                $map['IsFinish'] = array('eq',0);
                break;
            case 3:
                $map['Child'] = array('eq',0);
                break;
            default:
                break;
        }

        $list = $topics->where($map)->order(array('TopLevel'=>'desc','PostTime'=>'desc'))->page($pageIndex.',10')->select();
trace($list);
        foreach ($list as $k => $v) {
            $list[$k]['getLock'] = $this->getLock($v['islock']);
            $list[$k]['getTopLevel'] = $this->getTopLevel($v['toplevel']);
            $list[$k]['getDigest'] = $this->getDigest($v['isdigest']);
            $list[$k]['getTopicFinish'] = $this->getTopicFinish($v['isfinish']);
        }

        return $list;
    }

    /**
     * 计算主贴总数
     * @param $boardId 版区ID
     * @param $qaType 类型
     * @return mixed
     */
    public function GetTopicsCountByType($boardId, $qaType)
    {
        $topics = M('Topic');

        $map['BoardId'] = array('eq', $boardId);
        $map['TopicStatus'] = array('eq', 0);
        switch($qaType)
        {
            case 0:
                $map['IsDigest'] = array('eq',1);
                break;
            case 1:
                $map['IsFinish'] = array('eq',1);
                break;
            case 2:
                $map['IsFinish'] = array('eq',0);
                break;
            case 3:
                $map['Child'] = array('eq',0);
                break;
            default:
                break;
        }

        return $topics->where($map)->count();
    }

    /**
     * 得到锁定标记
     * @param $isLock 是否锁定
     * @return string
     */
    private function getLock($isLock)
    {
        $html = '';

        if($isLock == 1)
        {
            $html='<img src ="'.__ROOT__.'/Public/Images/ico_lock.gif" title="锁定" id="LockImg" />';
        }

        return $html;
    }

    /**
     * 得到置顶标记
     * @param $topLevel 置顶等级
     * @return string
     */
    private function getTopLevel($topLevel)
    {
        $html = '';

        if($topLevel >= 1)
        {
            $html='<img src ="'.__ROOT__.'/Public/Images/top.gif" title="置顶" id="TopImg" />';
        }

        return $html;
    }

    /**
     * 得到精华标记
     * @param $isDigest 是否精华
     * @return string
     */
    private function getDigest($isDigest)
    {
        $html = '';

        if($isDigest == 1)
        {
            $html='<img src ="'.__ROOT__.'/Public/Images/star.gif" title="精华" id="DigestImg" />';
        }

        return $html;
    }

    /**
     * 得到完成标记
     * @param $isFinish 是否完成
     * @return string
     */
    private function getTopicFinish($isFinish)
    {
        $imgUrl = 'Unresolved';
        $title = '未解决';

        if($isFinish == 1)
        {
            $imgUrl = 'Resolved';
            $title = '已解决';
        }

        $html='<img src ="'.__ROOT__.'/Public/Images/'.$imgUrl.'.gif" title="'.$title.'" id="FinishImg" />';

        return $html;
    }

    /**
     * 得到导航列表
     * @param $boardId 版区ID
     * @param $topicId 主贴ID
     * @param $lastNav 其他操作文本描述
     */
    public function getNav($boardId,$topicId = 0,$lastNav = '')
    {
        $boards = M('Board');
        $topics = M('Topic');
        $navArr['Topic'] = NULL;
        $navArr['LastNav']= $lastNav;

        //得到版区信息
        $board = $boards->find($boardId);
        //得到主贴信息
        if($topicId>0) {
            $topic = $topics->find($topicId);
            $navArr['Topic'] = $topic;
        }

        //递归查找父版区$parentArr
        if($boardId>0) {
            if ($board['depth'] == 0) {
                $parentArr = array($board);
            } else {
                $parent = $boards->find($board['parentid']);
                $parentArr = array($board, $parent);
            }
        }else{
            $parentArr = array();
        }

        while($parent['parnetid'] > 0)
        {
            $parent = $boards->find($parent['parentid']);
            $parentArr[]=$parent;
        }
        krsort($parentArr);
        $navArr['Boards'] = $parentArr;

        //返回导航
        return $navArr;
    }
}