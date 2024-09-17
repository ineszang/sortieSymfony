<?php

namespace App\Controller;

use App\Repository\SiteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SiteController extends AbstractController
{
    #[Route('/site', name: 'app_site')]
    #[isGranted("ROLE_ADMIN")]
    public function index(): Response
    {
        return $this->render('site/index.html.twig', [
            'controller_name' => 'SiteController',
        ]);
    }
    #[Route('/sitelist', name: 'list')]
    #[isGranted("ROLE_ADMIN")]
    public function all(SiteRepository $siteRepository): Response
    {
        $sites = $siteRepository->findAll();
        return $this->render('', [
            'title' => 'Liste de sites',
            'sites' => $sites
        ]);
    }
}
