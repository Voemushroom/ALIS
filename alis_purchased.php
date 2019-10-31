<?php

//別で取得したaccess_tokenとarticle_idをセット
session_start();
$access_token = $_SESSION["token"];
$article_id = $_POST["name"];

// 通知一覧を取得
$url = "https://alis.to/oauth2api/me/notifications?limit=100";
$headers = array("Authorization: {$access_token}","Content-Type: application/json; charset=utf-8");

$curl = curl_init(); 
curl_setopt($curl, CURLOPT_URL, "$url"); 
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");   
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$result = curl_exec($curl);
curl_close($curl);


//user_id取得
$kanma = ",";
$start = strpos($result,"user_id")+11;
$end   = strpos($result,$kanma,$start)-1;
$id = substr($result, $start, $end-$start); 


// 有料記事購入者を取得
$count     =0;
$total_purchased     =0;
$start = 0;
$context = stream_context_create(["http" => ["ignore_errors" => true,"timeout" => 3]]);

for($j = 0; $j < 100; $j++){
    for($i = 0; $i < 100; $i++){
    if(strpos($result,"purchased",$start) == 0){
        break;
    }
    
    $start   = strpos($result,"purchased",$start);
    $moji   = mb_strlen($result);
    $endd = $moji - $start;
        
    $start1 = strrpos($result,"acted_user_id", -$endd)+17;   // 後ろから検索
    $end1  = strpos($result,$kanma, $start1)-1;
    $user_id  = substr($result, $start1, $end1 - $start1);
    
    $start1 = strpos($result,"article_id", $end1)+14;  
    $end1  = strpos($result,$kanma, $start1)-1;
    $id2  = substr($result, $start1, $end1 - $start1);
    
    //入力された記事なら実行
    if ( strcmp($article_id, $id2) == 0 ) {
        $url3 = "https://alis.to/api/users/".$user_id."/info";
        $file    = file_get_contents($url3, false, $context);
        
        //アイコンのurlを取得
        if(strpos($file,"icon_image_url") !== false){
            $start1 = strpos($file,"icon_image_url")+18;  
            $end1  = strpos($file,"}", $start1)-1;
            $icon   = substr($file, $start1, $end1 - $start1);      
        }else{
            $icon = "https://alis.to/d/nuxt/dist/img/f7c46bd.png";
        }
        
        $url4   = "https://alis.to/users/".$user_id;
        $output[] = [$url4,$icon];
        
    }
    $start += 10;
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
                                
        $start = 0;
       
    }else{
        break;
    }
}

//出力
$a = count($output);
echo "<h2>購入者合計：".$a."人"."</h2><br>";

for($b = 0; $b < $a; $b++){
    $url = $output[$b][1];
    $img = file_get_contents($url);
    $enc_img = base64_encode($img);
    $imginfo = getimagesize('data:application/octet-stream;base64,' . $enc_img);

    echo '<img src="data:' . $imginfo['mime'] . ';base64,'.$enc_img.' ">'."<br>";
    echo '<a href='.$output[$b][0].'>'.$output[$b][0].'</a>'."<br><br>";
}

?>
