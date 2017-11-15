<?php namespace Moota\WHMCS;

use Moota\SDK\Contracts\Push\MatchesOrders;

class InvoiceMatcher implements MatchesOrders
{
    public function match(array $payments, array $orders)
    {
        $matchedPayments = [];

        $guardedPayments = $payments;

        // match whmcs invoice with moota transactions
        foreach ($orders as $order) {
            $transAmount = (float) $order->total;
            $tmpPayment = null;

            foreach ($guardedPayments as $idx => $payment) {
                if ( empty( $guardedPayments[ $idx ] ) ) continue;

                if ( ( (float) $payment['amount'] ) === $transAmount ) {
                    $tmpPayment = $payment;

                    $guardedPayments[ $idx ] = null;

                    break;
                }
            }

            $matchedPayments[]  = [
                // transactionId:
                //   { orderId }-{ moota:id }-{ moota:account_number }
                'transactionId' => implode('-', [
                    $order->id, $tmpPayment['id'], $tmpPayment['account_number']
                ]),
                'invoiceId' => $order->id,
                'mootaId' => $tmpPayment['id'],
                'mootaAccNo' => $tmpPayment['account_number'],
                'amount' => $tmpPayment['amount'],
                'mootaAmount' => $tmpPayment['amount'],
                'invoiceAmount' => (float) $order->total,
            ];
        }

        return $matchedPayments;
    }
}
