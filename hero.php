<?php
use DiDom\Document;

$start = time();
$src_img = './screenshot.png';
$src_croped = './crop_1.png';

// system("adb shell screencap -p > screenshot.png");

require_once './aip-php-sdk-2.1.0/AipOcr.php';
require './vendor/autoload.php';
// 你的 APPID AK SK
const APP_ID = '10678269';
const API_KEY = 'mOuIRvYN69Ok1GhKAHAlhEzy';
const SECRET_KEY = '7bUaGXeRMuk70cde73PUKxIcqACFbbWL';

$client = new AipOcr(APP_ID, API_KEY, SECRET_KEY);

$img_size = getimagesize($src_img);

$w = $img_size[0];
$h = $img_size[1];

echo "xx:(".$w.",".$h.")";

// 剪裁
$source = imagecreatefrompng($src_img);
$croped = imagecreatetruecolor($w, $h);
imagecopy($croped, $source, 0, 0, 70,200, $w-70,700);

imagepng($croped, $src_croped);
imagedestroy($croped);

$image = file_get_contents($src_croped);

$respon = $client->basicGeneral($image);   #用完500次后可改respon = client.basicAccurate(image)

$titles = $respon['words_result'];

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

// tissue = ans[1:2]
// if str.isdigit(tissue):            #去掉题目索引
//      ans = ans[3:]
// else:
//      ans = ans[2:]

// print(ans)       #打印问题

echo $ans,PHP_EOL,PHP_EOL;
// var_export($titles);

// var_dump($ans);

system('open -a "/Applications/Google Chrome.app" http://www.baidu.com/s?wd='.urlencode($ans));

$document = new Document('http://www.baidu.com/s?wd='.urlencode($ans), true);

$posts = $document->find('.result');

foreach($posts as $post) {
    echo $post->text(), PHP_EOL,PHP_EOL;
}


$end = time();
echo '程序用时：'.($end-$start).'秒';






