<?php

function CheckVersionTAG($a)
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

function CheckAndroidbox($a)
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

function FilterMacAddressClient($itag_list_client)
{
    $url = "setting/itag_list.json";
    $data = trim(file_get_contents($url));
    $json = json_decode($data);
    $iTAGListSetting = $json->itag_list;

    for($NumA=0;count($iTAGListSetting)>$NumA;$NumA++)
    {
        $mac_address_setting = $iTAGListSetting[$NumA]->mac_address;
        if($mac_address_setting==$itag_list_client[0])
        {
            $jsonWeb = [
                $itag_list_client[0],
                $itag_list_client[1],
                $itag_list_client[2],
                $itag_list_client[4]

            ];

            wr($jsonWeb);
            




            UpdateTAGListStatus($itag_list_client);  
        }    
    }
}

function UpdateTAGListStatus($itag_list_client)
{
    include("env.php");
    $url = "TAGListStatus.json";
    $data = trim(file_get_contents($url));
    $json = json_decode($data);
    for($NumA=0;count($json)>$NumA;$NumA++)
    {
        $mac_address_client = $itag_list_client[0];
        $mac_address_json = $json[$NumA]->mac_address;
        if($mac_address_json==$mac_address_client)
        {
            $distance_file = $json[$NumA]->distance;
            $distance_client = $itag_list_client[1];

            $androidbox_file = $json[$NumA]->androidbox;
            $androidbox_client = $itag_list_client[2];

            $updated_at_file = $json[$NumA]->updated_at;
            $updated_at_client = $itag_list_client[3];

            $array = [];

            echo $mac_address_client." Distance: Old ".$distance_file." | New ".$distance_client." ";

            /*
            if($distance_file<$distance_client)
            {
                echo "is New , Update: Distance & Time \n";
                WriteTAGListStatus($itag_list_client,"distance");
                WriteTAGListStatus($itag_list_client,"updated_at");
            }
            else if($distance_file>$distance_client)
            {
                echo "is Old , Update: Distance & Time \n\n";
                WriteTAGListStatus($itag_list_client,"distance");
                WriteTAGListStatus($itag_list_client,"updated_at");
            }
            else if($distance_file==$distance_client)
            {

            }

            echo "Androidbox: ";

            if($json[$NumA]->androidbox!=$androidbox_client)
            {
                echo "Update Box & Time \n";
                WriteTAGListStatus($itag_list_client,"androidbox");
                WriteTAGListStatus($itag_list_client,"updated_at");
            }
            else if($json[$NumA]->androidbox==$androidbox_client)
            {
                WriteTAGListStatus($itag_list_client,"updated_at");
                echo "Update Time \n";
            }
            */

            //     
            $DateTimeEpoch = strtotime( date("Y-m-d h:i:s", time()) );
            $TimeStamp = $updated_at_file;
            $timeout = $DateTimeEpoch-$TimeStamp;

            echo "\nTry: ".$DateTimeEpoch." - ".$TimeStamp." = ".$timeout."\n";

            if($androidbox_file!=$androidbox_client)
            {
                    echo "Nurse Update 10 Sec\n";

                    if($distance_client==$distance_file)
                    {
                        if( ($updated_at_client+2) > ($updated_at_client>=$updated_at_file) )
                        {
                            WriteTAGListStatus($itag_list_client,"androidbox");
                            echo "\n New Androidbox , Update: Distance & Time \n";
                        }
                        else
                        {
                            echo "\n Old Androidbox , Update: Distance & Time \n";
                        }
                    }
                    else
                    {
                        echo "\n New Androidbox , Update: Distance & Time \n";
                        WriteTAGListStatus($itag_list_client,"distance");
                        WriteTAGListStatus($itag_list_client,"androidbox");
                    }
                    WriteTAGListStatus($itag_list_client,"updated_at");
            }
            else if($androidbox_file==$androidbox_client)
            {
                WriteTAGListStatus($itag_list_client,"distance");
                WriteTAGListStatus($itag_list_client,"updated_at");                    
            }

            // echo date("Y-m-d h:i:s", time())."\n";

        }

    }

    //Check 10 Sec
    ClearList();
    //

}

function WriteTAGListStatus($json,$type)
{
    include("env.php");
    
    $mac_address_client = $json[0];

    $url = "TAGListStatus.json";
    $data = trim(file_get_contents($url));
    $jsonFile = json_decode($data);
    for($NumA=0;count($jsonFile)>$NumA;$NumA++)
    {

        // echo $jsonFile[$NumA]->mac_address."==".$mac_address_client."\n\n";

        if($jsonFile[$NumA]->mac_address==$mac_address_client)
        {
            if($type=="updated_at")
            {
                $updated_at = strtotime( date("Y-m-d h:i:s", time()) );
            }
            else
            {
                $updated_at = $jsonFile[$NumA]->updated_at;
            }

            if($type=="distance")
            {
                $distance = $json[1];
            }    
            else
            {
                $distance = $jsonFile[$NumA]->distance;
            }

            if($type=="androidbox")
            {
                $androidbox = $json[2];
            }
            else
            {
                $androidbox = $jsonFile[$NumA]->androidbox;
            }

            if($type=="clearlist")
            {
                $distance = $ENV_DISTANCE_CLEAR_LIST;
                $androidbox = $ENV_ANROIDBOX_CLEAR_LIST;
                $updated_at = $ENV_UPDATED_AT_CLEAR_LIST;

                echo "Clear List $mac_address_client \n\n";
            }

            $new_json = [
                "mac_address"=>$mac_address_client,
                "distance"=>$distance,
                "androidbox"=>$androidbox,
                "updated_at"=>$updated_at
            ];

            $jsonTemp[] = $new_json;
        }
        else
        {
            $jsonTemp[] = $jsonFile[$NumA];
        }
    }
    //
    $filename = "TAGListStatus.json";
    $file_encode = json_encode($jsonTemp,true);
    file_put_contents($filename, $file_encode );
    chmod($filename,0777);
}

function  wr($abc)
{
    include("env.php");

    $jsonTemp = [];
    
    
    $filename = "logger.json";
    $file_encode = json_encode($jsonTemp,true);
    file_put_contents($filename, $file_encode );
    chmod($filename,0777);

}