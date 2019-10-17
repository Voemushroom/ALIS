<?php

session_start();
$access_token = $_SESSION["token"];


// 価格呼び出し
$fp = fopen("price.txt", "r");
$price = fgets($fp);
fclose($fp);    


// 通知の一覧を取得
$url = "https://alis.to/oauth2api/me/notifications?limit=100";
$headers = array("Authorization: {$access_token}","Content-Type: application/json; charset=utf-8");

$curl = curl_init(); 
curl_setopt($curl, CURLOPT_URL, "$url"); 
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET"); 
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$result = curl_exec($curl);
curl_close($curl);
//echo $result."<br>"."<br>";

// user_id取得
$kanma = ",";
$start = strpos($result,"user_id")+11;
$end   = strpos($result,$kanma,$start)-1;
$id    = substr($result, $start, $end-$start); 


// 有料記事購入費用を取得
$purchase_count     =0;
$total_purchase     =0;
$total_purchase_usd =0;
$end = 0;

for($j = 0; $j < 10; $j++){

    for($i = 0; $i < 100; $i++){
    if(strpos($result,"price",$end) == 0){
        break;
    }    

    $start     = strpos($result,"price",$end)+8;
    $end      = strpos($result,$kanma,$start);
    $purchase  = substr($result, $start, $end-$start)/1000000000000000000;
    
    $start     = strpos($result,"type",$start)+8;
    $end      = strpos($result,"}",$start)-1;
    $type     = substr($result, $start, $end-$start);
    
    $moji   = mb_strlen($result);
    $endd = $moji - $start;
    
    $start1    = strrpos($result,"created_at","-".$endd)+13;   // 後ろから検索
    $timestamp = substr($result, $start1, 10);
    
    if($timestamp <= 1546268400 or $timestamp >= 1569855599){
        continue;
    }
    
    $date = new DateTime("@$timestamp");
    $date->setTimeZone(new DateTimeZone('Asia/Tokyo'));
    $time  = $date->format("m-d");
    
    // timestampから価格を呼び出し
    $start2 = strpos($price,$time)+6;
    $price2 = substr($price, $start2, 8); 
    
    $purchase_usd = round($purchase * $price2,2);
    
    if(strcasecmp("$type", "purchase") == 0){
        $purchase_count     ++;
        $total_purchase     += $purchase;
        $total_purchase_usd += $purchase_usd;
        echo $time. " 購入 ".$purchase ."ALIS($" .$purchase_usd.") $".$price2."<br>";
        
    }else{
        $purchased_count     ++;
        $total_purchased     += $purchase;
        $total_purchased_usd += $purchase_usd;
        echo $time. " 販売 ".$purchase ."ALIS($" .$purchase_usd.") $".$price2."<br>";
    }
        
    }
    //100通知以上あれば次の100通知読み出し
    if(substr_count($result,"created_at") >= 100){
        
        $start3 = strpos($result,"LastEvaluatedKey");
        $start4 = strpos($result,"notification_id",$start3)+19;
        $end4  = strpos($result,"$kanma",$start4)-1;
        $id2      = substr($result, $start4, $end4-$start4);

        $start5 = strpos($result,"sort_key",$start3)+11;
        $key    = substr($result, $start5, 16);

        $url2   = $url."&notification_id=".$id2."&sort_key=".$key;
        
        $curl = curl_init(); 
        curl_setopt($curl, CURLOPT_URL, "$url2"); 
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET"); 
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($curl);
        curl_close($curl);
                  
        //echo $result."<br>"."<br>";
         
        $end = 0;
        
    }else{
        break;
    }
}

$balance = $total_purchased - $total_purchase;
$balance_usd = $total_purchased_usd - $total_purchase_usd;

echo  "----------------------------------------"."<br>".
         "有料記事購入件数：".$purchase_count."<br>".   
         "有料記事総支払額：".$total_purchase." ALIS ($".$total_purchase_usd.")"."<br>".
         "有料記事販売件数：".$purchased_count."<br>".   
         "有料記事総収入額：".$total_purchased." ALIS ($".$total_purchased_usd.")"."<br>".
         "----------------------------------------"."<br>".
         "収支：".$balance." ALIS ($".$balance_usd.") ※()内USD換算"."<br>"."<br>";



// alis.to 最新100件分データー取得
$url     = "https://alis.to/api/users/".$id."/articles/public?limit=100";
$context = stream_context_create(["http" => ["ignore_errors" => true,"timeout" => 10]]);
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
            $id3    = substr($file, $start, $end-$start);

            //article_idからいいねによる獲得alisを取得
            $url2   = "https://alis.to/api/articles/".$id3."/alistoken";
            $file2  = file_get_contents($url2, false, $context);
            $start2 = strpos($file2,"token")+8;
            $end2   = strpos($file2,"$kanma",$start2);
            $alis   = round(substr($file2, $start2, $end2-$start2)/1000000000000000000,2);
    
            $alis_usd = round($alis * $price1,2);
            echo $time." いいね獲得token：".$alis."($".$alis_usd.")"." 獲得投げ銭：".$tip."($".$tip_usd.") $".$price1."<br>";
    
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

        $url    = "https://alis.to/api/users/".$id."/articles/public?limit=100&article_id=".$id2."&sort_key=".$key;
        $file   = file_get_contents($url, false, $context);
              
        $start  = 0;
       
    }else{
        break;
    }
}

$total1 = $totalalis + $totaltip;
$total2 = $totalalis_usd + $totaltip_usd;
$total3 = $totalalis + $totaltip + $balance;
$total4 = $totalalis_usd + $totaltip_usd + $balance_usd;
$ave1   = round($total1 / $count,2); 
$ave2   = round($total2 / $count,2); 

echo  "----------------------------------------"."<br>".
     "集計記事数：".$count." ※上限1000"."<br>".
     "いいね総獲得token：".$totalalis." ALIS($".$totalalis_usd.")"."<br>".
     "総獲得投げ銭：".$totaltip." ALIS($".$totaltip_usd.")"."<br>".
     "合計獲得token："."$total1"." ALIS($".$total2.")"."<br>".
     "平均獲得token："."$ave1"." ALIS($".$ave2.")"."<br>"."<br>";
   



//投げたtokenを集計
$url     = "https://alis.to/api/articles/recent?limit=100";
$file    = file_get_contents($url, false, $context);

//記事の投げ銭有無確認
$start = 0;
$count = 0;
$totaltip_usd2 = 0;

for($j = 2; $j < 100; $j++){

    for($i = 0; $i < 100; $i++){
    if(strpos($file,"tip_value",$start) == 0){
        break;
    }    

    $start   = strpos($file,"tip_value",$start)+12;
    $moji   = mb_strlen($file);
    $endd = $moji - $start;
    
    $start1    = strrpos($file,"created_at",-$endd)+13;   // 後ろから検索
    $timestamp = substr($file, $start1, 10);
    
    if($timestamp <= 1546268400 or $timestamp >= 1569855599){
        continue;
    }
    
    $date = new DateTime("@$timestamp");
    $date->setTimeZone(new DateTimeZone('Asia/Tokyo'));
    $time  = $date->format("m-d");
    
    // timestampから価格を呼び出し
    $start2 = strpos($price,$time)+6;
    $price2 = substr($price, $start2, 8); 
    
    $start1      = strrpos($file,"article_id",-$endd)+14;   // 後ろから検索
    $end1       = strpos($file, $kanma,$start1)-1;
    $article_id = substr($file, $start1, $end1-$start1);
    
    //supportersに自分がいるか確認
    $url2     = "https://alis.to/api/articles/".$article_id."/supporters";
    $file2    = file_get_contents($url2, false, $context);

     if(strpos($file2,$id) == 0){
         continue;
     }    
    
    $start2 = strpos($file2,$id);
    $start3 = strpos($file2,"sum_tip_value",$start2)+16;
    $end3   = strpos($file2,"$kanma",$start3);
    
    if($end3 == 0){
        $end3   = strpos($file2,"}",$start3);
    }
    $tip2 = round(substr($file2, $start3, $end3-$start3)/1000000000000000000,2);
    $tip_usd2 =  round($tip*$price2,2);
    
    $count ++;
    $totaltip2      += $tip2;
    $totaltip_usd2  += $tip_usd2;
    
    echo $time." 投げた銭：".$tip2." ALIS($".$tip_usd2.") $".$price2."<br>";
    
    }
    
    //最後のtimestampが1546268400以上であれば次の100記事読み出し
    $start4  = strrpos($file,"created_at")+13;   // 後ろから検索
    $time2  = substr($file,$start4,10) ;
    if($time2 >= 1546268400){
    
        $url    = "https://alis.to/api/articles/recent?limit=100&page=".$j;
        $file   = file_get_contents($url, false, $context);
              
        $start  = 0;
       
    }else{
        break;
    }
}

$total5 = $total3 - $totaltip2;
$total6 = $total4 - $totaltip_usd2;
echo  "----------------------------------------"."<br>".
     "総投げた銭：".$totaltip2." ALIS($".$totaltip_usd2.")"."<br>".
     "=============================="."<br>".
     "Total収支：".$total5. " ALIS($".$total6.")※()内USD換算"."<br>";

    
?>