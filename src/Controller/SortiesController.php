<?php

namespace App\Controller;

use App\Repository\SortieRepository;
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


    #[Route(['/allSorties'], name: 'app_allSorties')]
    public function indexSorties(SortieRepository $sortieRepository): Response
    {
        //TODO : trouver le user en ligne
        $utilisateur = "Melaine F.";


        $sorties = $sortieRepository->findPublishedSorties();


        var_dump("test");


        return $this->render('sorties/allSorties.html.twig', [
            'utilisateur' => $utilisateur,
            'sorties' => $sorties,
        ]);
    }
}
