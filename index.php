<?php 
include_once ('vendor/autoload.php');

$response = Unirest::get("https://spectator-league-of-legends-v1.p.mashape.com/lol/br/v1/spectator/by-name/Oblidd",
  array(
    "X-Mashape-Key" => "WDdDYHuqYLmshznB011K61QDTA4Ip1MHzOIjsnS4BgktTVXiub"
  )
);

echo '<pre>';
$json = json_decode(json_encode($response->body),true);	

print_r($json['data']['game']['teamTwo']);


?>
