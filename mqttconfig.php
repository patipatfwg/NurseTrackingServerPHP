<?php
date_default_timezone_set("Asia/Bangkok");
require ('mqtt/phpMQTT.php');
require ('env.php');
require ('Model.php');

require ('dashboardFunction.php');
require ('serverFunction.php');



$mqtt = new phpMQTT($server, $port, $client_id);
if( !$mqtt->connect(true, NULL, $username, $password) ) {
    exit(1);
}

$topics[$topicClientSub] = array("qos" => 0, "function" => "subscribeMessage");
$mqtt->subscribe($topics, 0);

while($mqtt->proc())
{

    

}

$mqtt->close();


function PublishMessage($json,$who)
{  
    require ('env.php');

    $mqtt = new phpMQTT($server, $port, $client_id);
  
    if ($mqtt->connect(true, NULL, $username, $password)) 
    {
        $json = json_encode($json);
        $mqtt->publish("Phyathai/Ward1/$who", $json, 0);
        $mqtt->close();
    } 
    else
    {
        echo "Time out!\n";
    }
}
