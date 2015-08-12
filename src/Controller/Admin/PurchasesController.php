<?php
namespace App\Controller\Admin;

use App\Controller\AppController;

class PurchasesController extends AppController
{
    public $paginate = [
        'contain' => [
            'Community' => [
                'fields' => ['id', 'name']
            ],
            'Product' => [
                'fields' => ['id', 'description', 'price']
            ],
            'Refunder' => [
                'fields' => ['id', 'name']
            ],
            'User' => [
                'fields' => ['id', 'name', 'email', 'phone', 'title', 'organization']
            ]
        ],
        'fields' => ['id', 'created', 'refunded'],
        'limit' => 50,
        'order' => [
            'Purchase.created' => 'DESC'
        ]
    ];

    public function index()
    {
        $this->set([
            'titleForLayout' => 'Payment Records',
            'purchases' => $this->paginate()
        ]);
    }
}
