<?php

set_time_limit(120);

if (is_uploaded_file($_FILES["csvfile"]["tmp_name"])) {
  $file_tmp_name = $_FILES["csvfile"]["tmp_name"];
  $file_name = $_FILES["csvfile"]["name"];

  //拡張子を判定
  if (pathinfo($file_name, PATHINFO_EXTENSION) != 'csv') {
    $err_msg = 'CSVファイルのみ対応しています。';
    echo $err_msg;
  } else {
    //ファイルをdataディレクトリに移動
    if (move_uploaded_file($file_tmp_name, "/home/xxxxx/www/wp/uploaded/" . $file_name)) {
      $file = '/home/xxxxx/www/wp/uploaded/'.$file_name;
      $fp   = fopen($file, "r");

      //配列に変換する
      while (($data = fgetcsv($fp, 0, ",")) !== FALSE) {
        $asins[] = $data;
      }
      fclose($fp);
      //ファイルの削除
      unlink('/home/xxxxx/www/wp/uploaded/'.$file_name);
    } else {
      $err_msg = "ファイルをアップロードできません。";
      echo $err_msg;
    }
  }
} else {
  $err_msg = "ファイルが選択されていません。";
  echo $err_msg;
}


$end = 0;
$count  = count($asins);
$space  = " ";
$kanma = ",";

$count_deposit = 0;
$total_deposit = 0;
$yen_deposit = 0;

$count_user = 0;
$total_user = 0;
$yen_user = 0;

$count_like = 0;
$total_like = 0;
$yen_like = 0;

$count_withdraw = 0;
$total_withdraw = 0;
$yen_withdraw = 0;

$count_give = 0;
$total_give = 0;
$yen_give = 0;

$count_burn = 0;
$total_burn = 0;
$yen_burn = 0;

$count_pool = 0;
$total_pool = 0;
$yen_pool = 0;


for($i = 0; $i < $count; $i++){
if(strpos($asins[$i][0],"19/") == 0){
    continue;
    }
    $start  = strpos($asins[$i][0],"2019/");
    $end   = strpos($asins[$i][0],$space,$start);
    $date  = substr($asins[$i][0],$start, $end-$start);
    //echo $date."<br>";
    $time  = date('d-m-Y',  strtotime($date));
    
     if(strcmp($time , "09-09-2019") == 0){
        $time0 = "08-09-2019";
        $url_gecko = "https://api.coingecko.com/api/v3/coins/alis/history?date=".$time0;
    }elseif(strcmp($time , "19-10-2019") == 0){
        $time0 = "18-10-2019";
        $url_gecko = "https://api.coingecko.com/api/v3/coins/alis/history?date=".$time0;
     }elseif(strcmp($time , "21-10-2019") == 0){
        $time0 = "20-10-2019";
        $url_gecko = "https://api.coingecko.com/api/v3/coins/alis/history?date=".$time0;
     }elseif(strcmp($time , "22-10-2019") == 0){
        $time0 = "20-10-2019";
        $url_gecko = "https://api.coingecko.com/api/v3/coins/alis/history?date=".$time0;
    }else{
        $url_gecko = "https://api.coingecko.com/api/v3/coins/alis/history?date=".$time;
    }
     
    // Geckoから価格を呼び出し
    $context = stream_context_create(["http" => ["ignore_errors" => true,"timeout" => 3]]);
    $price  = file_get_contents($url_gecko, false, $context);
    $start2 = strpos($price,"jpy")+5;
    $end2 = strpos($price,$kanma,$start2);
    $price2 = substr($price, $start2, $end2 - $start2); 
    $alis = round($price2 * $asins[$i][3] , 2);
    
    echo $date."[".$asins[$i][2]."]" .$asins[$i][3] ." ALIS(".$alis."円)@".$price2."<br>";
    
    if ( strcmp($asins[$i][2], "deposit") == 0 ) {
        $count_deposit ++;
        $total_deposit += $asins[$i][3];
        $yen_deposit  += $alis;
     }elseif ( strcmp($asins[$i][2], "get from user") == 0 ) {
        $count_user ++;
        $total_user += $asins[$i][3];
        $yen_user  += $alis;
     }elseif ( strcmp($asins[$i][2], "get by like") == 0 ) {
        $count_like ++;
        $total_like += $asins[$i][3];
        $yen_like  += $alis;
    }elseif ( strcmp($asins[$i][2], "withdraw") == 0 ) {
        $count_withdraw ++;
        $total_withdraw += $asins[$i][3];
        $yen_withdraw  += $alis;
    }elseif ( strcmp($asins[$i][2], "give") == 0 ) {
        $count_give ++;
        $total_give += $asins[$i][3];
        $yen_give  += $alis;
     }elseif ( strcmp($asins[$i][2], "burn") == 0 ) {
        $count_burn ++;
        $total_burn += $asins[$i][3];
        $yen_burn  += $alis;
     }else{
        $count_pool ++;
        $total_pool += $asins[$i][3];
        $yen_pool  += $alis;
     }    
}

$income   = $total_deposit + $total_user + $total_like;
$yen_income   = $yen_deposit + $yen_user + $yen_like;
$expense = $total_withdraw +$total_give + $total_burn + $total_pool;
$yen_expense = $yen_withdraw +$yen_give + $yen_burn + $yen_pool;
$balance = $income - $expense;
$yen_balance = $yen_income - $yen_expense;

echo "==================================="."<br>";
echo "Total Deposit ：" .$total_deposit. " ALIS(".$yen_deposit."円)"." [".$count_deposit."件]"."<br>";
echo "Total Get from user ：" .$total_user. " ALIS(".$yen_user."円)"." [".$count_user."件]"."<br>";
echo "Total Get by like ：" .$total_like. " ALIS(".$yen_like."円)"." [".$count_like."件]"."<br>";
echo "Total Withdraw ：" .$total_withdraw. " ALIS(".$yen_withdraw."円)"." [".$count_withdraw."件]"."<br>";
echo "Total Give：" .$total_give. " ALIS(".$yen_give."円)"." [".$count_give."件]"."<br>";
echo "Total Burn ：" .$total_burn. " ALIS(".$yen_burn."円)"." [".$count_burn."件]"."<br>";
echo "Total Pool ：" .$total_pool. " ALIS(".$yen_pool."円)"." [".$count_pool."件]"."<br>";
echo "==================================="."<br>";
echo "収入：".$income." ALIS(".$yen_income."円)"."<br>";
echo "支出：".$expense." ALIS(".$yen_expense."円)"."<br>";
echo "収支：".$balance." ALIS(".$yen_balance."円)"."<br>";

?>
