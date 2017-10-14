<?php
/**
 * WHMCS Sample Payment Callback File
 *
 * This sample file demonstrates how a payment gateway callback should be
 * handled within WHMCS.
 *
 * It demonstrates verifying that the payment gateway module is active,
 * validating an Invoice ID, checking for the existence of a Transaction ID,
 * Logging the Transaction for debugging and Adding Payment to an Invoice.
 *
 * For more information, please refer to the online documentation.
 *
 * @see https://developers.whmcs.com/payment-gateways/callbacks/
 *
 * @copyright Copyright (c) WHMCS Limited 2017
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */

if ( strtolower($_SERVER['REQUEST_METHOD']) !== 'post' ) {
    http_response_code(405);
    die('Only POST is allowed');
}

$pwd = getcwd();

// Require libraries needed for gateway module functions.
require_once $pwd . '/../../../init.php';
require_once $pwd . '/../../../includes/gatewayfunctions.php';
require_once $pwd . '/../../../includes/invoicefunctions.php';
require_once $pwd . '/../moota/autoload.php';

use Moota\SDK\Config;
use Moota\SDK\PushCallbackHandler;
use WHMCS\Database\Capsule;

// Detect module name from filename.
$gatewayModuleName = basename(__FILE__, '.php');

// Fetch gateway configuration parameters.
$gatewayParams = getGatewayVariables($gatewayModuleName);

// Die if module is not active.
if (!$gatewayParams['type']) {
    http_response_code(501);
    die('Module Not Activated');
}

Config::fromArray([
    'apiKey' => $gatewayParams['mootaApiKey'],
    'apiTimeout' => $gatewayParams['mootaApiTimeout'],
    'sdkMode' => strtolower( $gatewayParams['mootaEnvironment'] ),
    'serverAddress' => $gatewayParams['mootaServerAddress'],
]);

$mootaInflows = [];
$whereInflowAmounts = [];
$payments = [];

$pushReplyData = [];

$transactions = PushCallbackHandler::createDefault()->decode();

// only CR
foreach ($transactions as $trans) {
    if ($trans['type'] === 'CR') {
        $mootaInflows[] = $trans;
        $whereInflowAmounts[] = $trans['amount'];
    }
}

$invoices = Capsule::table('tblinvoices')
    ->whereIn('total', $whereInflowAmounts)
    ->where('status', 'Unpaid')
    ->get();

if ( ! empty($invoices) && count($invoices) > 0 ) {
    // match whmcs invoice with moota transactions
    foreach ($invoices as $invoice) {
        $transAmount = (int) str_replace('.00', '', $invoice->total . '');
        $tmpPayment = null;
    
        foreach ($mootaInflows as $mootaInflow) {
            if ($mootaInflow['amount'] === $transAmount) {
                $tmpPayment = $mootaInflow;
                break;
            }
        }
    
        $payments[]  = [
            // transactionId:
            //   { invoiceId }-{ moota:id }-{ moota:account_number }
            'transactionId' => implode('-', [
                $invoice->id, $tmpPayment['id'], $tmpPayment['account_number']
            ]),
            'invoiceId' => $invoice->id,
            'mootaId' => $tmpPayment['id'],
            'mootaAccNo' => $tmpPayment['account_number'],
            'amount' => $tmpPayment['amount'],
            'mootaAmount' => $tmpPayment['amount'],
            'invoiceAmount' => $invoice->total,
        ];
    }

    $pushReplyData['data'] = [
        'dataCount' => count($transactions),
        'inflowCount' => count($mootaInflows),
        'payments' => $payments,
    ];

    if ( count($payments) > 0 ) {
        // finally add payment and log to gateway logs
        foreach ($payments as $payment) {
            /**
             * Add Invoice Payment.
             *
             * Applies a payment transaction entry to the given invoice ID.
             *
             * @param int $invoiceId         Invoice ID
             * @param string $transactionId  Transaction ID
             * @param float $paymentAmount   Amount paid (defaults to full balance)
             * @param float $paymentFee      Payment fee (optional)
             * @param string $gatewayModule  Gateway module name
             */
            addInvoicePayment(
                $payment['invoiceId'],
                $payment['transactionId'],
                $payment['amount'],
                0,
                $gatewayModuleName
            );

            /**
             * Log Transaction.
             *
             * Add an entry to the Gateway Log for debugging purposes.
             *
             * The debug data can be a string or an array. In the case of an
             * array it will be
             *
             * @param string $gatewayName        Display label
             * @param string|array $debugData    Data to log
             * @param string $transactionStatus  Status
             */
            logTransaction($gatewayParams['name'], $payment, 'Paid');
        }

        $pushReplyData['status'] = 'ok';
    } else {
        $pushReplyData['status'] = 'not-ok';
        $pushReplyData['status'] = 'No unpaid invoice matches current push data';
    }
} else {
    $pushReplyData['status'] = 'not-ok';
    $pushReplyData['error'] = 'No unpaid invoice found';
}

header('Content-Type: application/json');

die( json_encode( $pushReplyData ) );
