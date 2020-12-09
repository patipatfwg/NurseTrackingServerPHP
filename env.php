<?php

$serverHome = '192.168.1.49';
$serverFWG  = '10.32.11.18';
$serverHTTP  = 'test.mosquitto.org';
$server = $serverHTTP;
  
$port = 1883;
$client_id = 'phyathai';
$topic = "Phyathai/Ward1";
$topicClientSub = $topic."/Client";
$topicServerPub = $topic."/Server";
$topicDashboardPub = $topic."/Dashboard";                    
$username = '';                   
$password = '';

$ENV_TIME_CLEAR_LIST = 10;
$ENV_DISTANCE_CLEAR_LIST = -999;
$ENV_ANROIDBOX_CLEAR_LIST = "";
$ENV_UPDATED_AT_CLEAR_LIST = 0;

