<?php
ini_set("display_errors", 1);
error_reporting(E_ALL);

require_once("TwistOAuth.phar");

$consumerKey       = "xxxxx";
$consumerSecret    = "xxxxx";
$accessToken       = "xxxxx";
$accessTokenSecret = "xxxxx";

$contract = "0xea610b1153477720748dc13ed378003941d84fab";
$ce_ad    = "0x4b01721f0244e7c5b5f63c20942850e447f5a5ee";
$alis_ad  = "0xed93fb4b30b31dd614d13e92b89aba1c6d12c9d4";
$apikey   = "xxxxx";
$title    = "?df44ALIS TXNS ALERT?df44"."\n"."\n";
    
$fp = fopen("blockNumber.txt", "r");
$block = fgets($fp);
fclose($fp);
    
$url = "https://api.etherscan.io/api?module=account&action=tokentx&contractaddress=".$contract."&address=".$ce_ad."&startblock=".$block."&endblock=9999999&sort=asc&apikey=".$apikey;      
    
$ce      = file_get_contents($url);
$start1  = mb_strpos($ce,'status":"')+9;
$end1    = mb_strpos($ce,'","message"');
$status1 = mb_substr($ce, $start1, $end1-$start1);

if ($status1 == 1){

    $start = mb_strpos($ce,'timeStamp":"')+12;
    $end   = mb_strpos($ce,'","hash"');
    $timestamp = mb_substr($ce, $start, $end-$start); 
    $date = new DateTime("@$timestamp");
    $date->setTimeZone(new DateTimeZone('Asia/Tokyo'));
    $time = "[".$date->format("m-d H:i")  ."]"."\n";

    $start5 = mb_strpos($ce,'value":"')+8;
    $end5 = mb_strpos($ce,'","tokenName"');
    $value = mb_substr($ce, $start5, $end5-$start5);
    $val = "value：".$value / 1000000000000000000 . "  ALIS"."\n";

    $start2 = mb_strpos($ce,'blockNumber":"')+14;
    $end2 = mb_strpos($ce,'","timeStamp"');
    $Number = mb_substr($ce, $start2, $end2-$start2);
    $blockNumber = $Number +1 ;
    $fp1 = fopen("blockNumber.txt", "w");
    fwrite($fp1, "$blockNumber");
    fclose($fp1);

    $start3 = mb_strpos($ce,'from":"')+7;
    $end3 = mb_strpos($ce,'","contractAddress"');
    $from = mb_substr($ce, $start3, $end3-$start3);
    if (strcasecmp("$from", "$ce_ad") == 0 ){
        $from1 = "from：CoinExchange.io"."\n";
	  }elseif(strcasecmp("$from", "$alis_ad") == 0 ){
	      $from1 = "from：ALIS運営"."\n";
    }else {
    $from1 = "from：unknown address"."\n";
	  }
	
	  $start4 = mb_strpos($ce,'","to":"0')+8;
    $end4 = mb_strpos($ce,'","value"');
    $to = mb_substr($ce, $start4, $end4-$start4);
    if (strcasecmp("$to", "$ce_ad") == 0 ){
        $to1 = "to：CoinExchange.io"."\n"."\n";
	  }elseif(strcasecmp("$to", "$alis_ad") == 0 ){
		    $to1 = "to：ALIS運営"."\n"."\n";
    }else {
    $to1 = "to：unknown address"."\n"."\n";
    }
	
	  $twitter = new TwistOAuth($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
    $result = $twitter->post("statuses/update", ["status" => "$title"."$time"."$val"."$from1"."$to1"."＃ALIS"]);
}

$fp2 = fopen("blockNumber_ALIS.txt", "r");
$block2 = fgets($fp2);
fclose($fp2);

$url2 = "https://api.etherscan.io/api?module=account&action=tokentx&contractaddress=".$contract."&address=".$alis_ad."&startblock=".$block2."&endblock=9999999&sort=asc&apikey=".$apikey;

$alis    = file_get_contents($url2);
$start6  = mb_strpos($alis,'status":"')+9;
$end6    = mb_strpos($alis,'","message"');
$status2 = mb_substr($alis, $start6, $end6-$start6);

if ($status2 == 1){

    $start7 = mb_strpos($alis,'timeStamp":"')+12;
    $end7   = mb_strpos($alis,'","hash"');
    $timestamp2 = mb_substr($alis, $start7, $end7-$start7); 
    $date2 = new DateTime("@$timestamp");
    $date2->setTimeZone(new DateTimeZone('Asia/Tokyo'));
    $time2 = "[".$date2->format("m-d H:i")  ."]"."\n";

    $start8 = mb_strpos($alis,'value":"')+8;
    $end8 = mb_strpos($alis,'","tokenName"');
    $value2 = mb_substr($alis, $start8, $end8-$start8);
    $val2 = "value：".$value2 / 1000000000000000000 . "  ALIS"."\n";

    $start9 = mb_strpos($alis,'blockNumber":"')+14;
    $end9 = mb_strpos($alis,'","timeStamp"');
    $Number2 = mb_substr($alis, $start9, $end9-$start9);
    $blockNumber2 = $Number2 +1 ;
    $fp3 = fopen("blockNumber_ALIS.txt", "w");
    fwrite($fp3, "$blockNumber2");
    fclose($fp3);

    $start10 = mb_strpos($alis,'from":"')+7;
    $end10 = mb_strpos($alis,'","contractAddress"');
    $from2 = mb_substr($alis, $start10, $end10-$start10);
    if (strcasecmp("$from", "$ce_ad") == 0 ){
        $from3 = "from：CoinExchange.io"."\n";
	  }elseif(strcasecmp("$from", "$alis_ad") == 0 ){
	      $from3 = "from：ALIS運営"."\n";
    }else {
    $from3 = "from：unknown address"."\n";
	  }
	
	  $start11 = mb_strpos($alis,'","to":"0')+8;
    $end11 = mb_strpos($alis,'","value"');
    $to2 = mb_substr($alis, $start11, $end11-$start11);
    if (strcasecmp("$to", "$ce_ad") == 0 ){
        $to3 = "to：CoinExchange.io"."\n"."\n";
	  }elseif(strcasecmp("$to", "$alis_ad") == 0 ){
		    $to3 = "to：ALIS運営"."\n"."\n";
    }else {
    $to3 = "to：unknown address"."\n"."\n";
    }
	
	  $twitter2 = new TwistOAuth($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
    $result2 = $twitter2->post("statuses/update", ["status" => "$title"."$time2"."$val2"."$from3"."$to3"."＃ALIS"]);
}
