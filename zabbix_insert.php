<?php
function getModel($year,$brand){
  $ch = curl_init();
  $url = "https://www.tipinsure.com/Motor/getModelCarNewFromFundTable";

  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch,CURLOPT_POSTFIELDS,"brand_name=$brand&voluntary_code=110&car_group=2%2C3%2C4%2C5&car_year=$year");
  $output = curl_exec($ch);
  curl_close($ch);
  return $output;
}

function getSubModel($year,$brand,$modelName){
  $ch = curl_init();
  $url = "https://www.tipinsure.com/Motor/getSpecCarNewFromFundTable";

  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch,CURLOPT_POSTFIELDS,"model_name=$modelName&brand_name=$brand&voluntary_code=110&car_group=2%2C3%2C4%2C5&car_year=$year");
  $output = curl_exec($ch);
  curl_close($ch);
  return $output;
}

function SubmitForm($year,$brand,$modelName,$spec,$insure_type){
  global $cookie;
  $ch = curl_init();
  $url = "https://www.tipinsure.com/Motor/submit_step_1/";
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch,CURLOPT_POSTFIELDS,"motor_type=$insure_type&brand_name_text=$brand&model_name_text=$modelName&spec_name_text=$spec&car_register_year=$year&brand_id=$brand&free_text_brand_name=&motor_voluntary_code=110&free_text_voluntary_code_name=dc&model_id=$modelName&free_text_model_name=&spec_id=$spec&free_text_spec_name=&car_cc=1&car_seat=0&car_weight=0&motor_product_type=$insure_type");
  $output = curl_exec($ch);
  preg_match('/^Set-Cookie:\s*(.+)/mi', $output, $matches);
  preg_match('/{"success":[a-z]+}/mi',$output,$json_arr);
  //var_dump($json_arr);
  $cookie = $matches[1];
  //echo $cookie."\n";
  if(isset($json_arr)){
    $output = $json_arr[0];
  }else{
    $output = false;
    
  }
  curl_close($ch);
  return $output;
}


function step2($insure,$cookie){
  $ch = curl_init();
  $url = "https://www.tipinsure.com/Motor/motor_step_2/$insure";
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie: $cookie"));

  //curl_setopt($ch, CURLOPT_POST, 1);
  //curl_setopt($ch,CURLOPT_POSTFIELDS,"model_name=$modelName&brand_name=$brand&voluntary_code=110&car_group=2%2C3%2C4%2C5&car_year=$year");
  $output = curl_exec($ch);
  curl_close($ch);
  return $output;
}

require("zb.php");
$output = authen("Admin","admin@zabbix");
$token = json_decode($output)->result;
//var_dump($brand);
$brand = array(
"TOYOTA","HONDA","ISUZU","NISSAN","MITSUBISHI","MAZDA","FORD","SUZUKI",
"ALFA ROMEO","AUDI","BMW","CHEVROLET","HYUNDAI","KIA","LAND ROVER","LEXUS",
"MERCEDES-BENZ","MG","MINI","PEUGEOT","PROTON","SUBARU","TATA","VOLKSWAGEN","VOLVO","OTHER"
);
$year = array(
  2017,2018
);

$insure_type = array(
  "first_class",
  "first_class_lady",
  "3",
  "2_plus",
  "3_plus"
);

foreach($brand as $each_brand){
  foreach($year as $each_year){
     //echo "$each_brand,$each_year\n";
     $output = getModel($each_year,$each_brand);
     $model_json = json_decode($output);
     foreach($model_json->data as $modelName){
       $output = getSubModel($each_year,$each_brand,$modelName);
       $submodel_json = json_decode($output);
       //var_dump($submodel_json);
       foreach($submodel_json->data as $spec){
         foreach($insure_type as $each_insure){

            $output = SubmitForm($each_year,$each_brand,$modelName,$spec,$each_insure);
            $success = json_decode($output)->success;
            if($success){
              $output = step2($each_insure,$cookie);
              preg_match("/\(\'\#package_default_premium\'\)\.val\(\"([0-9\.]+)\"\)/mi",$output,$matches);
              //var_dump($matches);
              if($token){
                //2009,TOYOTA,ALPHARD,2.4,first_class,27000.38
                $expectation = (isset($matches[1]))?$matches[1]:"true";
                $result =  http_create($token,$each_year,$each_brand,$modelName,$spec,$each_insure,$expectation);
                //echo $result;
              }
              echo "$each_year,$each_brand,$modelName,$spec,$each_insure,$matches[1]\n";
            }else{
              echo "$each_year,$each_brand,$modelName,$spec,$each_insure,false\n";
            }
         }
       }
     }
  }
}
