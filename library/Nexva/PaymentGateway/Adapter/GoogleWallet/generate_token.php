<?php
/**
*  This code is generating JWT of a product.
*
* @copyright 2013  Google Inc. All rights reserved.
* @author Rohit Panwar <panwar@google.com>
*/

/**
 * JWT class to encode/decode payload into JWT format.
 */
include_once "lib/JWT.php";

/**
 * Get merchant account information.
 */
include_once "seller_info.php";

/**
 * Get payload of the product.
 */
include_once "payload.php";

$sellerIdentifier = SellerInfo::$issuerId;
$sellerSecretKey = SellerInfo::$secretKey;

$payload = new Payload();
$payload->SetIssuedAt(time());
$payload->SetExpiration(time()+3600);
$payload->AddProperty("name", "Piece of Cake");
$payload->AddProperty("description",
    "Virtual chocolate cake to fill your virtual tummy");
$payload->AddProperty("price", "10.50");
$payload->AddProperty("currencyCode", "USD");
$payload->AddProperty("sellerData",
    "user_id:1224245,offer_code:3098576987,affiliate:aksdfbovu9j");

// Creating payload of the product.
$Token = $payload->CreatePayload($sellerIdentifier);

// Encoding payload into JWT format.
$jwtToken = JWT::encode($Token, $sellerSecretKey);
