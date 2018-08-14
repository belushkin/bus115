<?php

namespace Bus115\Upload;

use Silex\Application;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class Manager
{

    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function getForm()
    {
//        return $this->app['form.factory']->createBuilder(FormType::class, $data)
//            ->add('name')
//            ->add('email')
//            ->add('billing_plan', ChoiceType::class, array(
//                'choices' => array('free' => 1, 'small business' => 2, 'corporate' => 3),
//                'expanded' => true,
//            ))
//            ->add('submit', SubmitType::class, [
//                'label' => 'Save',
//            ])
//            ->getForm();

        return $this->app['form.factory']
            ->createBuilder(FormType::class, [])
            ->add('FileUpload', 'file')
            ->getForm();
    }

}
