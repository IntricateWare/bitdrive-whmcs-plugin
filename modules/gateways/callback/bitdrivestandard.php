<?php

/*
 * Copyright (c) 2015 IntricateWare Inc.
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

include('../../../dbconnect.php');
include('../../../includes/functions.php');
include('../../../includes/gatewayfunctions.php');
include('../../../includes/invoicefunctions.php');
 
$gatewaymodule = 'bitdrivestandard';

$gateway = getGatewayVariables($gatewaymodule);
if (!$gateway['type']) {
    die('Payment module not activated.');
}

// Only process HTTP POST requests
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    exit;   
}

// Check for SHA 256 support
if (!in_array('sha256', hash_algos())) {
    logTransaction($gateway['name'], null, 'The PHP installation does not support the SHA 256 hash algorithm.');
    exit;
}

// Check the IPN data
$data = file_get_contents('php://input');
$json = json_decode($data);
if (!$json) {
    logTransaction($gateway['name'], $data, 'The BitDrive IPN JSON data is invalid.');
    exit;
}

// Check for the IPN parameters that are required
$requiredIpnParams = array(
    'notification_type',
    'sale_id',
    'merchant_invoice',
    'amount',
    'bitcoin_amount'
);
foreach ($requiredIpnParams as $param) {
    if (!isset($json->$param) || strlen(trim($json->$param)) == 0) {
        logTransaction($gateway['name'], $data, sprintf('Missing %s IPN parameter.', $param));
        exit;
    }
}

// Verify the SHA 256 hash
$merchantId = $gateway['merchantId'];
$ipnSecret = $gateway['ipnSecret'];
$hashString = strtoupper(hash('sha256', $json->sale_id . $merchantId . $json->merchant_invoice . $ipnSecret));
if ($hashString != $json->hash) {
    logTransaction($gateway['name'], $data, 'The notification message cannot be processed due to a hash mismatch.');
    exit;
}

// Check the inovice ID
$invoiceId = checkCbInvoiceID($json->merchant_invoice, $gateway['name']);

// Check the transaction ID
$transactionId = $json->sale_id;
checkCbTransID($transactionId);

switch ($json->notification_type) {
    // Order created
    case 'ORDER_CREATED':
        logTransaction($gateway['name'], $data, $json->notification_description);
        break;
    
    // Payment completed
    case 'PAYMENT_COMPLETED':
        addInvoicePayment($invoiceId, $transactionId, $json->amount, 0, $gatewaymodule);
        logTransaction($gateway['name'], $data, $json->notification_description);
        break;
    
    // Transaction cancelled/expired
    case 'TRANSACTION_CANCELLED':
    case 'TRANSACTION_EXPIRED':
        logTransaction($gateway['name'], $data, $json->notification_description);
        break;
}

?>