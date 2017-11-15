<?php namespace Moota\WHMCS;

use Moota\SDK\Contracts\Push\FullfilsOrder;

class InvoiceFullfiler implements FullfilsOrder
{
    protected $gatewayModuleName;
    protected $gatewayParams;

    public function __construct($gatewayModuleName, $gatewayParams)
    {
        $this->gatewayModuleName = $gatewayModuleName;
        $this->gatewayParams = $gatewayParams;
    }

    public function fullfil($order)
    {
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
         *
         * @return string success or error, this value is extremely unreliable
         */
        $saved = addInvoicePayment(
            $order['invoiceId'],
            $order['transactionId'],
            $order['mootaAmount'],
            0,
            $this->gatewayModuleName
        );

        /**
         * Log Transaction.
         *
         * Add an entry to the Gateway Log for debugging purposes.
         *
         * The debug data can be a string or an array.
         *
         * @param string $gatewayName        Display label
         * @param string|array $debugData    Data to log
         * @param string $transactionStatus  Status
         */
        logTransaction(
            $this->gatewayParams['name'],
            $order,
            'Payment applied'
        );

        // $saved value is extremely unreliable
        // also, at this point, as long as there is no error
        // we can consider as payment has been added successfully
        return true;
    }
}
