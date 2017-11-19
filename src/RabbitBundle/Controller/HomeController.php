<?php

namespace RabbitBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
    public function indexAction()
    {
        return $this->render('RabbitBundle:Home:index.html.twig');
    }

    public function downloadAction()
    {
        // Initialize
        $pageHoover = $this->container->get('rabbit.pagehoover');

        // Download page
        $page = 'https://www.example.com/';
        $pageHoover->downloadPage($page);

        // Return status
        $response = new Response();

        return $response->setContent('Page "'.$page.'" is downloaded !')->send();
    }
}
