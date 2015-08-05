<?php

namespace LaPoiz\GraphBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('LaPoizGraphBundle:Default:index.html.twig', array('name' => $name));
    }
}
