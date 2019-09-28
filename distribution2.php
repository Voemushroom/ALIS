<?php

ini_set("display_errors", 1);
error_reporting(E_ALL);

// twitter OAuth
require_once("TwistOAuth.phar");
$consumerKey       = "XXXXX";
$consumerSecret    = "XXXXX";
$accessToken       = "XXXXX";
$accessTokenSecret = "XXXXX";

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


// 1万ALIS以下のホルダー数計算
$start1    = strpos($dis,">Token Holders:")+16;
$end1      = 5;
$holder_0  = substr($dis, $start1, $end1);
$holder    = preg_replace( '/,/', '', $holder_0);
$count_5   = $holder - $count_2 - $count_3 - $count_4;


// add割合を計算
$add_2 = round($count_2 / $holder *100 ,1)."%";
$add_3 = round($count_3 / $holder *100 ,1)."%";
$add_4 = round($count_4 / $holder *100 ,1)."%";
$add_5 = round($count_5 / $holder *100 ,1)."%";


$ad_2 = number_format($count_2);
$ad_3 = number_format($count_3);
$ad_4 = number_format($count_4);
$ad_5 = number_format($count_5);


$coin_2 = number_format((int)$quantity_2);
$coin_3 = number_format((int)$quantity_3);
$coin_4 = number_format((int)$quantity_4);
$coin_5 = number_format((int)$quantity_5);

//前日と比較
$searray = unserialize(file_get_contents("./distribution/distribution.dat"));

if ($searray[0] === $ad_2){
    $fugou_0 = "-) ";
}elseif ($searray[0] <= $ad_2){
    $fugou_0 = "↑) ";
}else{
    $fugou_0 = "↓) ";
}

if ($searray[1] === $coin_2){
    $fugou_1 = "-) ";
}elseif ($searray[0] <= $coin_2){
    $fugou_1 = "↑) ";
}else{
    $fugou_1 = "↓) ";
}

if ($searray[2] === $ad_3){
    $fugou_2 = "-) ";
}elseif ($searray[2] <= $ad_3){
    $fugou_2 = "↑) ";
}else{
    $fugou_2 = "↓) ";
}

if ($searray[3] === $coin_3){
    $fugou_3 = "-) ";
}elseif ($searray[3] <= $coin_3){
    $fugou_3 = "↑) ";
}else{
    $fugou_3 = "↓) ";
}

if ($searray[4] === $ad_4){
    $fugou_4 = "-) ";
}elseif ($searray[4] <= $ad_4){
    $fugou_4 = "↑) ";
}else{
    $fugou_4 = "↓) ";
}

if ($searray[5] === $coin_4){
    $fugou_5 = "-) " ;
}elseif ($searray[5] <= $coin_4){
    $fugou_5 = "↑) ";
}else{
    $fugou_5 = "↓) ";
}

if ($searray[6] === $ad_5){
    $fugou_6 = "-) " ;
}elseif ($searray[6] <= $ad_5){
    $fugou_6 = "↑) ";
}else{
    $fugou_6 = "↓) ";
}

if ($searray[7] === $coin_5){
    $fugou_7 = "-) ";
}elseif ($searray[7] <= $coin_5){
    $fugou_7 = "↑) ";
}else{
    $fugou_7 = "↓) ";
}

if ($searray[8] === $holder_0){
    $fugou_8 = " (-)";
}elseif ($searray[8] <= $holder_0){
    $fugou_8 = " (↑)";
}else{
    $fugou_8 = " (↓)";
}


//出力
$tweet= "["."100万ALIS～"."]"."\n".
        $fugou_0.$ad_2." Add (".$add_2.")"."\n".
        $fugou_1.$coin_2 . " ALIS (" .$par_2.")"."\n"."\n".
        "["."10万～100万ALIS"."]"."\n".
        $fugou_2.$ad_3." Add (".$add_3.")"."\n".
        $fugou_3.$coin_3 . " ALIS (" .$par_3.")"."\n"."\n".
        "["."1万～10万ALIS"."]" ."\n".
        $fugou_4.$ad_4." Add (".$add_4.")"."\n".
        $fugou_5.$coin_4 . " ALIS (" .$par_4.")"."\n"."\n".
        "["."～1万ALIS"."]"  ."\n".
        $fugou_6.$ad_5." Add (".$add_5.")"."\n".
        $fugou_7.$coin_5 . " ALIS (" .$par_5.")"."\n"."\n".
        "Total:".$holder_0."Add"."$fugou_8 \n".
        "＃ALIS";
       

// Tweet実行
$twitter = new TwistOAuth($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
$result = $twitter->post("statuses/update", ["status" => "$tweet"]);


// 保存
$array = [$ad_2,$coin_2,$ad_3,$coin_3,$ad_4,$coin_4,$ad_5,$coin_5,$holder_0];
file_put_contents("./distribution/distribution.dat",serialize($array));


$fp = fopen("distribution/distribution2.txt", "w");
    fwrite($fp, "$tweet");
    fclose($fp);


// 確認用
echo $tweet;
