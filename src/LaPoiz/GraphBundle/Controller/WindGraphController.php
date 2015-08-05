<?php

namespace LaPoiz\GraphBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class WindGraphController extends Controller
{
    public function test1Action()
    {
        return $this->render('LaPoizGraphBundle:Wind:test1.html.twig');
    }
}
