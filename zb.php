<?php
function authen($username,$password){
  $ch = curl_init();
  $url = "http://localhost/zabbix/api_jsonrpc.php";

  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json-rpc"));
  curl_setopt($ch,CURLOPT_POSTFIELDS,'{"jsonrpc": "2.0","method":"user.login","params": {"user": "'.$username.'","password": "'.$password.'"},"id": 1,"auth": null}');
  $output = curl_exec($ch);
  curl_close($ch);
  return $output;
}

function http_create($token,$year,$brand,$modelName,$spec,$insure_type,$hostid,$expectation = ''){
  if(!$expectation){
    $expectation = 'true';
  }
  $ch = curl_init();
  $url = "http://localhost/zabbix/api_jsonrpc.php";

  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json-rpc"));
  curl_setopt($ch,CURLOPT_POSTFIELDS,'{
    "jsonrpc": "2.0",
    "method": "httptest.create",
    "params": {
        "name": "'.$year.','.$brand.','.$modelName.','.$spec.','.$insure_type.'",
        "hostid": "'.$hostid.'",
        "delay": "3600",
        "steps": [
            {
                "name": "'.$year.','.$brand.','.$modelName.','.$spec.','.$insure_type.' step1",
                "url": "https://www.tipinsure.com/Motor/submit_step_1/",
                "status_codes": "200",
                "no": 1,
                "posts": "motor_type='.$insure_type.'&brand_name_text='.$brand.'&model_name_text='.$modelName.'&spec_name_text='.$spec.'&car_register_year='.$year.'&brand_id='.$brand.'&free_text_brand_name=&motor_voluntary_code=110&free_text_voluntary_code_name=dc&model_id='.$modelName.'&free_text_model_name=&spec_id='.$spec.'&free_text_spec_name=&car_cc=1&car_seat=0&car_weight=0&motor_product_type='.$insure_type.'",
                "required": "true"
            },{
                "name": "'.$year.','.$brand.','.$modelName.','.$spec.','.$insure_type.' step2",
                "url": "https://www.tipinsure.com/Motor/motor_step_2/'.$insure_type.'",
                "status_codes": "200",
                "no": 2,
                "required": "'.$expectation.'"
            }

        ]
    },
    "auth": "'.$token.'",
    "id": 1
}');
  $output = curl_exec($ch);
  curl_close($ch);
  return $output;
}

function hostCreate($token,$hostname){
    $ch = curl_init();
    $url = "http://localhost/zabbix/api_jsonrpc.php";
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json-rpc"));
    curl_setopt($ch,CURLOPT_POSTFIELDS,'{
      "jsonrpc": "2.0",
      "method": "host.create",
      "params": {
         "host": "'.$hostname.'",
         "interfaces": [
            {
                "type": 1,
                "main": 1,
                "useip": 1,
                "ip": "127.0.0.1",
                "dns": "",
                "port": "10050"
            }
         ],
         "groups": [
            {
                "groupid": "12"
            }
         ]
      },
      "auth": "'.$token.'",
      "id": 1
   }');

   $output = curl_exec($ch);
   curl_close($ch);
   return $output;
}

function triggerCreate($token,$desc,$exp){
    $ch = curl_init();
    $url = "http://localhost/zabbix/api_jsonrpc.php";
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json-rpc"));

    $json = '{
      "jsonrpc": "2.0",
      "method": "trigger.create",
      "params": {
          "description": "Step failed '.$desc.'",
          "expression": "'.$exp.'"
      },
      "auth": "'.$token.'",
      "id": 1
    }';
    curl_setopt($ch,CURLOPT_POSTFIELDS,$json);

   //echo $json;
   $output = curl_exec($ch);
   curl_close($ch);
   return $output;
}

/*
$output = authen("Admin","zabbix");
$token = json_decode($output)->result;
if($token){
  //2009,TOYOTA,ALPHARD,2.4,first_class,27000.38
  $year = "2009";
  $brand = "TOYOTA";
  $modelName = "ALPHARD";
  $spec = "2.4";
  $insure_type = "first_class";
  echo http_create($token,$year,$brand,$modelName,$spec,$insure_type,$expectation);
}
*/

?>
