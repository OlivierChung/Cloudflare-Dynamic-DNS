<?php

/*
 * Copyright 2019 Olivier Chung
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *  
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

error_reporting(E_ALL|E_STRICT);

// Get domain from subdomain
function get_domain($host){
    $myhost = strtolower(trim($host));
    $count = substr_count($myhost, '.');
    if($count === 2){
        if(strlen(explode('.', $myhost)[1]) > 3) $myhost = explode('.', $myhost, 2)[1];
    }
    else if($count > 2){
        $myhost = get_domain(explode('.', $myhost, 2)[1]);
    }
    return $myhost;
}

// Get Zone ID and Record ID
function requestID($url){
    global $headers;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result, true);
}

// Update Record IP address
function updateRecord($url){
    global $headers, $record, $ip;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $newRecord = array( 
            'type' => 'A', 
            'name' => $record, 
            'content' => $ip, 
            'ttl' => 120, 
            'proxied' => false 
        ); 
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($newRecord));
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result, true);
}

//header('WWW-Authenticate: Basic realm="Cloudflare DDNS Updater"');

$email = empty($_SERVER['PHP_AUTH_USER']) ? exit("noauth") : $_SERVER['PHP_AUTH_USER'];
$token = empty($_SERVER['PHP_AUTH_PW']) ? exit("noauth") : $_SERVER['PHP_AUTH_PW'];
$record = empty($_GET['hostname']) ? exit("nohost") : $_GET['hostname'];
$ip = empty($_GET['myip']) ? exit("noip") : $_GET['myip'];
$zone = get_domain($record);

// Check if IP has changed or not
if (gethostbyname($record) == $ip) exit("nochg");

// Init headers
$headers = array( 
  'X-Auth-Key: ' . $token,
  'X-Auth-Email: ' . $email,
  'Content-Type: application/json',
); 

// Get Zone ID
$urlRequest_ZoneID = "https://api.cloudflare.com/client/v4/zones?name=" . $zone;
$zoneID = requestID($urlRequest_ZoneID)['result'][0]['id'];

// Get Record ID
$urlRequest_RecordID = "https://api.cloudflare.com/client/v4/zones/" . $zoneID . "/dns_records?type=A&name=" . $record;
$recordID = requestID($urlRequest_RecordID)['result'][0]['id'];

// Build update URL
$urlRequest_Update = "https://api.cloudflare.com/client/v4/zones/" . $zoneID . "/dns_records/" . $recordID;

// Update Record
$result = updateRecord($urlRequest_Update);
if ($result['success'] == true) echo "good";

?>