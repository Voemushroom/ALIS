<?php

ini_set("display_errors", 1);
error_reporting(E_ALL);

//twitter認証
require_once("TwistOAuth.phar");

$consumerKey       = "xxxxx";
$consumerSecret    = "xxxxx";
$accessToken       = "xxxxx";
$accessTokenSecret = "xxxxx";

//新着記事100件読み出し
$url     = "https://alis.to/api/articles/recent?limit=100";
$context = stream_context_create(["http" => ["ignore_errors" => true,"timeout" => 3]]);
$file    = file_get_contents($url, false, $context);


//いろいろデーターをセット
$start = 0;
$count = 0;
$crypto = 0;
$gourmet = 0;
$game = 0;
$business = 0;
$travel = 0;
$beauty = 0;
$education = 0;
$comic = 0;
$technology = 0;
$other = 0;
$count0 = 0;
$crypto0 = 0;
$gourmet0 = 0;
$game0 = 0;
$business0 = 0;
$travel0 = 0;
$beauty0 = 0;
$education0 = 0;
$comic0 = 0;
$technology0 = 0;
$other0 = 0;
$kanma = ",";


// 1000記事分繰り返し処理
for($j = 2; $j < 10; $j++){
    for($i = 0; $i < 100; $i++){
     
    //記事作成時間取得 
    $start = strpos($file,"created_at",$start)+13;
    //echo $start."<br>";
    if($start == 0){
        break;
    }

    $timestamp = substr($file, $start, 10);
    //echo $timestamp."<br>";
    $date = new DateTime("@$timestamp");
    $date->setTimeZone(new DateTimeZone('Asia/Tokyo'));
    $time  = $date->format("m-d");
    $yesterday = date("m-d",strtotime("yesterday"));
    $before_yesterday = date("m-d",strtotime("-2 day"));
    
    //昨日ならカウント
    if(strcmp($time , $yesterday) == 0){   
    
        //topicを取得
        $start1 = strpos($file,"topic",$start)+9; 
        $end1   = strpos($file, $kanma,$start1)-1;
        $topic  = substr($file, $start1, $end1-$start1);
        //echo $topic."<br>";

        //topicにより分岐 
        if(strcmp($topic , "crypto") == 0){
            $crypto ++;
        }elseif(strcmp($topic , "gourmet") == 0){
            $gourmet ++;
        }elseif(strcmp($topic , "game") == 0){
            $game ++;
        }elseif(strcmp($topic , "business") == 0){
            $business ++;
        }elseif(strcmp($topic , "travel") == 0){
            $travel ++;
        }elseif(strcmp($topic , "beauty") == 0){
            $beauty ++;
        }elseif(strcmp($topic , "education-parenting") == 0){
            $education ++;
        }elseif(strcmp($topic , "comic-animation") == 0){
            $comic ++;
        }elseif(strcmp($topic , "technology") == 0){
            $technology ++;
        }else{
            $other ++;
        }

        $count ++;
    
    //一昨日なら
    }elseif(strcmp($time , $before_yesterday) == 0){
        
        //topicを取得
        $start1 = strpos($file,"topic",$start)+9; 
        $end1   = strpos($file, $kanma,$start1)-1;
        $topic  = substr($file, $start1, $end1-$start1);
        //echo $topic."<br>";

        //topicにより分岐 
        if(strcmp($topic , "crypto") == 0){
            $crypto0 ++;
        }elseif(strcmp($topic , "gourmet") == 0){
            $gourmet0 ++;
        }elseif(strcmp($topic , "game") == 0){
            $game ++;
        }elseif(strcmp($topic , "business") == 0){
            $business0 ++;
        }elseif(strcmp($topic , "travel") == 0){
            $travel0 ++;
        }elseif(strcmp($topic , "beauty") == 0){
            $beauty0 ++;
        }elseif(strcmp($topic , "education-parenting") == 0){
            $education0 ++;
        }elseif(strcmp($topic , "comic-animation") == 0){
            $comic0 ++;
        }elseif(strcmp($topic , "technology") == 0){
            $technology0 ++;
        }else{
            $other0 ++;
        }

        $count0 ++;
    
    }

    }
    
    //次の100件読み出し
    $url    = "https://alis.to/api/articles/recent?limit=100&page=".$j;
    $file   = file_get_contents($url, false, $context);
    
    $start  = 0;
       
}

//符号(+)をつけるfunction
function formatNum($num){
    return sprintf("%+d",$num);
}

//一昨日と昨日を比較
$count1      = formatNum($count - $count0);
$crypto1     = formatNum($crypto -$crypto0);
$gourmet1    = formatNum($gourmet -$gourmet0);
$game1       = formatNum($game - $game0); 
$business1   = formatNum($business -$business0);
$travel1     = formatNum($travel - $travel0);
$beauty1     = formatNum($beauty - $beauty0);
$education1  = formatNum($education - $education0);
$comic1      = formatNum($comic - $comic0);
$technology1 = formatNum($technology - $technology0);
$other1      = formatNum($other - $other0);

//tweet文を作成
$tweet = $yesterday."の投稿数：".$count."件"."($count1)"."\n"."\n".
         "[カテゴリー内訳]"."\n".
         "クリプト：".$crypto."件($crypto1)"."\n".
         "グルメ：".$gourmet."件($gourmet1)"."\n".
         "ゲーム：".$game."件($game1)"."\n".
         "ビジネス：".$business."件($business1)"."\n".
         "トラベル：".$travel."件($travel1)"."\n".
         "ビューテ：".$beauty."件($beauty1)"."\n".
         "教育子育：".$education."件($education1)"."\n".
         "コミ漫画：".$comic."件($comic1)"."\n".
         "テクノ：".$technology."件($technology1)"."\n".
         "その他：".$other."件($other1)"."\n".
         "※( )内は前日比"."\n"."\n".
         "＃ALIS";

//tweet実行
$twitter = new TwistOAuth($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
$result = $twitter->post("statuses/update", ["status" => $tweet]);
echo $tweet;

?>





