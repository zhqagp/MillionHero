<?php
use DiDom\Document;

$start = microtime(TRUE);
require_once './vendor/autoload.php';
require_once './aip-php-sdk-2.1.0/AipOcr.php';

// 你的 APPID AK SK
const APP_ID = '10678269';
const API_KEY = 'mOuIRvYN69Ok1GhKAHAlhEzy';
const SECRET_KEY = '7bUaGXeRMuk70cde73PUKxIcqACFbbWL';

$client = new AipOcr(APP_ID, API_KEY, SECRET_KEY);

$src_img = './screenshot.png';
$src_croped = './crop_1.png';
$src_small_img = './crop_small_1.png';

system("adb shell screencap -p > screenshot.png");

$middle = microtime(TRUE);

echo '截图用时：'.($middle-$start).'秒',PHP_EOL;

$img_size = getimagesize($src_img);

$w = $img_size[0];
$h = $img_size[1];

echo "图片宽高:(".$w.",".$h.")",PHP_EOL;

// 剪裁
$source = imagecreatefrompng($src_img);
$croped = imagecreatetruecolor($w, $h);
if($type==1){
	// 冲顶大会
	imagecopy($croped, $source, 0, 0, 70,300, $w-100,900);
}else{
	// 百万英雄
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

$after = microtime(TRUE);

echo '图片处理用时：'.($after-$middle).'秒',PHP_EOL;

$respon = $client->basicGeneral($image);

$after_api = microtime(TRUE);
echo 'OCR接口用时：'.($after_api-$after).'秒',PHP_EOL;

$titles = $respon['words_result'];

var_export($titles);
echo PHP_EOL;

$ans = '';

foreach ($titles as $k => $v) {
	if(strstr($v['words'], '?') !== false){
		$ans .= $v['words'];
		$ans = trim($ans,'?');
		break;
	}else{
		$ans .= $v['words'];
	}
}

$ans = preg_replace('/^[1-9]\\d*\\./u', '', $ans);

echo $ans,PHP_EOL,PHP_EOL;

// system('open -a "/Applications/Google Chrome.app" http://www.baidu.com/s?wd='.urlencode($ans));

$document = new Document('http://www.baidu.com/s?wd='.urlencode($ans), true);

$after_baidu = microtime(TRUE);
echo 'Baidu接口用时：'.($after_baidu-$after_api).'秒',PHP_EOL;

$posts = $document->find('.result');

foreach(array_slice($posts, 0,3) as $post) {
    echo $post->text(), PHP_EOL,PHP_EOL;
}

$end = microtime(TRUE);
echo '程序用时：'.($end-$start).'秒';

