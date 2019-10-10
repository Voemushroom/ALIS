<?php

ini_set("display_errors", 1);
error_reporting(E_ALL);

//価格呼び出し
$fp = fopen("price.txt", "r");
$price = fgets($fp);
fclose($fp);    

//alis.to 最新100件分データー取得
$user_id = $_POST["name"];
$url     = "https://alis.to/api/users/".$user_id."/articles/public?limit=100";
$context = stream_context_create(["http" => ["ignore_errors" => true,"timeout" => 100]]);
$file    = file_get_contents($url, false, $context);

$start         = 0;
$count         = 0;
$totaltip      = 0;
$totaltip_usd  = 0;
$totalalis     = 0;
$totalalis_usd = 0;
$kanma =",";

for($j = 0; $j < 10; $j++){

    for($i = 0; $i < 100; $i++){
        if(strpos($file,"created_at",$start) == 0){
            break;
        }
        $start = strpos($file,"created_at",$start)+13;
        $end   = strpos($file,"topic",$start)-3;
        $timestamp = substr($file, $start, $end-$start); 

        // 2019/01/01 0:00:00 ～ 2019/09/30 23:59:59 確認
        if($timestamp >= 1546268400 && $timestamp <= 1569855599){
            $date = new DateTime("@$timestamp");
            $date->setTimeZone(new DateTimeZone('Asia/Tokyo'));
            $time  = $date->format("m-d");

            // timestampから価格を呼び出し
            $start1 = strpos($price,$time)+6;
            $price1 = substr($price, $start1, 8); 
    
            //投げ銭の有無確認
            $test = substr($file, $end, 50);
            if(preg_match("/tip_value/","$test") !== 0){
                $start = strpos($file,"tip_value",$end)+12;
                $end   = strpos($file,"$kanma",$start);
                $tip   = round(substr($file, $start, $end-$start)/1000000000000000000,2);
                $tip_usd = round($tip * $price1,2);
            }else{
                $tip = 0;
                $tip_usd = 0;
            }
            $start = strpos($file,"article_id",$end)+14;
            $end   = strpos($file,"$kanma",$start)-1;
            $id    = substr($file, $start, $end-$start);

            //article_idからいいねによる獲得alisを取得
            $url2   = "https://alis.to/api/articles/".$id."/alistoken";
            $file2  = file_get_contents($url2, false, $context);
            $start2 = strpos($file2,"token")+8;
            $end2   = strpos($file2,"$kanma",$start2);
            $alis   = round(substr($file2, $start2, $end2-$start2)/1000000000000000000,2);
    
            $alis_usd = round($alis * $price1,2);
            echo $time." いいね獲得token：".$alis."($alis_usd)"." 獲得投げ銭：".$tip."($tip_usd)"."<br>";
    
            $count ++;
            $totaltip      += $tip;
            $totaltip_usd  += $tip_usd;
            $totalalis     += $alis;
            $totalalis_usd += $alis_usd;
        }
    }

    //100記事以上あれば次の100記事読み出し
    if(substr_count($file,"created_at") >= 100){
        
        $start3 = strpos($file,"LastEvaluatedKey");
        $start4 = strpos($file,"article_id",$start3)+14;
        $end4   = strpos($file,"$kanma",$start4)-1;
        $id2    = substr($file, $start4, $end4-$start4);

        $start5 = strpos($file,"sort_key",$start)+11;
        //$end5   = strpos($file,"$kanma",$start5)-1;
        $key    = substr($file, $start5, 16);

        //いまのページの最後のarticle_idとsort_keyを抽出
        $url    = "https://alis.to/api/users/".$user_id."/articles/public?limit=100&article_id=".$id2."&sort_key=".$key;
        $file   = file_get_contents($url, false, $context);
              
        $start  = 0;
        //echo $id2."<br>".$key."<br>";

    }else{
        break;
    }
}

$total1 = $totalalis + $totaltip;
$total2 = $totalalis_usd + $totaltip_usd;
$ave1   = round($total1 / $count,2); 
$ave2   = round($total2 / $count,2); 

echo "<br>".$user_id." さんの履歴"."<br>".
     "集計記事数：".$count." ※上限1000"."<br>".
     "いいね総獲得token：".$totalalis."($totalalis_usd)"."<br>".
     "総獲得投げ銭：".$totaltip."($totaltip_usd)"."<br>".
     "合計獲得token："."$total1"."($total2)"."<br>".
     "平均獲得token："."$ave1"."($ave2)"."<br>".
     "※( )内はtoken取得日のUSD換算"."<br>";
