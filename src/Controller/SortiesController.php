<?php

namespace App\Controller;

use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    public function indexSorties(SortieRepository $sortieRepository, SiteRepository $siteRepository, Request $request): Response
    {
        //TODO : trouver le user en ligne
        $utilisateur = "Melaine F.";

        $site = $request->query->get('site');
        $recherche = $request->query->get('recherche');
        $dateStart = $request->query->get('dateStart');
        $dateEnd = $request->query->get('dateEnd');
        $mesSorties = $request->query->get('mesSorties');
        $mesInscriptions = $request->query->get('mesInscriptions');
        $pasMesInscriptions = $request->query->get('pasMesInscriptions');
        $sortiesFinies = $request->query->get('sortiesFinies');

        $sorties = $sortieRepository->findPublishedSorties();
        $sites = $siteRepository->findAll();



        //chercher dans la bdd
        $sites = $siteRepository->findBySearchParameters();




        return $this->render('sorties/allSorties.html.twig', [
            'utilisateur' => $utilisateur,
            'sorties' => $sorties,
            'sites' => $sites,
            'site' => $site,
            'recherche' => $recherche,
            'dateStart' => $dateStart,
            'dateEnd' => $dateEnd,
            'mesSorties' => $mesSorties,
            'mesInscriptions' => $mesInscriptions,
            'pasMesInscriptions' => $pasMesInscriptions,
            'sortiesFinies' => $sortiesFinies,
        ]);
    }


    #[Route(['/detailsSorties/{id}'], name: 'app_details_sorties')]
    public function indexDetailsSortie(int $id,SortieRepository $sortieRepository): Response
    {
        //TODO : trouver le user en ligne
        $utilisateur = "Melaine F.";

        $sortie = $sortieRepository->findOneBySomeField($id);





        return $this->render('sorties/detailsSortie.html.twig', [
            'utilisateur' => $utilisateur,
            'sortie' => $sortie,

        ]);
    }
}
