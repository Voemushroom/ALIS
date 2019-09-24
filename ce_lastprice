<?php
    $url = "https://www.coinexchange.io/api/v1/getmarketsummary?market_id=538";
    $alis = file_get_contents($url);
    $start = mb_strpos($alis,'LastPrice":"')+12;
    $end = mb_strpos($alis,'","Change"');
    $LastPrice = mb_substr($alis, $start, $end-$start);
    echo "$LastPrice";
