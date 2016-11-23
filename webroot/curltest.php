<?php
$connection = curl_init();
if (!is_resource($connection)) {
    exit('Can not initialize connection');
}

$apiKey = 'ecnn8289t8xah98n7st3658d';
$accessToken = 'paTBz61kMlD0mPWrsaHR3wQWQpZQU-i9gXjIkpMaCVaoMR2HTrGVzgVsZlhcOlck6GcsiItVykpcB2lvBYv-4Yh15MpEHwGemlAg2o7FZ5spToOPhg-8YHIDD-7ZMcx0';
$params = [];
$connectionOptions = [];
$request_url = 'https://api.surveymonkey.net/v2/surveys/get_survey_list?api_key=' . $apiKey;

curl_setopt($connection, CURLOPT_URL, $request_url);  // URL to post to
curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1 );   // return into a variable
$headers = array('Content-type: application/json', 'Authorization: Bearer ' . $accessToken);
curl_setopt($connection, CURLOPT_HTTPHEADER, $headers ); // custom headers
curl_setopt($connection, CURLOPT_HEADER, false );     // return into a variable
curl_setopt($connection, CURLOPT_POST, true);     // POST
$postBody = (!empty($params))? json_encode($params) : "{}";
curl_setopt($connection, CURLOPT_POSTFIELDS,  $postBody);
curl_setopt_array($connection, $connectionOptions);  // (optional) additional options

// Added by Graham Watson to try to fix the following curl error:
// error:14077410:SSL routines:SSL23_GET_SERVER_HELLO:sslv3 alert handshake failure
curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($connection, CURLOPT_SSLVERSION, 1);
curl_setopt($connection, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');

$result = curl_exec( $connection );
if ($result === false) {
    exit('Curl Error: ' . curl_error($connection));
}

curl_close($connection);

$parsedResult = json_decode($result, true);
$jsonErr = json_last_error();
if ($parsedResult === null  && $jsonErr !== JSON_ERROR_NONE) {
    exit("Error [$jsonErr] parsing result JSON");
}

$status = $parsedResult['status'];
if ($status != 0) {
    exit("API Error: Status $status, Message [" . $parsedResult["errmsg"] . ']');
}

echo 'Success! Results:<br /><pre>' . print_r($parsedResult, true) . '</pre>';