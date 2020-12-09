<?php

function AndroidModel($device_id,$Message) 
{
  $DateTimeModel = date("Y-m-d h:i:s", time());
  $anrdoidboxModel = ["datetime"=>$DateTimeModel, "device_id"=>$device_id, "message"=>$Message];
  return $anrdoidboxModel;
}