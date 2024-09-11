<?php

namespace App\Controller;

use App\Repository\LieuRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LieuController extends AbstractController
{
    private LieuRepository $lieuRepository;

    public function __construct(LieuRepository $lieuRepository)
    {
        $this->lieuRepository = $lieuRepository;
    }

    #[Route('/lieu', name: 'app_lieu')]
    public function index(): Response
    {
        return $this->render('lieu/index.html.twig', [
            'controller_name' => 'LieuController',
        ]);
    }

    #[Route('/rechercher-lieu', name: 'rechercher_lieu', methods: ['GET'])]
    public function searchLieu(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');
        $lieux = $this->lieuRepository->searchLieux($query);

        $results = [];
        foreach ($lieux as $lieu) {
            $results[] = [
                'id' => $lieu->getId(),
                'nomLieu' => $lieu->getNomLieu(),
                'rue' => $lieu->getRue(),
                'ville' => [
                    'nomVille' => $lieu->getVille()->getNomVille(),
                    'codePostal' => $lieu->getVille()->getCodePostal(),
                ],
            ];
        }
        return new JsonResponse($results);
    }
}
