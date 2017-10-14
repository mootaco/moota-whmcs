<?php
/**
 * WHMCS Sample Payment Gateway Module
 *
 * Payment Gateway modules allow you to integrate payment solutions with the
 * WHMCS platform.
 *
 * This sample file demonstrates how a payment gateway module for WHMCS should
 * be structured and all supported functionality it can contain.
 *
 * Within the module itself, all functions must be prefixed with the module
 * filename, followed by an underscore, and then the function name. For this
 * example file, the filename is "gatewaymodule" and therefore all functions
 * begin "gatewaymodule_".
 *
 * If your module or third party API does not support a given function, you
 * should not define that function within your module. Only the _config
 * function is required.
 *
 * For more information, please refer to the online documentation.
 *
 * @see https://developers.whmcs.com/payment-gateways/
 *
 * @copyright Copyright (c) WHMCS Limited 2017
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Define module related meta data.
 *
 * Values returned here are used to determine module related capabilities and
 * settings.
 *
 * @see https://developers.whmcs.com/payment-gateways/meta-data-params/
 *
 * @return array
 */
function moota_MetaData()
{
    return [
        'DisplayName' => 'Moota Payment Gateway',
        'APIVersion' => '1.1', // Use API Version 1.1
        'DisableLocalCredtCardInput' => true,
        'TokenisedStorage' => false,
    ];
}

/**
 * Define gateway configuration options.
 *
 * The fields you define here determine the configuration options that are
 * presented to administrator users when activating and configuring your
 * payment gateway module for use.
 *
 * Supported field types include:
 * * text
 * * password
 * * yesno
 * * dropdown
 * * radio
 * * textarea
 *
 * Examples of each field type and their possible configuration parameters are
 * provided in the sample function below.
 *
 * @return array
 */
function moota_config()
{
    $paths = explode('/', dirname( $_SERVER['DOCUMENT_URI'] ));
    array_pop($paths);
    $paths = implode('/', $paths);
    $baseUri = $_SERVER['SERVER_NAME'] . realpath(
        dirname( $_SERVER['DOCUMENT_URI'] ) . '/..'
    ) . $paths;

    return [
        // the friendly display name for a payment gateway should be
        // defined here for backwards compatibility
        'FriendlyName' => [
            'Type' => 'System',
            'Value' => 'Moota Payment Gateway',
        ],

        /** string $apiKey */
        'mootaApiKey' => [
            'FriendlyName' => 'API Key',
            'Type' => 'text',
            'Size' => '50',
            'Default' => '',
            'Description' => 'Enter your Moota account API Key here',
        ],

        /** integer $apiTimeout */
        'mootaApiTimeout' => [
            'FriendlyName' => 'API Access Timeout',
            'Type' => 'text',
            'Size' => '2',
            'Default' => '30',
            'Description' => 'Timeout, in second',
        ],

        /** string $serverAddress */
        'mootaEnvironment' => [
            'FriendlyName' => 'Moota SDK Environment',
            'Type' => 'dropdown',
            'Size' => '255',
            'Options' => ['production', 'testing'],
            'Default' => 'production',
            'Description' => 'Only change when asked by Moota',
        ],

        /** string $serverAddress */
        'mootaServerAddress' => [
            'FriendlyName' => 'Moota Server Address',
            'Type' => 'text',
            'Size' => '255',
            'Default' => 'app.moota.co',
            'Description' => 'Only change when asked by Moota',
        ],

        'mootaPushCallbackUri' => [
            'FriendlyName' => 'Moota Server Address',
            'Type' => 'text',
            'Size' => '255',
            'Default' => "http://{$baseUri}/modules/gateways/callback/moota.php",
            'Description' => 'Masuk halaman edit bank di moota &gt; tab notifikasi &gt; edit "API Push Notif" &gt; lalu masukkan url ini',
        ],
    ];
}

/**
 * Payment link.
 *
 * Required by third party payment gateway modules only.
 *
 * Defines the HTML output displayed on an invoice. Typically consists of an
 * HTML form that will take the user to the payment gateway endpoint.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see https://developers.whmcs.com/payment-gateways/third-party-gateway/
 *
 * @return string
 */
function moota_link($params)
{
    return 'Not implemented yet';
}

/**
 * Refund transaction.
 *
 * Called when a refund is requested for a previously successful transaction.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see https://developers.whmcs.com/payment-gateways/refunds/
 *
 * @return array Transaction response status
 */
function moota_refund($params)
{
    return [];
}

/**
 * Cancel subscription.
 *
 * If the payment gateway creates subscriptions and stores the subscription
 * ID in tblhosting.subscriptionid, this function is called upon cancellation
 * or request by an admin user.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see https://developers.whmcs.com/payment-gateways/subscription-management/
 *
 * @return array Transaction response status
 */
function moota_cancelSubscription($params)
{
    return [];
}
