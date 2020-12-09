<?php
session_start();
date_default_timezone_set("Asia/Bangkok");
require ('mqtt/phpMQTT.php');

  $serverHome = '192.168.1.49';
  $serverFWG  = '10.32.11.18';
  $serverLocal  = '192.168.43.234';
  $serverHTTP = 'mqtt.eclipse.org';
  $server = $serverFWG;
  
  $port     = 1883;
  $client_id = 'phyathai';
  $topic = "Phyathai/Ward1/Client";                    
  $username = '';                   
  $password = '';                   

  $mqtt = new phpMQTT($server, $port, $client_id);
  if( !$mqtt->connect(true, NULL, $username, $password) ) {
  exit(1);
  }


  $topics[$topic] = array("qos" => 0, "function" => "procmsg");
  $mqtt->subscribe($topics, 0);

  while($mqtt->proc()){
  
  }

  $mqtt->close();  


function procmsgDashboard($topic, $msg)
{
  echo $topic;
}



function procmsg($topic, $msg)
{
  $DateTimeModel = date("Y-m-d h:i:s", time());
  echo "Recieved at: " . $DateTimeModel . "\n";
  echo "Topic: {$topic}\n";
  // echo "Client Message: $msg\n\n";

  $json = json_decode($msg);
  $deviceId = $json->androidbox->device_id;
  $checkandroidbox = CheckAndroidbox($deviceId);
  if($checkandroidbox==1 && $deviceId!="")
  {
    $checkVersionTAG = 0;
    $version = $json->itag->version;
    $checkVersionTAG = checkVersionTAG($version);
    if($checkVersionTAG[0]==1)
    {
      WriteAndroidbox($json);
      SendDashboard();
    }
    else
    {
      SendServer($deviceId,$checkVersionTAG[0],$checkVersionTAG[1]);
    }  
  }
}

function AndroidModel($device_id,$Message) 
{
  $DateTimeModel = date("Y-m-d h:i:s", time());
  $anrdoidboxModel = ["datetime"=>$DateTimeModel, "device_id"=>$device_id, "message"=>$Message];
  return $anrdoidboxModel;
}


function WriteAndroidbox($json)
{
  $deviceId = $json->androidbox->device_id;
  $filename = "androidbox/".$deviceId.".json";
  $file_encode = json_encode($json,true);
  file_put_contents($filename, $file_encode );
  chmod($filename,0777);
}

function WriteDashboard($json)
{
  $filename = "JSONdashboard.json";
  $file_encode = json_encode($json,true);
  file_put_contents($filename, $file_encode );
  chmod($filename,0777);
}


function checkAndroidbox($a)
{
  $url = "setting/androidbox.json";
  $data = trim(file_get_contents($url));
  $deviceArray = json_decode($data)->device;
  $result = 0;
  for($NumA=0; count($deviceArray)>$NumA; $NumA++)
  {
    $deviceIDSetting = $deviceArray[$NumA]->device_id;
    if($deviceIDSetting==$a)
    {
      $result = 1;
    }
  }
  return $result;
}

function checkVersionTAG($a)
{
  $url = "setting/itag_list.json";
  $data = trim(file_get_contents($url));
  $json = json_decode($data);
  $version = $json->version;
  if($version==$a)
  {
    $result = [1];
  }else{
    $result = [$version, $json->itag_list];
  }
  return $result;
}

function SendServer($deviceId, $version, $itag_listModel)
{
  $itagModel = ["version"=>$version,"itag_list"=>$itag_listModel];
  $anrdoidboxModel = AndroidModel($deviceId,"Please update version.");
  $json = ["androidbox"=>$anrdoidboxModel,"itag"=>$itagModel];
  pub($json,"Server");

  //Log
  echo "Server Message: ";
  echo json_encode($json)."\n\n";
  //
}

function SendDashboard()
{
  $layoutyModel = getLayout('y');
  $layoutxModel = getLayout('x');
  $anrdoidboxModel = AndroidModel("Dashboard","Send Room List.");
  $json = ["androidbox"=>$anrdoidboxModel,"layoutY"=>$layoutyModel,"layoutX"=>$layoutxModel];
  $myJSON = $json;
  WriteDashboard($myJSON);
  pub($myJSON,"Dashboard");

  //Log
  echo "Dashboard Message: \n\n";
  $myJSON = json_encode($json);
  echo $myJSON."\n\n";

}

function getNurseV1($deviceId_Roomlist)
{

  $urlAndroidbox = "androidbox/$deviceId_Roomlist.json";
  if( file_exists($urlAndroidbox) )
  {
    $data = trim(file_get_contents($urlAndroidbox));
    $myJSONClientDecode = json_decode($data);
    $itaglistClient = $myJSONClientDecode->itag->itag_list;
    $deviceIdClient = $myJSONClientDecode->androidbox->device_id;
    if($deviceId_Roomlist==$deviceIdClient)
    {
      //Check TAG Client
      for($NumNurse=0;count($itaglistClient)>$NumNurse;$NumNurse++)
      {
        $mac_address_client = $itaglistClient[$NumNurse]->mac_address;
        $distance_client = $itaglistClient[$NumNurse]->distance;

        //Get Nickname
        for($NumTitle=0;count($taglistSetting)>$NumTitle;$NumTitle++)
        {
          $mac_address_setting = $taglistSetting[$NumTitle]->mac_address;
          if($mac_address_setting==$mac_address_client)
          {
            $nickname = $taglistSetting[$NumTitle]->nickname;
            $_SESSION["$mac_address_client"] = array(
              $deviceId_Roomlist,
              $mac_address_client,
              $distance_client,
              $nickname,
              date("i:s", time())
            );

            $dataNurse[] = [
              "title"=>$nickname
             ,"mac_address"=>$mac_address_client
             ,"distance"=>$distance_client
             ,"nickname"=>$nickname
            ];

          }
        }
      }
      unlink($urlAndroidbox);
    }
  }
  else
  {
    $dataNurse = [];
  }
  return $dataNurse;
}

function FilterNurse($mac_address_client,$distance_client,$nickname)
{
  if( !isset( $_SESSION["$mac_address_client"] ) )
  {
    $_SESSION["$mac_address_setting"] = array(
      $deviceId_Roomlist,
      $mac_address_setting,
      $distance_client,
      $taglistSetting[$NumTitle]->nickname,
      date("i:s", time())
    );

    $dataNurse = [
      "title"=>$nickname
     ,"mac_address"=>$mac_address_client
     ,"distance"=>$distance_client
     ,"nickname"=>$nickname
    ];
  }
  else
  {
    $dataNurse = [
      "title"=>$nickname
     ,"mac_address"=>$mac_address_client
     ,"distance"=>$distance_client
     ,"nickname"=>$nickname
    ];
  }
  return $dataNurse;
}



function getNurseBackup($deviceId_Roomlist)
{
  //
  $url = "setting/itag_list.json";
  $data = trim(file_get_contents($url));
  $JsonTAGlist = json_decode($data);
  $taglistSetting = $JsonTAGlist->itag_list;
  $dataNurse = [];
  if( !isset($_SESSION["flag_nurse_status"]) )
  {
    $_SESSION["flag_nurse_status"] = 0;
  }
  
  //
  $url = "androidbox/$deviceId_Roomlist.json";
  if( file_exists ( $url )==true )
  {
    $data = trim(file_get_contents($url));
    $myJSONClientDecode = json_decode($data);
      $itaglistClient = $myJSONClientDecode->itag->itag_list;
      $deviceIdClient = $myJSONClientDecode->androidbox->device_id;
      if($deviceId_Roomlist==$deviceIdClient)
      {
        for($NumNurse=0;count($itaglistClient)>$NumNurse;$NumNurse++)
        {
          $mac_address_client = $itaglistClient[$NumNurse]->mac_address;
          $distance_client = $itaglistClient[$NumNurse]->distance;
          for($NumTitle=0;count($taglistSetting)>$NumTitle;$NumTitle++)
          {
            $mac_address_setting = $taglistSetting[$NumTitle]->mac_address;
            if($mac_address_setting==$mac_address_client)
            {
              if( !isset($_SESSION["$mac_address_setting"]) )
              {
                $_SESSION["$mac_address_setting"] = array(
                  $deviceId_Roomlist,
                  $mac_address_setting,
                  $distance_client,
                  $taglistSetting[$NumTitle]->nickname,
                  date("i:s", time())
                );

                $dataNurse[] = [
                  "title"=>$taglistSetting[$NumTitle]->nickname
                  ,"mac_address"=>$mac_address_setting
                  ,"distance"=>$distance_client
                ];
                
                echo "new_session & new_nurse \n";
                echo $taglistSetting[$NumTitle]->nickname." $distance_client  \n";
                echo "========================= \n";
              }
              else if( isset($_SESSION["$mac_address_setting"]) )
              {
                $SESSION_RSSI = $_SESSION["$mac_address_setting"][2];
                $Client_RSSI = $distance_client;
                if($SESSION_RSSI<$Client_RSSI)
                {
                  $dataNurse[] = [
                    "title"=>$taglistSetting[$NumTitle]->nickname
                    ,"mac_address"=>$mac_address_setting
                    ,"distance"=>$distance_client
                  ]; 

                  $_SESSION["$mac_address_setting"] = array(
                    $deviceId_Roomlist,
                    $mac_address_setting,
                    $distance_client,
                    $taglistSetting[$NumTitle]->nickname,
                    date("i:s", time())
                  );

                  echo "new_nurse \n";
                  echo $taglistSetting[$NumTitle]->nickname." $distance_client  \n";
                  echo "========================= \n";

                  //
                  // DELETE TAG OLD
                  //

                }
                else if($SESSION_RSSI>$Client_RSSI)
                {
                  echo "old_nurse \n";
                  echo $_SESSION["$mac_address_setting"][3]."\n";
                  echo " $SESSION_RSSI"."\n";
                  echo "========================= \n";
                  $_SESSION["flag_nurse_status"] = 1;
                }
              }

              //
              if($_SESSION["flag_nurse_status"]==1)
              {
                $old_deviceId_Roomlist = $_SESSION["$mac_address_setting"][0];
                if($deviceId_Roomlist==$old_deviceId_Roomlist)
                {
                  
                  
                    $dataNurse[] = [
                      "title"=>$_SESSION["$mac_address_setting"][3]
                      ,"mac_address"=>$_SESSION["$mac_address_setting"][1]
                      ,"distance"=>$_SESSION["$mac_address_setting"][2]
                    ];

                    echo "old_nurse \n";
                    echo $_SESSION["$mac_address_setting"][3];
                    echo " $SESSION_RSSI \n";
                    echo "========================= \n";                     
                  
                }
              }
              //

            }
          }
        }
      }
  }
  return $dataNurse; 

}

function getLayout($sw)
{
  //
  $url = "setting/layout.json";
  $data = trim(file_get_contents($url));
  $JsonLayout = json_decode($data);
  //

  $url = "setting/androidbox.json";
  $data = trim(file_get_contents($url));
  $JsonRoomlist = json_decode($data);
  $roomlist = $JsonRoomlist->device;
  $CountRoomList = count($roomlist);
  //
  //Create Room
  //Create Layout
    if($JsonLayout->layout_type==3)
    {
      $room_amountX = $JsonLayout->roomX;
      $room_amountY = $JsonLayout->roomY;
    }

    //Layout Y
    $roomYArray = [];
    for($NumRoomY=1;$room_amountY>=$NumRoomY;$NumRoomY++)
    {
      for($NumRoomYY=0;$CountRoomList>$NumRoomYY;$NumRoomYY++)
      {
        if($roomlist[$NumRoomYY]->ordinal==$NumRoomY)
        {
          $roomYArray[] = [
            "ordinal"=>$roomlist[$NumRoomYY]->ordinal,
            "room_title"=>$roomlist[$NumRoomYY]->title,
            "device_id"=>$roomlist[$NumRoomYY]->device_id,
            "nurse_list"=>getNurseV1( $roomlist[$NumRoomYY]->device_id )
          ];
        }
      }

      if( empty($roomYArray[$NumRoomY-1])  )
      {
        $roomYArray[] = [
                  "ordinal"=>$NumRoomY,
                  "room_title"=>"",
                  "device_id"=>"",
                  "nurse_list"=>[]
        ];
      }
    }

    //Layout X
    $NumRoomXStart = $room_amountY+1;
    $NumRoomXEnd = $room_amountX+$NumRoomXStart;
    // echo "Layout Y Amout: $room_amountX | RoomX start: $NumRoomXStart | RoomX end: $NumRoomXEnd \n";
    $roomXArray = [];
    //
    for($NumRoomX=$NumRoomXStart;$NumRoomXEnd>=$NumRoomX;$NumRoomX++)
    {
      if( $CountRoomList>$room_amountY )
      {
        for($NumRoomXX=$NumRoomXX;$CountRoomList>$NumRoomXX;$NumRoomXX++)
        {
          if($roomlist[$NumRoomXX]->ordinal==$NumRoomX)
          {
            $roomXArray[] = [
              "ordinal"=>$roomlist[$NumRoomXX]->ordinal,
              "room_title"=>$roomlist[$NumRoomXX]->room_title,
              "nurse_list"=>getNurseV1( $roomlist[$NumRoomXX]->device_id )
            ];
          }
        }
      }
      else
      {
        $roomXArray[] = [
            "ordinal"=>$NumRoomX,
            "room_title"=>"",
            "nurse_list"=>[]
          ];
      }
    }

    $_SESSION["LayoutY"] = $roomYArray;
    $_SESSION["LayoutX"] = $roomXArray;

    if($sw=='y')
    {
      $layoutModel = ["room_amount"=>$room_amountY,"room_list"=>$roomYArray];
    }
    else if($sw=='x')
    {
      $layoutModel = ["room_amount"=>$room_amountX,"room_list"=>$roomXArray];
    }


  // }
  // else if( $JsonLayout->version == $_SESSION["LayoutVersion"] )
  // {
  //   //Compre Nurse
  //   $LayoutY = $_SESSION["LayoutY"];
  //   $LayoutX = $_SESSION["LayoutX"];
  //   $jsonClient = $_SESSION["jsonClient"];

  //   echo "SESSION Client:  ".json_encode(  $jsonClient  )."\n\n";
  //   echo "SESSION LayoutX:  ".json_encode(  $LayoutX  )."\n\n";
  //   echo "SESSION LayoutY:  ".json_encode(  $LayoutY  )."\n\n";

  //   $json_itag_list =  $jsonClient->itag->itag_list;
  //   $json_deviceId =  $jsonClient->androidbox->device_id;

  //   //Find Room LayoutY
  //   for($NumFindY=0;count($LayoutY)>$NumFindY;$NumFindY++)
  //   {
  //     $deviceId = $LayoutY[$NumFindY]['device_id'];

  //     //Check is Room
  //     if($deviceId==$json_deviceId)
  //     {
  //       $nurse_list = $LayoutY[$NumFindY]['nurse_list'];
        
  //       //Find Nurse
  //       for($NumFindNurseY=0;count($nurse_list)>$NumFindNurseY;$NumFindNurseY++)
  //       {
  //         $distance = $nurse_list[$NumFindNurseY]['distance'];
  //         $mac_address = $nurse_list[$NumFindNurseY]['mac_address'];
  //       }

  //       if( count($nurse_list)==0 )
  //       {
  //         $nurse_list_new[] = [
  //           "title"=>  $json_itag_list[$NumFindClient]->nickname,
  //           "mac_address"=> $json_itag_list[$NumFindClient]->mac_address
  //         ];
  //       }
        
  //       //

  //       //

  //     }
  //   }
  //   $layoutModel = [];
  // }
  //
  return  $layoutModel;
  //
}

function pub($json,$who)
{
  $serverHome = '192.168.1.49';
  $serverFWG  = '10.32.11.18';
  $serverLocal = '192.168.43.234';
  $serverHTTP = 'mqtt.eclipse.org';
  $server = $serverFWG;

  $port  = 1883;
  $username = "";
  $password = "";
  $client_id = "Client-".rand();
  
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