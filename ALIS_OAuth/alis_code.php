<?php
session_start();

//code受け取り
$codes = json_encode($_GET);

//code抽出
$end  = strpos($codes,"}")-1;
$code = substr($codes, 9, $end-9);

$client_secret = "xxxxx";
$token = base64_encode($client_id.":".$client_secret);
$url2 = "https://alis.to/oauth2/token";

//code_verifierを受け取り
$code_verifier = $_SESSION["code_verifier"];

$params = http_build_query(array("grant_type" => "authorization_code",
"code" => $code , "redirect_uri" => "http://voemushroom.com/alis_code.php",
"code_verifier" => $code_verifier)); 

$headers = array("Authorization: Basic {$token}","Content-Type: application/x-www-form-urlencoded"); 

//アクセスtoken取得
$curl = curl_init(); 
curl_setopt($curl, CURLOPT_URL, "$url2"); 
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST"); 
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers); 
curl_setopt($curl, CURLOPT_POSTFIELDS, $params); 
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

$result = curl_exec($curl);
curl_close($curl);

//echo $result."<br>"."<br>";


//アクセスtoken抽出
$end = strpos($result,"refresh_token")-4;
$access_token = substr($result,18, $end-18);
//setcookie("token", $access_token , time()+60*3);
$_SESSION["token"] = $access_token;

$url3 = "http://voemushroom.com/alis-tool/";
header("Location: ".$url3);
exit();


?>

