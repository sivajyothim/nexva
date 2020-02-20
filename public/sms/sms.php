<?php
$rand_number=rand(1000, 9999);
$cSession = curl_init();
curl_setopt($cSession, CURLOPT_URL, "https://rest.messagebird.com/messages?access_key=iO5Ys42xXLnWx48Ev1bXxZeNx");
curl_setopt($cSession, CURLOPT_RETURNTRANSFER, true);
curl_setopt($cSession, CURLOPT_POST, 1);
curl_setopt($cSession, CURLOPT_POSTFIELDS, "recipients=".$_POST['phone']."&originator=eCarrot&body=". $rand_number);
curl_setopt($cSession, CURLOPT_HEADER, false);
curl_setopt($cSession, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($cSession);
curl_close($cSession);
$resultArray= json_decode($result);
$item=$resultArray->recipients->items;

if (($item[0]->status == 'sent')  && (isset($item[0]->status))) {
    die(json_encode(array('status'=>'success','token'=>$rand_number)));   
}else{
   die(json_encode(array('status'=>'fail','token'=>''))); 
}
    