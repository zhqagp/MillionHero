<?php
use DiDom\Document;
system("clear");
$start = microtime(TRUE);
require_once './vendor/autoload.php';
require_once './aip-php-sdk-2.1.0/AipOcr.php';

// 你的 APPID AK SK
const APP_ID = '10678269';
const API_KEY = 'mOuIRvYN69Ok1GhKAHAlhEzy';
const SECRET_KEY = '7bUaGXeRMuk70cde73PUKxIcqACFbbWL';
const DEV = false;

$type = !empty($argv['1'])?$argv['1']:'bw';

$client = new AipOcr(APP_ID, API_KEY, SECRET_KEY);

$src_img = './screenshot.png';
$src_croped = './crop_1.png';
$src_small_img = './crop_small_1.png';

system("adb shell screencap -p > screenshot.png");

if(DEV){
	$middle = microtime(TRUE);
	echo '截图用时：'.($middle-$start).'秒',PHP_EOL;
}

$img_size = getimagesize($src_img);

$w = $img_size[0];
$h = $img_size[1];

if(DEV){
	echo "图片宽高:(".$w.",".$h.")",PHP_EOL;
}

// 剪裁
$source = imagecreatefrompng($src_img);
$croped = imagecreatetruecolor($w, $h);
if($type=='cd'){
	// 冲顶大会
	imagecopy($croped, $source, 0, 0, 50,300, $w-100,680);
}else if($type=='zs'){
	// 芝士超人
	imagecopy($croped, $source, 0, 0, 70,300, $w-100,900);
}else{
	// 百万英雄  $type=='bw'
	imagecopy($croped, $source, 0, 0, 70,300, $w-100,900);
}
// 保存
imagepng($croped, $src_croped);
imagedestroy($croped);

$image = file_get_contents($src_croped);

/**
//3.使用固定的公式计算新的宽高
$x = $w/2;
$y = $h/2;
//4.生成目标图像资源
$small = imagecreatetruecolor($x,$y);

//5.进行缩放
imagecopyresampled($small,$source,0,0,0,0,$x,$y,$w,$h);

// 保存
imagepng($small, $src_small_img);
imagedestroy($small);
 */

if(DEV){
	$after = microtime(TRUE);
	echo '图片处理用时：'.($after-$middle).'秒',PHP_EOL;
}
$respon = $client->basicGeneral($image);

if(DEV){
	$after_api = microtime(TRUE);
	echo 'OCR接口用时：'.($after_api-$after).'秒',PHP_EOL;
}

$titles = $respon['words_result'];
if(DEV){
	var_export($titles);
	echo PHP_EOL;
}

$ans = '';
$tmp = [];
// $mark = false;//问题是否有问号
foreach ($titles as $k => $v) {
	$tmp[] = $v['words'];
	// if(strstr($v['words'], '?') !== false){
	// 	$ans .= $v['words'];
	// 	$ans = trim($ans,'?');
	// 	// $mark = true;
	// 	break;
	// }else{
	// 	$ans .= $v['words'];
	// }
}

$select = array_slice($tmp,count($tmp)-3,3);
$ans = implode('', array_slice($tmp,0,count($tmp)-3));
$ans = preg_replace('/^[1-9]\\d*|\\./u', '', $ans);
$ans = trim($ans,'?');
echo '问题：',$ans,PHP_EOL;

// 剔除字符串左侧序号
// $ans = preg_replace('/^[1-9]\\d*|\\./u', '', $ans);
// $ans = preg_replace('/^[1-9]\\d*\\./u', '', $ans);


// 浏览器打开，不建议使用，如果前几条结果没有答案，那后面有正确答案的概率也很小
// system('open -a "/Applications/Google Chrome.app" http://www.baidu.com/s?wd='.urlencode($ans));

echo PHP_EOL,'以下为搜索结果：',PHP_EOL;

$document = new Document('http://www.baidu.com/s?wd='.urlencode($ans), true);
if(DEV){
	$after_baidu = microtime(TRUE);
	echo 'Baidu接口用时：'.($after_baidu-$after_api).'秒',PHP_EOL;
}
$posts = $document->find('.op_generalqa_answer_content');
$result = array_slice($posts,0,3);
$posts = $document->find('.result');
$result = array_merge($result,array_slice($posts,0,3));
foreach(array_slice($result,0,3) as $post) {
	echo strtr(trim($post->text(),"\n"), array(' '=>''));
    echo PHP_EOL,PHP_EOL;
}


$after_baidu = microtime(TRUE);
echo PHP_EOL,PHP_EOL,'Baidu程序用时：'.($after_baidu-$start).'秒',PHP_EOL;

$pmi = $a_count = $q_count = $qa_count = [];

// $document = new Document('http://www.baidu.com/s?wd='.urlencode($ans), true);
// $posts = $document->find('.nums');
// foreach($posts as $post) {
// 	$q_count = get_count($post->text());
// }

foreach ($select as $k => $v) {
	echo $ans.' '.$v,PHP_EOL;

	$document = new Document('http://www.baidu.com/s?wd='.urlencode($ans.' '.$v), true);
	$posts = $document->find('.nums');
	foreach($posts as $post) {
		$qa_count[$k] = get_count($post->text());
 	}

 	$document = new Document('http://www.baidu.com/s?wd='.urlencode($v), true);
	$posts = $document->find('.nums');
	foreach($posts as $post) {
		$a_count[$k] = get_count($post->text());
 	}
}

foreach ($select as $k => $v) {
	$pmi[$k] = $qa_count[$k]/$a_count[$k];
	echo PHP_EOL,'问题',($k+1),': PMI=',$pmi[$k];
}

$key = array_search(max($pmi),$pmi);
// echo '其中最大值',$key,PHP_EOL,PHP_EOL;
if(max($pmi)>1){
	unset($pmi[$key]);
	$key = array_search(max($pmi),$pmi);
}
echo PHP_EOL,PHP_EOL,'答案应该为：';
system('echo "\033[31m '.($key+1).' \033[0m"');

$end = microtime(TRUE);
echo PHP_EOL,PHP_EOL,PHP_EOL,'PMI程序用时：'.($end-$start).'秒';


function get_count($text)
{
	$text = str_replace(',', '', $text);
	$count = trim($text,'搜索工具百度为您找到相关结果约,个');
	return $count;
}

