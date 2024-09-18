<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Repository\EtatRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EtatController extends AbstractController
{
    #[Route('/etat', name: 'app_etat')]
    #[isGranted("ROLE_USER")]
    public function index(): Response
    {
        return $this->render('etat/index.html.twig', [
            'controller_name' => 'EtatController',
        ]);
    }

    #[Route('/etatlist', name: 'list')]
    #[isGranted("ROLE_USER")]
    public function all(EtatRepository $etatRepository): Response
    {
        $etats = $etatRepository->findAll();
        return $this->render('', [
            'title' => 'Liste de sites',
            'etat' => $etats
        ]);
    }
}
