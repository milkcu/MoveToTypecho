<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$ispost = true;
	$success = 0;
	$count = 0;

	$one = 1;
	$author = (int)$_POST['author'];
	$category = (int)$_POST['category'];

	$xml = simplexml_load_file($_POST['filename'], null, LIBXML_NOCDATA);
	$xml->registerXPathNamespace('dc', 'http://purl.org/dc/elements/1.1/');
	$items = $xml->xpath('/rss/channel/item');
	$count = count($items);
	
	$sdbpre = $_POST['prefix'];

	$sdb = new SaeMysql();

	// 遍历文章
	foreach ($items as $item)
	{
		// 文章标题
		$title = (string)$item->title;

		// 发布时间
		$time = strtotime($item->pubDate) - (8 * 3600);

		// 文章内容
		$content = (string)$item->description;
		
		$sql = 'insert into ' . $sdbpre . 'contents(title, slug, created, modified, text, authorId, allowComment, allowPing, allowFeed) '.
		       "VALUES('".$sdb->escape($title)."', '".$time."', '".$time."', '".$time."', '".$sdb->escape($content)."', '".$author."', '".$one."', '".$one."', '".$one."')";
		$sdb->runSql($sql);
		if( $sdb->errno() != 0 )
		{
			die( "Error:" . $sdb->errmsg() );
		}
		$cid = $sdb->lastId();
		$sql = 'insert into '.$sdbpre."relationships(cid, mid) values('".$cid."','".$category."')";
		$sdb->runSql($sql);
		if( $sdb->errno() != 0 )
		{
			die( "Error:" . $sdb->errmsg() );
		}
		$success++;
	}
	$sdb->closeDb();
}
else
{
	$ispost = false;
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>cnblogs to typecho</title>
	</head>
	<body>
		<?php if ($ispost == false) { ?>
			<h1>cnblogs to typecho</h1>
			<hr>
			<form method="post">
				<table>
					<tr>
						<td>博客园 XML 文件名：</td>
						<td>
							<input type="text" name="filename" value="cnblogs.xml">
							<span>请将该文件放在本页面相同目录！</span>
						</td>
					</tr>
					<tr>
						<td>typecho 数据表前缀：</td>
						<td>
							<input type="text" name="prefix" value="typecho_">
						</td>
					</tr>
					<tr>
						<td>导入后文章所属作者：</td>
						<td>
							<input type="text" name="author" value="1">
							<span>1 为默认第一个用户，如果没有创建过其它用户，则为 1 默认</span>
						</td>
					</tr>
					<tr>
						<td>导入后文章所属分类标签：</td>
						<td>
							<input type="text" name="category" value="1">
							<span>填写导入后文章所属的分类，默认为 1</span>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<input type="submit" value="确 定 导 入" style="width:110px; height:35px; font-size:14px;">
							<span style="color:#C33;">注意：本程序没有经过大范围测试，请自行做好 typecho 的备份！</span>
						</td>
					</tr>
				</table>
			</form>
		<?php } else { ?>
			<div style="padding:15px 20px; background:#DBEDDB; border:1px solid #3C933E; color:#2E7931;">
				在 XML 文件中发现 <?php echo $count; ?> 篇文章，已成功导入 <?php echo $success; ?> 篇！<br>
				现在去你的博客看看奇迹发生了没有吧！
			</div>
		<?php }	?>
		<hr>
		<p>code by <a href="http://www.abelyao.com/" target="_blank" style="text-decoration:none; color:#0A8CD2;">abelyao</a> & <a href="http://www.milkcu.com" target="_blank">MilkCu</a> @ 2014</p>
	</body>
</html>
