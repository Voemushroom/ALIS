<?php

ini_set("display_errors", 1);
error_reporting(E_ALL);

// twitter OAuth
require_once("TwistOAuth.phar");
$consumerKey       = "xxxxx";
$consumerSecret    = "xxxxx";
$accessToken       = "xxxxx";
$accessTokenSecret = "xxxxx";

// etherscan.ioから500人分のALISホルダー抽出
$url  ="https://etherscan.io/token/tokenholderchart/0xea610b1153477720748dc13ed378003941d84fab?range=500";
$kari = file_get_contents($url);
$number       = strpos("$kari","VIEWSTATEGENERATOR");
$distribution = substr("$kari",$number);
$dis = mb_convert_encoding($distribution, "UTF-8", "auto");


//トークン保有量抽出
$start       = strpos($dis,'</a></td><td>')+13;
$end         = strpos($dis,'</td><td>', $start);
$quantity_0  = substr($dis, $start, $end-$start);
$quantity_2  = preg_replace( '/,/', '', $quantity_0);

$quantity_3  = 0;
$quantity_4  = 0;

$count_2 = 1;
$count_3 = 0;
$count_4 = 0;

for($i=0; $i<444; $i++){
    $start    = strpos($dis,'</a></td><td>',$end)+13;
    $end      = strpos($dis,'</td><td>', $start);
    $number_0 = substr($dis, $start, $end-$start); 
    $number   = floatval(preg_replace( '/,/', '', $number_0));
    
    if ($number >= 1000000){
        $quantity_2 += $number;
        $count_2++;
        continue;
    }

    if ($number >= 100000){
        $quantity_3 += $number;
        $count_3++;
        continue;
    }

    if ($number >= 10000){
        $quantity_4 += $number;
        $count_4++;
    }  
}

// CEのみ例外処理
$start1       = strpos($dis,'(CoinExchange.io)</td><td>')+26;
$end1         = strpos($dis,'</td><td>', $start1);
$quan_0  = substr($dis, $start1, $end1-$start1);
$quan    = preg_replace( '/,/', '', $quan_0);

if ($quan >= 1000000){
    $quantity_2 += $quan;
    $count_2++;
    }elseif($quan >= 100000 && $quan < 1000000){
    $quantity_3 += $quan;
    $count_3++;
    }elseif($quan >= 10000 && $quan < 100000){
    $quantity_4 += $quan;
    $count_4++;
    }elseif($quan < 10000){
    $quantity_5 += $quan;
    $count_5++;
}

$quantity_5 = 75200000 - $quantity_2 - $quantity_3 - $quantity_4;


// coin割合を計算
$par_2 = round($quantity_2 / 75200000 *100 , 1)."%";
$par_3 = round($quantity_3 / 75200000 *100 , 1)."%";
$par_4 = round($quantity_4 / 75200000 *100 , 1)."%";
$par_5 = round($quantity_5 / 75200000 *100 , 1)."%";


// total holderから1万ALIS以下のホルダー数計算
$start1    = strpos($dis,">Token Holders:")+16;
$end1      = 5;
$holder_0  = substr($dis, $start1, $end1);
$holder    = preg_replace( '/,/', '', $holder_0);
$count_5   = $holder - $count_2 - $count_3 - $count_4;


// add割合を計算
$add_2 = round($count_2 / $holder *100 ,2)."%";
$add_3 = round($count_3 / $holder *100 ,2)."%";
$add_4 = round($count_4 / $holder *100 ,2)."%";
$add_5 = round($count_5 / $holder *100 ,2)."%";

// 区切り追加
$ad_2 = number_format($count_2);
$ad_3 = number_format($count_3);
$ad_4 = number_format($count_4);
$ad_5 = number_format($count_5);


$coin_2 = number_format((int)$quantity_2);
$coin_3 = number_format((int)$quantity_3);
$coin_4 = number_format((int)$quantity_4);
$coin_5 = number_format((int)$quantity_5);


//出力分作成
$tweet= "["."1,000,000 ALIS ～"."]"."\n".
        $ad_2." Add (".$add_2.")"."\n".
        $coin_2 . " ALIS (" .$par_2.")"."\n"."\n".
        "["."100,000～1,000,000 ALIS"."]"."\n".
        $ad_3." Add (".$add_3.")"."\n".
        $coin_3 . " ALIS (" .$par_3.")"."\n"."\n".
        "["."10,000～100,000 ALIS"."]" ."\n".
        $ad_4." Add (".$add_4.")"."\n".
        $coin_4 . " ALIS (" .$par_4.")"."\n"."\n".
        "["."～ 10,000 ALIS"."]"  ."\n".
        $ad_5." Add (".$add_5.")"."\n".
        $coin_5 . " ALIS (" .$par_5.")"."\n"."\n".
        "Total Holders : ".$holder_0."\n".
        "＃ALIS";
       

// Tweet実行
$twitter = new TwistOAuth($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
$result = $twitter->post("statuses/update", ["status" => "$tweet"]);


// 一応保存
$fp = fopen("distribution.txt", "w");
    fwrite($fp, "$tweet");
    fclose($fp);

// 確認用
echo $tweet;
