<?php

namespace LaPoiz\TableBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('LaPoizTableBundle:Default:index.html.twig');
    }
}
