<?php

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
$kanma = ",";
$kigo  = "\"";
$time0 = strtotime("-24 hour");


// 1000記事分繰り返し処理
for($j = 2; $j < 10; $j++){
    for($i = 0; $i < 100; $i++){
     
    //記事作成時間取得 
    $start = strpos($file,"created_at",$start)+13;
    $timestamp = substr($file, $start, 10);
        
    if($timestamp >= $time0){
        $start1 = strpos($file,"tags",$start)+8; 
    
        //タグ取得
        for($a = 0; $a < 5; $a++){     
            $start1 = strpos($file, $kigo,$start1);
            $start2 = $start1 +1;
            $end1   = strpos($file, $kigo,$start2)+1;
            $tag = substr($file, $start1, $end1-$start1);

            if($tag == "\"status\""){
                break;
            }

            $tags[]  = json_decode($tag);
            $start1 = $end1 +1;
        }
    }

    }
    
    // 次の100件読み出し   
    $url  = "https://alis.to/api/articles/recent?limit=100&page=".$j;
    $file = file_get_contents($url, false, $context);
    
    $start= 0;
       
}

//ソートする
$b = count($tags)-1;
sort($tags, SORT_NATURAL);
$count = 0;

//タグをカウント
for($c = 0; $c < $b; $c++){
    $count++;
    if($tags[$c] == $tags[$c+1]){
        continue;
    }
    
    $tagg[] = [$count,$tags[$c]];
    $count  = 0;
 
}

//多い順に並べ替え
rsort($tagg);

$link = "https://alis.to/tag/".urlencode($tagg[0][1]);

//出力
$tweet = "<直近24時間 ALIS人気タグTOP3>"."\n".
     $tagg[0][1]."：".$tagg[0][0]."件"."\n".
     $tagg[1][1]."：".$tagg[1][0]."件"."\n".
     $tagg[2][1]."：".$tagg[2][0]."件"."\n".
     $link;

$twitter = new TwistOAuth($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
$result = $twitter->post("statuses/update", ["status" => $tweet]);

//確認用
echo $tweet;


