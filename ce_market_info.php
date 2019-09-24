<?php

ini_set("display_errors", 1);
error_reporting(E_ALL);

require_once("TwistOAuth.phar");

$consumerKey       = "xxxxx";
$consumerSecret    = "xxxxx";
$accessToken       = "xxxxx";
$accessTokenSecret = "xxxxx";

$url  = "http://voemushroom.com/2019/09/10/ce-market/";
$size = "?w=1200&h=750";

$context = stream_context_create(["http" => ["ignore_errors" => true,"timeout" => 100]]);

$sc = file_get_contents("https://s.wordpress.com/mshots/v1/"."$url"."$size", false, $context);

file_put_contents("./screenshot/hoge.jpg" , $sc);


// トリミングの工程
$file = 'screenshot/hoge.jpg';

// サムネイルになる土台の画像を作る
$thumbnail = imagecreatetruecolor(620, 150);

// 元の画像を読み込む
$baseImage = imagecreatefromjpeg($file);

// 元画像の(60, 590)の位置から620×150をコピー
imagecopyresampled($thumbnail, $baseImage, 0, 0, 60, 590, 620, 150, 620, 150);

// 保存
imagejpeg($thumbnail, "./screenshot/hogehoge.jpg");

$twitter = new TwistOAuth($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);

// 画像をアップロード
$media1 = $twitter->postMultipart('media/upload', array('@media' => 'screenshot/hogehoge.jpg'));


// ツイートするためのパラメータをセット
$parameters = [
    'status' => "CoinCxchange market-info"."\n"."＃ALIS",
    'media_ids' => implode(',', [$media1->media_id_string])
];

// ツイートを実行
$result = $twitter->post('statuses/update', $parameters);
