<?php
/**
 * MootaPay Payment Gateway - Push Notification Handler
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
require_once $pwd . '/../moota/lib/autoload.php';

// Detect module name from filename.
$gatewayModuleName = basename(__FILE__, '.php');

// Fetch gateway configuration parameters.
$gatewayParams = getGatewayVariables($gatewayModuleName);

// Die if module is not active.
if (!$gatewayParams['type']) {
    http_response_code(501);
    die('Module Not Activated');
}

Moota\SDK\Config::fromArray([
    'apiKey' => $gatewayParams['mootaApiKey'],
    'apiTimeout' => $gatewayParams['mootaApiTimeout'],
    'sdkMode' => strtolower( $gatewayParams['mootaEnvironment'] ),
]);

$handler = Moota\SDK\PushCallbackHandler::createDefault()
    ->setOrderFetcher(new Moota\WHMCS\InvoiceFetcher(
        new WHMCS\Database\Capsule
    ))
    ->setOrderMatcher(new Moota\WHMCS\InvoiceMatcher)
    ->setOrderFullfiler(new Moota\WHMCS\InvoiceFullfiler(
        $gatewayModuleName, $gatewayParams
    ))
;

$statusData = $handler->handle();

header('Content-Type: application/json');

die( json_encode( $statusData ) );
