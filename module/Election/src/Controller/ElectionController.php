<?php

namespace Election\Controller;

use Election\Model\ElectionTable;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ElectionController extends AbstractActionController
{

    // Add this property:
    private $table;

    // Add this constructor:
    public function __construct(ElectionTable $table)
    {
        $this->table = $table;
    }

    public function indexAction()
    {
        return new ViewModel([
            'albums' => $this->table->fetchAll(),
        ]);
    }

    public function addAction()
    {
    }

    public function editAction()
    {
    }

    public function deleteAction()
    {
    }

}
