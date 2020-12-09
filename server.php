<?php
session_start();
date_default_timezone_set("Asia/Bangkok");
require ('mqttconfig.php');

function subscribeMessage($topic, $msg)
{
    $DateTimeModel = date("Y-m-d h:i:s", time());
    echo "Recieved at: " . $DateTimeModel . "\n";
    echo "Topic: {$topic}\n";
    echo "Client Message: \n";
    print_r($msg);
    echo "\n\n";

    $json = json_decode($msg);
    $device_id = $json->androidbox->device_id;
    $is_Androidbox = CheckAndroidbox($device_id);
    if($is_Androidbox==1)
    {
        $checkVersionTAG = 0;
        $version = $json->itag->version;
        $checkVersionTAG = CheckVersionTAG($version);
        if($checkVersionTAG[0]==1)
        {
            $tag_list = $json->itag->itag_list;
            for($NumA=0;count($tag_list)>$NumA;$NumA++)
            {
                $jsonclient = [
                    $tag_list[$NumA]->mac_address,
                    $tag_list[$NumA]->distance,
                    $device_id,
                    strtotime( date("Y-m-d h:i:s", time()) ),
                    $json->androidbox->datetime
                    
                ];
                FilterMacAddressClient($jsonclient);
            }
            SendDashboard();
        }
        else
        {
          SendServer($device_id,$checkVersionTAG[0],$checkVersionTAG[1]);
        }
    }
}

function ClearList()
{
    include("env.php");

    // echo "\n\n ClearList start \n\n";

    $url = "TAGListStatus.json";
    $data = trim(file_get_contents($url));
    $jsonFile = json_decode($data);

    for($NumA=0;count($jsonFile)>$NumA;$NumA++)
    {
        $DateTimeEpoch = strtotime( date("Y-m-d h:i:s", time()) );
        $TimeStamp = $jsonFile[$NumA]->updated_at;
        $timeout = $DateTimeEpoch-$TimeStamp;

        echo $DateTimeEpoch." - ".$TimeStamp." = ".$timeout."\n";

        if($timeout>=$ENV_TIME_CLEAR_LIST)
        {
            $mac_address = $jsonFile[$NumA]->mac_address;

            echo "Found $mac_address \n";

            $json = [
                $jsonFile[$NumA]->mac_address,
                $jsonFile[$NumA]->distance,
                $jsonFile[$NumA]->androidbox,
                $jsonFile[$NumA]->updated_at
            ];

            WriteTAGListStatus($json,"clearlist");





        }
    }

}

function SendServer($deviceId, $version, $itag_listModel)
{
  $itagModel = ["version"=>$version,"itag_list"=>$itag_listModel];
  $anrdoidboxModel = AndroidModel($deviceId,"Please update version.");
  $json = ["androidbox"=>$anrdoidboxModel,"itag"=>$itagModel];
  PublishMessage($json,"Server");

  //Log
  echo "Server Message: ";
  echo json_encode($json)."\n\n";
  //
}