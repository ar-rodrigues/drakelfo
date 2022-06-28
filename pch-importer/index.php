<?php
define('PCH_CUSTOMER', 19043);
define('PCH_KEY', "Kaiser27");

if (isset($_GET['full_update'])) {
  pch_get_all_products();
}

if (!file_exists('stock.json')) {
  pch_get_all_products();
}

function pch_get_all_products()
{
  $url = "https://pchm.to-do.mx/extcust/getprodlist/";

  $curl = curl_init($url);
  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_POST, true);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

  $headers = array(
    "Content-Type: application/json",
  );
  curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

  $data = '{"customer":"19043","key":"Kaiser27"}';

  curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

  //for debug only!
  curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

  $resp = curl_exec($curl);
  curl_close($curl);


  $response = json_decode($resp, TRUE);
  var_dump($response);
  if ($response['status'] == 200) {
    file_put_contents('stock.json', $resp);
    echo 'La base de datos fue actualizada';
  } else {
    echo 'La base de datos no se pudo actualizar. Intentalo mas tarde.';
  }
}
