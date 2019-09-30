<?php

ini_set("display_errors", 1);
error_reporting(E_ALL);

require_once("TwistOAuth.phar");

$consumerKey       = "xxxxx";
$consumerSecret    = "xxxxx";
$accessToken       = "xxxxx";
$accessTokenSecret = "xxxxx";

$url  = "https://www.coinexchange.io/api/v1/getorderbook?market_id=538";

$context = stream_context_create(["http" => ["ignore_errors" => true,"timeout" => 100]]);

$orderbook = file_get_contents($url, false, $context);

date_default_timezone_set('Asia/Tokyo');
$date = [date("Y-m-d H:i",strtotime("-2 minute")),date("Y-m-d H:i",strtotime("-1 minute")),date("Y-m-d H:i")];

for($d = 0 ; $d < 3; $d++){

    $start = 0;
    for($i = 0; $i <= 10; $i++){

    if(strpos($orderbook,$date[$d],$start) === false){
        break;
    }

    $start0    = strpos($orderbook,$date[$d],$start)+33;
    $end0      = strpos($orderbook,"}",$start0)-1; 
    $quantity = substr($orderbook, $start0, $end0 - $start0);
    
    if((int)$quantity >= 10000){
        $start1 = strpos($orderbook,"Price",$start0-50)+8;
        $end1   = strpos($orderbook ,"OrderTime",$start1)-3;
        $price  = substr($orderbook , $start1 , $end1 - $start1);

        $start2 = strpos($orderbook,"Type", $start0-70)+7;
        $end2   = strpos($orderbook,"Price",$start2)-3; 
        $type   = substr($orderbook, $start2, $end2 - $start2);
        
        //tweet作成
        if(strcasecmp("$type", "buy") == 0 ){
            $type_tweet = "["."買い板"."]"." ALIS/BTC";
        }else{
            $type_tweet = "["."売り板"."]"." ALIS/BTC";
        }
        $tweet= $type_tweet."\n".
                "Price:".$price."\n".
                "Volume:".number_format((int)$quantity)." ALIS"."\n".
                "[".$date[$d]."]";

        // Tweet実行
        $twitter = new TwistOAuth($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
        $result = $twitter->post("statuses/update", ["status" => "$tweet"]);
        }   
    $start = $start0 +10;
    }
}
