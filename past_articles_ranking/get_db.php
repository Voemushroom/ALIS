<?php

set_time_limit(300);

$context = stream_context_create(["http" => ["ignore_errors" => true,"timeout" => 100]]);

//いろいろデーターをセット
$start = 0;
$kanma = ",";
$kigo  = "\"";
$number = 0;
$count  = 1;
$gets   = 0;
$file   = 0;

//対象のURL
for($j = 1; $j < 500; $j++){
    $urls[] = "https://alis.to/api/articles/recent?limit=100&page=".$j;
}  
  
//マルチハンドル初期化
$mh = curl_multi_init();

//後で使うため個別ハンドル保管用の配列を準備
$ch_array = array();

//URLのセット
foreach($urls as $url) {
	$ch = curl_init();
	$ch_array[] = $ch;
	curl_setopt_array($ch, [
		CURLOPT_URL            => $url,
		CURLOPT_HEADER         => false,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_TIMEOUT        => 30,
	]);
	curl_multi_add_handle($mh, $ch);
}

do {
    curl_multi_exec($mh, $running);
    curl_multi_select($mh);
} while ($running > 0);


//HTML取得
foreach ($ch_array as $ch) {
	$file = curl_multi_getcontent($ch);
        usleep(50000);
        
	//ここから$fileを好きにする
        if(strpos($file,"article_id",$start) == false){
            curl_close($ch);
            $gets = 1;

            do {
            $file = file_get_contents("https://alis.to/api/articles/recent?limit=100&page=".$count,false, $context);
            } while (strpos($http_response_header[0], "500") !== false);
            
            echo "file_getしました(".$http_response_header[0].")"."<br>";
            usleep(50000);
                
        }

        // 100記事分繰り返し処理
        for($i = 0; $i < 100; $i++){
        if(strpos($file,"article_id",$start) == false){
            break;
        }

        $start = strpos($file,"article_id",$start)+14;
        $end   = strpos($file,$kanma,$start)-1;
        $article_id = substr($file, $start, $end - $start);
        //echo $article_id.$kanma;

        //ユーザーIDを取得
        if(strpos($file,"user_id",$end) == false){
            break;
        } 
        $start = strpos($file,"user_id",$end)+11;
        $end   = strpos($file,$kanma,$start)-1;
        $user_id = substr($file, $start, $end - $start);
        //echo $user_id.$kanma;

        //記事作成時間取得 
        if(strpos($file,"created_at",$end) == false){
            break;
        } 
        $start = strpos($file,"created_at",$end)+13;
        $timestamp = substr($file, $start, 10);
        $date = new DateTime("@$timestamp");
        $date->setTimeZone(new DateTimeZone('Asia/Tokyo'));
        $time  = $date->format("Ymd");
        //echo $time.$kanma;

        //トピックを取得 
        if(strpos($file,"topic",$end) == false){
            break;
        } 
        $start = strpos($file,"topic",$start)+9;
        $end   = strpos($file,$kanma,$start)-1;
        $topic = substr($file, $start, $end - $start);
        //echo $topic.$kanma;

        //タイトルを取得
        if(strpos($file,"title",$end) == false){
            break;
        } 
        $start = strpos($file,"title",$end)+8;
        $end   = strpos($file,$kanma,$start);
        $title = json_decode(substr($file, $start, $end - $start));
        //echo $title.$kanma;

        //状態を取得 
        $start  = strpos($file,"status",$end)+10;
        $end    = strpos($file,"}",$start)-1;
        $status = substr($file, $start, $end - $start);
        //echo $status."<br>";

        if(strcmp($status,"public") == 0){ 
            $output[] = "article_id:"."$article_id,$user_id,$time,$topic,$title"."\n";
            
        }
   
        }

        $start= 0;
        $count ++;
        
        if($gets !== 1){
	    curl_multi_remove_handle($mh, $ch);
	    curl_close($ch);
        }
	
        $gets = 0;
        $file = 0;
        
}

//終了処理
curl_multi_close($mh);

file_put_contents("/home/bluebadger8/www/wp/ranking.dat",serialize($output));
