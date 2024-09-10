<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SortiesController extends AbstractController
{
    #[Route('/sorties', name: 'app_sorties')]
    public function index(): Response
    {
        return $this->render('sorties/creerSortie.twig', [
            'controller_name' => 'SortiesController',
        ]);
    }


    #[Route(['/allSorties'], name: 'app_home')]
    public function indexSorties(): Response
    {
        $utilisateur = "Melaine F.";

        return $this->render('sorties/allSorties.html.twig', [
            'utilisateur' => $utilisateur,
        ]);
    }
}
