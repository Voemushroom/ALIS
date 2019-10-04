<?php

ini_set("display_errors", 1);
error_reporting(E_ALL);

require_once("TwistOAuth.phar");

$consumerKey       = "xxxxx";
$consumerSecret    = "xxxxx";
$accessToken       = "xxxxx";
$accessTokenSecret = "xxxxx";

//YoBit Market Info

$url2  = "https://yobit.net/api/2/alis_btc/ticker";
$yo = file_get_contents($url2, false, $context);

$start = mb_strpos($yo,"high")+6;
$end   = mb_strpos($yo,"low")-2;
$high  = number_format(mb_substr($yo, $start, $end-$start),8);

$start = mb_strpos($yo,"low")+5;
$end   = mb_strpos($yo,"avg")-2;
$low   = number_format(mb_substr($yo, $start, $end-$start),8);

$start = mb_strpos($yo,"vol")+5;
$end   = mb_strpos($yo,"vol_cur")-2;
$vol   = mb_substr($yo, $start, $end-$start);

$start = mb_strpos($yo,"last")+6;
$end   = mb_strpos($yo,"buy")-2;
$last  = number_format(mb_substr($yo, $start, $end-$start),8);

$start = mb_strpos($yo,"buy")+5;
$end   = mb_strpos($yo,"sell")-2;
$buy   = number_format(mb_substr($yo, $start, $end-$start),8);

$start = mb_strpos($yo,"sell")+6;
$end   = mb_strpos($yo,"updated")-2;
$sell  = number_format(mb_substr($yo, $start, $end-$start),8);

//orderbookから総ALIS量を取得
$url3  = "https://yobit.net/api/3/depth/alis_btc";
$orderbook3 = file_get_contents($url3, false, $context);

$end2 = 22;
$sell_count  = 0;
$sell_amount = 0;
$buy_count   = 0;
$buy_amount  = 0;
$kanma = ",";
$futa  = "]";

for($y = 0; $y < 500; $y++){

    if(strpos($orderbook3,"[",$end2) == 0){
    break;
    }

    $start1    = strpos($orderbook3,"[",$end2);
    $end1      = strpos($orderbook3,"$kanma",$start1)+1; 
    
    $end2      = strpos($orderbook3,"$futa",$end1); 
    $amount    = substr($orderbook3, $end1, $end2 - $end1);
    
    if(strpos($orderbook3,"bids",$end2) == 0 ){
        $buy_count  ++;
        $buy_amount  += $amount;

        }else{
        $sell_count ++;
        $sell_amount += $amount;
    }
}

//Tweet文作成
$tweet= "["."YoBit Market Info"."]"."\n".
        "LastPrice:".$last."\n".
        "HighPrice:".$high."\n".
        "Low_Price:".$low."\n".
        "Bid_Price:".$buy."\n".
        "Ask_Price:".$sell."\n".
        "Volume:".$vol." BTC"."\n"."\n".
        "Buy_OrderCount:".$buy_count."\n".
        "(".number_format((int)$buy_amount)." ALIS)"."\n"."\n".
        "SellOrderCount:".$sell_count."\n".
        "(".number_format((int)$sell_amount)." ALIS)"."\n";


// Tweet実行
$twitter = new TwistOAuth($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
$result = $twitter->post("statuses/update", ["status" => "$tweet"]);
