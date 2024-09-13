<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Repository\LieuRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
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

    #[Route('/creer-lieu', name: 'creer_lieu', methods: ['POST'])]
    public function creerLieu(Request $request, EntityManagerInterface $em, VilleRepository $villeRepo): JsonResponse
    {
        // Vérifiez que la requête est bien AJAX
        if ($request->isXmlHttpRequest()) {
            $data = json_decode($request->getContent(), true);

            // Recherchez la ville par son ID
            $ville = $villeRepo->find($data['villeId']);

            if ($ville) {
                // Créez le lieu et enregistrez en base de données
                $lieu = new Lieu();
                $lieu->setNomLieu($data['nomLieu']);
                $lieu->setRue($data['rue']);
                $lieu->setVille($ville);
                $lieu->setLatitude($data['latitude']);
                $lieu->setLongitude($data['longitude']);

                // Persistez le lieu dans la base de données
                $em->persist($lieu);
                $em->flush();

                // Réponse JSON avec les détails du lieu créé
                return $this->json([
                    'success' => true,
                    'lieuId' => $lieu->getId(),
                    'villeNom' => $lieu->getVille()->getNomVille(),
                    'villeCodePostal' => $lieu->getVille()->getCodePostal()
                ]);
            }
        }

        // En cas d'erreur, renvoyer une réponse JSON d'échec
        return $this->json(['success' => false], 400);
    }
}
