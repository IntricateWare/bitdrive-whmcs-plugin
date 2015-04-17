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

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly.');
}

/**
 * Get the configuration keys and structure for the BitDrive Standard Checkout module.
 *
 * @return array
 */
function bitdrivestandard_config() {
    return array(
        'FriendlyName' => array(
            'Type'  => 'System',
            'Value' => 'BitDrive Standard Checkout'
        ),
        'UsageNotes' => array(
            'Type'  => 'System',
            'Value' => 'Accept bitcoin payments using BitDrive Standard Checkout.'
        ),
        'merchantId' => array(
            'FriendlyName'  => 'Merchant ID',
            'Type'          => 'text',
            'Size'          => '36',
            'Description'   => 'The BitDrive merchant ID which can be found in Merchant Tools.'
        ),
        'ipnSecret' => array(
            'FriendlyName'  => 'IPN Secret',
            'Type'          => 'text',
            'Description'   =>
                'The Instant Payment Notification (IPN) secret which you configured in your BitDrive merchant account settings.'
        )
    );
}

/**
 * Build the BitDrive Standard Checkout form based on the specified parameters.
 *
 * @param array $params
 * 
 * @return string
 */
function bitdrivestandard_link($params) {
    $checkoutUrl = 'https://www.bitdrive.io/pay';
    
    $memo = $params['description'];
    if (strlen($memo) > 200) {
        $memo = substr($memo, 0, 200);
    }
    
    $fields = array(
        'bd-cmd'            => 'pay',
        'bd-merchant'       => $params['merchantId'],
        'bd-currency'       => $params['currency'],
        'bd-amount'         => $params['amount'],
        'bd-memo'           => $memo,
        'bd-invoice'        => $params['invoiceid'],
        'bd-success-url'    => $params['systemurl']
    );
    
    // Build the HTML form fields string based on the field name/value pairs
    $htmlFields = '';
    foreach ($fields as $name => $value) {
        $htmlFields .= sprintf('<input type="hidden" name="%s" value="%s" />', $name, $value);
    }
    
    // Build the HTML form string
    $html  = sprintf('<form method="post" action="%s">', $checkoutUrl);
    $html .= $htmlFields;
    $html .= sprintf('<input type="submit" value="%s" />', $params['langpaynow']);
    $html .= '</form>';
    
    return $html;
}

?>