<!doctype html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title><?php echo $Title; ?> - <?php echo $Powered; ?></title>
        <link rel="stylesheet" href="./css/install.css?v=9.0" />
    </head>
    <body>
        <div class="wrap">
            <?php require './templates/header.php'; ?>
            <div class="section">
                <div class="main cc">
                    <pre class="pact" readonly="readonly">
互动交流系统后台使用协议
本后台为 <b>luckynet</b> 学习参考用，其中没有做过多数据过滤完全考虑和界面兼容处理，如果你将本通用后台使用于你自己的系统中，请自行处理（不过不会有太多的问题）。

<b>本通用后台包含以下功能：</b>

1、论坛版块版区管理
   便捷地建立版块和版区，以及版区内的分类，方便话题管理。

2、帖子内容管理
   可以对帖子完成结贴，置顶，精华，锁定等操作。

3、备份、还原数据库，打包已备份sql文件
   可以对帖子完成结贴，置顶，精华，锁定等操作。

<b>特别说明：</b>
1、本向导是直接拿 @水平凡 的项目向导修改的；
2、后面界面设计是来自互联网，排版是本人完成的；
3、本系统虽然有备份、回复数据库功能，但是为了完全建议还是执行备份。（本系统可以作为一个备份补充手动）
4、由于使用本后台系统出现任何安全问题、数据文件等其他其他与本人无关，请考虑后用。

版权所有(c)2014-2015，luckynet 保留所有权力。</a></pre>
                </div>
                <div class="bottom tac"> <a href="./index.php?step=2" class="btn">接 受</a> </div>
            </div>
        </div>
        <?php require './templates/footer.php'; ?>
    </body>
</html>