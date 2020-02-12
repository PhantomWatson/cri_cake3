<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;

/**
 * Invoices Controller
 *
 * @property \App\Model\Table\InvoicesTable $Invoices
 */
class InvoicesController extends AppController
{
    /**
     * Method for marking the passed purchase IDs as being billed to OCRA
     *
     * @return \Cake\Http\Response
     */
    public function markBilled()
    {
        $purchaseIds = $this->request->getData('purchaseIds');
        $purchasesTable = TableRegistry::get('Purchases');
        $successCount = 0;
        foreach ($purchaseIds as $purchaseId) {
            if (! $purchaseId) {
                continue;
            }

            // Confirm that this is an appropriate purchase
            $purchase = $purchasesTable->get($purchaseId);
            if ($purchase->source != 'ocra') {
                $msg = "Error: Cannot mark a non-OCRA charge (#$purchaseId) as billed";
                $this->Flash->error($msg);
                break;
            }

            // Skip any already-recorded invoices
            $count = $this->Invoices->find('all')
                ->where(['purchase_id' => $purchaseId])
                ->count();
            if ($count) {
                $msg = "Purchase #$purchaseId already marked as billed";
                $this->Flash->set($msg);
                continue;
            }

            // Save
            $invoice = $this->Invoices->newEntity([
                'purchase_id' => $purchaseId,
                'paid' => false,
            ]);
            if (! $this->Invoices->save($invoice)) {
                $msg = 'There was an error updating the database. Details: ' . $invoice->getErrors();
                $this->Flash->error($msg);
                break;
            }

            $successCount++;
        }

        if ($successCount) {
            $msg = $successCount . __n(' item', ' items', $successCount) . ' marked as billed';
            $this->Flash->success($msg);
        } else {
            $this->Flash->set('No items were marked as billed');
        }

        return $this->redirect($this->request->referer());
    }

    /**
     * Method for marking the passed invoice IDs as being paid
     *
     * @return \Cake\Http\Response
     */
    public function markPaid()
    {
        $invoiceIds = $this->request->getData('invoiceIds');
        $successCount = 0;
        foreach ($invoiceIds as $invoiceId) {
            if (! $invoiceId) {
                continue;
            }

            // Confirm that this invoice exists
            $invoice = $this->Invoices->get($invoiceId);

            // Skip any already-recorded payments
            if ($invoice->paid) {
                $msg = "Invoice #$invoiceId already marked as paid";
                $this->Flash->set($msg);
                continue;
            }

            // Save
            $this->Invoices->patchEntity($invoice, [
                'paid' => true,
            ]);
            if (! $this->Invoices->save($invoice)) {
                $msg = 'There was an error updating the database. Details: ' . $invoice->getErrors();
                $this->Flash->error($msg);
                break;
            }

            $successCount++;
        }

        if ($successCount) {
            $msg = $successCount . __n(' item', ' items', $successCount) . ' marked as paid';
            $this->Flash->success($msg);
        } else {
            $this->Flash->set('No items were marked as paid');
        }

        return $this->redirect($this->request->referer());
    }
}
