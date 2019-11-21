<?php

set_time_limit(300);
ini_set("display_errors", 1);
error_reporting(E_ALL);

$date1    = $_POST["date1"];
$date2    = $_POST["date2"];
$categori = $_POST["categori"];

if($date1 == null){
    exit("開始日が選択されていません");
}

if($date2 == null){
    exit("終了日が選択されていません");
}

$date_start = date('Ymd',  strtotime($date1));
$date_end   = date('Ymd',  strtotime($date2));


$context = stream_context_create(["http" => ["ignore_errors" => true,"timeout" => 100]]);
$file    = file_get_contents("ranking.dat");


//いろいろデーターをセット
$end    = 0;
$kanma  = ",";
$kigo   = "\"";
$number = 0;

$sum   = substr($file, 2, 5);
//echo $sum."<br>";

for($j = 0; $j < $sum; $j++){   
     
    $start = strpos($file,"article_id",$end)+11;
    $end   = strpos($file,$kanma,$start);
    $id    = substr($file, $start, $end - $start);
    
    $start1 = $end+1;
    $end    = strpos($file,$kanma,$start1);
    $user   = substr($file, $start1, $end - $start1);
    
    $start2 = $end+1;
    $end    = strpos($file,$kanma,$start2);
    $time0  = substr($file, $start2, $end - $start2);
    $time   = date('Ymd', strtotime($time0));

    $start3 = $end+1;
    $end    = strpos($file,$kanma,$start3);
    $topic   = substr($file, $start3, $end - $start3);
    
    $start4 = $end+1;
    $end    = strpos($file,$kigo,$start4);
    $title  = substr($file, $start4, $end - $start4);

    if(strcmp($categori,"nothing") !== 0 && strcmp($categori,$topic) !== 0){
        continue;
    }

    if($time >= $date_start && $time <= $date_end){
        do {
        $like = file_get_contents("https://alis.to/api/articles/".$id."/likes",false, $context);
        } while (strpos($http_response_header[0], "500") !== false);
    

        //likesを取得
        $end1  = strpos($like,"}");
        $likes = substr($like,10, $end1 - 10);
        //echo "$id,$user,$time,$topic,$title,$likes"."<br>";

        $arr[] = [$likes,$id,$user,$time,$topic,$title];

    }  
    
}


rsort($arr);
$count = count($arr);

?>


<html>
<head>
<meta charset='utf-8'>
<title>ランキング結果🍄</title>
</head>
<body>
<h2><?php echo $date_start." ～ ".$date_end." のいいねランキング結果🍄"; ?></h2>
<h3>対象記事件数(<?= $count; ?>)</h3>

<?php
for($q = 0; $q < 10; $q++){
    
    if($q > $count-1){
        break;
    }

    $w = $q+1;
    echo "<h4>"."$w"."位：".$arr[$q][0]."いいね".
    '<a href="https://alis.to/users/'.$arr[$q][2].' target="_blank">'.$arr[$q][2]."さん</a><br>".
    '<a href="https://alis.to/'.$arr[$q][2]."/articles/".$arr[$q][1].' target="_blank">'.$arr[$q][5]."</a><br>".
    "(".$arr[$q][4].")</h4>";
}
?>

</body>
</html>





