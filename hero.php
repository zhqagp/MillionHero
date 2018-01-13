<?php
use DiDom\Document;
require_once './vendor/autoload.php';
require_once './aip-php-sdk-2.1.0/AipOcr.php';

$start = microtime(TRUE);
$src_img = './screenshot.png';
$src_croped = './crop_1.png';
$src_small_img = './crop_small_1.png';
system("adb shell screencap -p > screenshot.png");

// 你的 APPID AK SK
const APP_ID = '10678269';
const API_KEY = 'mOuIRvYN69Ok1GhKAHAlhEzy';
const SECRET_KEY = '7bUaGXeRMuk70cde73PUKxIcqACFbbWL';

$client = new AipOcr(APP_ID, API_KEY, SECRET_KEY);

$img_size = getimagesize($src_img);

$w = $img_size[0];
$h = $img_size[1];

echo "图片宽高:(".$w.",".$h.")",PHP_EOL;

// 剪裁
$source = imagecreatefrompng($src_img);
$croped = imagecreatetruecolor($w, $h);
imagecopy($croped, $source, 0, 0, 70,300, $w-100,900);
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

$respon = $client->basicGeneral($image);   #用完500次后可改respon = client.basicAccurate(image)

$titles = $respon['words_result'];

var_export($titles);

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

$posts = $document->find('.result');

foreach(array_slice($posts, 0,3) as $post) {
    echo $post->text(), PHP_EOL,PHP_EOL;
}

$end = microtime(TRUE);
echo '程序用时：'.($end-$start).'秒';



