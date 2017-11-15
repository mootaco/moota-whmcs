<?php namespace Moota\WHMCS;

use Moota\SDK\Contracts\Push\FetchesOrders;

class InvoiceFetcher implements FetchesOrders
{
    /** @var \WHMCS\Database\Capsule */
    protected $db;

    /**
     * @param \WHMCS\Database\Capsule $db
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    public function fetch(array $inflowAmounts)
    {
        $invoices = $this->db->table('tblinvoices');

        if (!empty($inflowAmounts) && count($inflowAmounts) > 0) {
            $invoices = $invoices->whereIn('total', $inflowAmounts);
        }

        $invoices = $invoices->where('status', 'Unpaid')->get();

        return $invoices;
    }
}
