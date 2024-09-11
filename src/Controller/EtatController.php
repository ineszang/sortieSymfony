<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Repository\EtatRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EtatController extends AbstractController
{
    #[Route('/etat', name: 'app_etat')]
    public function index(): Response
    {
        return $this->render('etat/index.html.twig', [
            'controller_name' => 'EtatController',
        ]);
    }

    #[Route('/etatlist', name: 'list')]
    public function all(EtatRepository $etatRepository): Response
    {
        $etats = $etatRepository->findAll();
        return $this->render('', [
            'title' => 'Liste de sites',
            'etat' => $etats
        ]);
    }
}
