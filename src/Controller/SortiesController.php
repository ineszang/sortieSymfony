<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Form\CreationSortieType;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function PHPUnit\Framework\throwException;

class SortiesController extends AbstractController
{
    #[Route("/sortieAjoutForm", name: "sortie_form")]
    public function create(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, ValidatorInterface $validator): Response
    {
        //mock pour la connexion
        $violetteSiteId = 1;
        $violetteId = 1;
        $violette = $entityManager->getRepository(Participant::class)->find($violetteId);
        $site = $entityManager->getRepository(Site::class)->find($violetteSiteId);

        if (!$violette || !$site) {
            throw $this->createNotFoundException('Utilisateur ou site non trouvé.');
        }

        $sortie = new Sortie();
        $sortieForm = $this->createForm(CreationSortieType::class, $sortie);

        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $imageFile = $sortieForm->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    throw $e;
                }

                $sortie->setUrlPhoto('/uploads/images/'.$newFilename);
            }

            $etatCree = $entityManager->getRepository(Etat::class)->findOneBy(['libelle' => 'créée']);
            if (!$etatCree) {
                throw $this->createNotFoundException("L'état 'créée' n'existe pas.");
            }
            $sortie->setEtat($etatCree);
            $sortie->setOrganisateur($violette);
            $sortie->setSiteOrganisateur($site);

            // Persister la sortie en base de données
            $entityManager->persist($sortie);
            $entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        $error = $validator->validate($sortie);
        dump($error);

        return $this->render("sorties/creerSortie.html.twig", [
            'title' => 'Formulaire d\'ajout de sorties',
            "sortieForm" => $sortieForm,
            'user' => $violette,
            'error' => $error
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



        //
        //chercher dans la bdd
        //$sites = $siteRepository->findBySearchParameters();




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
