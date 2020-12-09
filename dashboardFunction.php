<?php

function SendDashboard()
{
  $layoutyModel = getLayout('y');
  $layoutxModel = getLayout('x');
  $anrdoidboxModel = AndroidModel("Dashboard","Send Room List.");
  $json = ["androidbox"=>$anrdoidboxModel,"layoutY"=>$layoutyModel,"layoutX"=>$layoutxModel];
  $myJSON = $json;
  WriteDashboard($myJSON);
  PublishMessage($myJSON,"Dashboard");

  //Log
//   echo "Dashboard Message: \n\n";
  $myJSON = json_encode($json);
//   echo $myJSON."\n\n";

}

function WriteDashboard($json)
{
  $filename = "JSONdashboard.json";
  $file_encode = json_encode($json,true);
  file_put_contents($filename, $file_encode );
  chmod($filename,0777);
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
            "nurse_list"=>getNurseV2( $roomlist[$NumRoomYY]->device_id )
          ];
        }
      }

      if( empty($roomYArray[$NumRoomY-1])  )
      {
        $roomYArray[] = [
                  "ordinal"=>$NumRoomY,
                  "room_title"=>"ห้องว่าง",
                  "device_id"=>"",
                  "nurse_list"=>[]
        ];
      }
    }

    //Layout X
    $NumRoomXStart = $room_amountY+1;
    $NumRoomXEnd = $room_amountX+$room_amountY;
    // echo "Layout X Amout: $room_amountX | Room start: $NumRoomXStart | Room end: $NumRoomXEnd \n";
    $roomXArray = [];
    //
    for($NumRoomX=$room_amountY;$NumRoomXEnd>=$NumRoomX;$NumRoomX++)
    {
      if( $CountRoomList>$room_amountY )
      {
        for($NumRoomXX=$room_amountY;$CountRoomList>$NumRoomXX;$NumRoomXX++)
        {
            $ordinal = $roomlist[$NumRoomXX]->ordinal;
            $title = $roomlist[$NumRoomXX]->title;
            if($ordinal==$NumRoomX)
            {
                $roomXArray[] = [
                "ordinal"=>$ordinal,
                "room_title"=>$title,
                "nurse_list"=>getNurseV2( $roomlist[$NumRoomXX]->device_id )
                ];
            }
        }

        if( empty($roomXArray[$NumRoomX-1])  )
        {
          $roomXArray[] = [
                    "ordinal"=>$NumRoomX,
                    "room_title"=>"ห้องว่าง",
                    "device_id"=>"",
                    "nurse_list"=>[]
          ];
        }

      }
      else
      {
        $roomXArray[] = [
            "ordinal"=>$NumRoomX,
            "room_title"=>"ห้องว่าง",
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

  return  $layoutModel;

}

Function getNurseV2($androidbox)
{
    $nurse = [];
    $url = "TAGListStatus.json";
    $data = trim(file_get_contents($url));
    $json_nurse = json_decode($data);

    $url = "setting/itag_list.json";
    $data = trim(file_get_contents($url));
    $json_name = json_decode($data)->itag_list;

    for($NumA=0;count($json_nurse)>$NumA;$NumA++)
    {
        if($json_nurse[$NumA]->androidbox==$androidbox)
        {
            for($NumB=0;count($json_name)>$NumB;$NumB++)
            {
                if( $json_nurse[$NumA]->mac_address==$json_name[$NumB]->mac_address && $json_nurse[$NumA]->androidbox!="" )
                {
                    $nurse[] = [
                        "title"=>$json_name[$NumB]->title,
                        "fullname"=>$json_name[$NumB]->fullname
                    ];

                }            
            }            
        }
    }
    return $nurse;
}