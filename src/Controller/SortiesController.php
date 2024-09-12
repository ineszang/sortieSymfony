<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Form\CreationSortieType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use function PHPUnit\Framework\throwException;

class SortiesController extends AbstractController
{
    #[Route("/sortieAjoutForm", name: "sortie_form")]
    public function create(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
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

        if ($sortieForm->isSubmitted()) {
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

        return $this->render("sorties/creerSortie.html.twig", [
            'title' => 'Formulaire d\'ajout de sorties',
            "sortieForm" => $sortieForm->createView(),
            'user' => $violette
        ]);
    }
}
