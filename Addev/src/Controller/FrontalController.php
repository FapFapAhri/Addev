<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
class FrontalController extends Controller{

/**
*@Route("/", name="home")
*/
    public function index(){

       return $this->render('index.html.twig');
    }
}