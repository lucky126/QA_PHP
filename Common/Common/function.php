<?php
/**
 * Created by PhpStorm.
 * User: songlei
 * Date: 2015/3/6
 * Time: 9:32
 */

/*自定义函数*/
//得到加密后的密码
function getEncPassword($password)
{
    return  md5(C('AUTH_CODE') . md5($password));
}

// 判定是否
function getYesOrNo($yesOrNo)
{
    if((int)$yesOrNo==1) {
        $str = "<span class=\"YesOrNo\">是</span>";
    }else{
        $str = "";
    }

    return $str;
}

/**
 * 返回论坛楼数
 * @param $cnt 排序
 * @return string
 */
function getDiscussCount($cnt)
{
    switch($cnt)
    {
        case 1:
            $txt = '楼主';
            break;
        case 2:
            $txt = '沙发';
            break;
        case 3:
            $txt = '板凳';
            break;
        default:
            $txt = $cnt.'楼';
            break;
    }
    return $txt;

}

/**
 * 获得用户等级html显示内容
 * @return string
 */
function getGenderOption($gender)
{
    $html = '';

    foreach(C('GC.GenderNamDesc') as $k=>$v) {
        $isCheck = '';
        if ($k == $gender) {
            $isCheck = 'checked';
        }
        $html .= '<input type="radio" name="Gender" id="Gender" class="cbr cbr-blue" value="' . $k . '" ' . $isCheck . '> ' . $v . '  ';
    }

    return $html;
}

/**
 * 生成datetime
 * @return string
 */
function datetime() {
    return date('Y-m-d H:i:s');
}

/**
 * 时间差计算
 *
 * @param Timestamp $time
 * @return String Time Elapsed
 * @author Shelley Shyan
 * @copyright http://phparch.cn (Professional PHP Architecture)
 */
function time2Units ($time)
{
    $year   = floor($time / 60 / 60 / 24 / 365);
    $time  -= $year * 60 * 60 * 24 * 365;
    $month  = floor($time / 60 / 60 / 24 / 30);
    $time  -= $month * 60 * 60 * 24 * 30;
    $week   = floor($time / 60 / 60 / 24 / 7);
    $time  -= $week * 60 * 60 * 24 * 7;
    $day    = floor($time / 60 / 60 / 24);
    $time  -= $day * 60 * 60 * 24;
    $hour   = floor($time / 60 / 60);
    $time  -= $hour * 60 * 60;
    $minute = floor($time / 60);
    $time  -= $minute * 60;
    $second = $time;
    $elapse = '';

    $unitArr = array('年'  =>'year', '个月'=>'month',  '周'=>'week', '天'=>'day',
        '小时'=>'hour', '分钟'=>'minute', '秒'=>'second'
    );

    foreach ( $unitArr as $cn => $u )
    {
        if ( $$u > 0 )
        {
            $elapse = $$u . $cn;
            break;
        }
    }

    return $elapse;
}