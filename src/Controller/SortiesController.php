<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Form\CreationSortieType;
use App\Repository\ParticipantRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use App\Service\FileUploaderService;
use App\Service\SortiesService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SortiesController extends AbstractController
{
    #[Route("/sortieAjoutForm", name: "sortie_form")]
    #[isGranted("ROLE_USER")]
    public function create(Request $request, ParticipantRepository $participantRepository, EntityManagerInterface $entityManager, FileUploaderService $fileUploaderService,
                           SluggerInterface $slugger, Security $security, ValidatorInterface $validator, VilleRepository $villeRepository): Response
    {
        $sortie = new Sortie();

        $user = $security->getUser();
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        $participant = $participantRepository->findOneByPseudo($user->getUserIdentifier());
        if (!$participant) {
            throw $this->createNotFoundException('Participant non trouvé.');
        }

        $site = $participant->getSite();
        if (!$site) {
            throw $this->createNotFoundException('Site non trouvé.');
        }

        $action = $request->get('action');

        if ($action === 'creer') {
            $etat = $entityManager->getRepository(Etat::class)->findOneBy(['libelle' => 'crée']);
            $sortie->setEtat($etat);
            if (!$etat) {
                throw $this->createNotFoundException("L'état n'existe pas.");
            }
        } elseif ($action === 'publier') {
            $etat = $entityManager->getRepository(Etat::class)->findOneBy(['libelle' => 'ouverte']);
            $sortie->setEtat($etat);
            if (!$etat) {
                throw $this->createNotFoundException("L'état n'existe pas.");
            }
        }
        $sortie->setOrganisateur($participant);
        $sortie->setSiteOrganisateur($site);

        $sortieForm = $this->createForm(CreationSortieType::class, $sortie, [
            'organisateur_pseudo' => $participant->getPseudo(),
            'site_organisateur_nom' => $site->getNomSite()
        ]);

        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $imageFile = $sortieForm->get('image')->getData();
            if ($imageFile) {
                $newFilename = $fileUploaderService->upload($imageFile);

                $sortie->setUrlPhoto('/uploads/images/' . $newFilename);
            }

            $entityManager->persist($sortie);
            $entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        $villes = $villeRepository->findAll();
        $errors = $validator->validate($sortie);
        dump($errors);

        return $this->render("sorties/creerSortie.html.twig", [
            'title' => 'Formulaire d\'ajout de sorties',
            "sortieForm" => $sortieForm,
            'user' => $user,
            'errors' => $errors,
            'villes' => $villes
        ]);
    }

    #[Route("/sortieUpdateForm/{id}", name: "app_modifier_sortie")]
    #[isGranted("ROLE_USER")]
    public function update(
        int $id,
        Request $request,
        ParticipantRepository $participantRepository,
        EntityManagerInterface $entityManager,
        FileUploaderService $fileUploaderService,
        Security $security,
        ValidatorInterface $validator,
        VilleRepository $villeRepository
    ): Response {
        $sortie = $entityManager->getRepository(Sortie::class)->find($id);

        $photoPrevisualisation = $sortie->getUrlPhoto();

        if (!$sortie) {
            throw $this->createNotFoundException('Sortie non trouvée.');
        }

        $user = $security->getUser();
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        $participant = $participantRepository->findOneByPseudo($user->getUserIdentifier());
        if (!$participant) {
            throw $this->createNotFoundException('Participant non trouvé.');
        }

        $site = $participant->getSite();
        if (!$site) {
            throw $this->createNotFoundException('Site non trouvé.');
        }

        $lieuNomComplet = $sortie->getLieu();

        $action = $request->get('action');

        if ($action === 'modifier') {
            $etat = $entityManager->getRepository(Etat::class)->findOneBy(['libelle' => 'modifié']);
            $sortie->setEtat($etat);
            if (!$etat) {
                throw $this->createNotFoundException("L'état n'existe pas.");
            }
        } elseif ($action === 'publier') {
            $etat = $entityManager->getRepository(Etat::class)->findOneBy(['libelle' => 'ouverte']);
            $sortie->setEtat($etat);
            if (!$etat) {
                throw $this->createNotFoundException("L'état n'existe pas.");
            }
        }

        $sortieForm = $this->createForm(CreationSortieType::class, $sortie, [
            'organisateur_pseudo' => $participant->getPseudo(),
            'site_organisateur_nom' => $site->getNomSite()
        ]);

        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $imageFile = $sortieForm->get('image')->getData();
            if ($imageFile) {
                $newFilename = $fileUploaderService->upload($imageFile);
                $sortie->setUrlPhoto('/uploads/images/' . $newFilename);
            }

            $entityManager->persist($sortie);
            $entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        $villes = $villeRepository->findAll();
        $errors = $validator->validate($sortie);

        return $this->render("sorties/modifierSortie.html.twig", [
            'title' => 'Formulaire de modification de sorties',
            "sortieForm" => $sortieForm,
            'user' => $user,
            'errors' => $errors,
            'villes' => $villes,
            'lieuNomComplet' => $lieuNomComplet,
            'photoPrevisualisation' => $photoPrevisualisation
        ]);
    }


    #[Route(['/allSorties'], name: 'app_allSorties')]
    #[isGranted("ROLE_USER")]
    public function indexSorties(ParticipantRepository $p,SortieRepository $sortieRepository, SiteRepository $siteRepository, Request $request, SortiesService $sortiesService): Response
    {


        $site = $request->query->get('site');
        $recherche = $request->query->get('recherche');
        $dateStart = $request->query->get('dateStart');
        $dateEnd = $request->query->get('dateEnd');
        $mesSorties = $request->query->get('mesSorties') === '1';
        $mesInscriptions = $request->query->get('mesInscriptions') === '1';
        $pasMesInscriptions = $request->query->get('pasMesInscriptions') === '1';
        $sortiesFinies = $request->query->get('sortiesFinies') === '1';

        $sites = $siteRepository->findAll();

        $utilisateur = $this->getUser();

        if ($utilisateur) {

            $username = $utilisateur->getUserIdentifier();
        } else {
            $username = "no user";
        }

        $participant = $p->findOneByPseudo($utilisateur->getUserIdentifier());
        $idUtilisateur = $participant->getId();
        //chercher dans la bdd
        $sorties = $sortieRepository->findBySearchParameters($site, $recherche, $dateStart, $dateEnd, $mesSorties, $mesInscriptions, $pasMesInscriptions, $sortiesFinies, $participant->getId());

        $sortiesSubscrible = $sortiesService->listInscription($sortieRepository,$idUtilisateur);

        $sortiesSubscription = $sortieRepository->findBySearchParameters(null, null, null, null, false, true, false, false, $participant->getId());


        return $this->render('sorties/allSorties.html.twig', [
            'utilisateur' => $username,
            'sorties' => $sorties,
            'sites' => $sites,
            'site_selected' => $site,
            'recherche' => $recherche,
            'dateStart' => $dateStart,
            'dateEnd' => $dateEnd,
            'mesSorties' => $mesSorties,
            'mesInscriptions' => $mesInscriptions,
            'pasMesInscriptions' => $pasMesInscriptions,
            'sortiesFinies' => $sortiesFinies,
            'idUtilisateur' => $idUtilisateur,
            'sortiesSubscrible' => $sortiesSubscrible,
            'sortiesSubscription' => $sortiesSubscription
        ]);
    }


    #[Route('/detailsSorties/{id}', name: 'app_details_sorties')]
    #[isGranted("ROLE_USER")]
    public function indexDetailsSortie(int $id, SortieRepository $sortieRepository, ParticipantRepository $participantRepository): Response
    {
        $sortie = $sortieRepository->find($id);

        if (!$sortie) {
            throw $this->createNotFoundException('Sortie non trouvée');
        }

        $utilisateur = $this->getUser();
        $participant = $participantRepository->findOneByPseudo($utilisateur->getUserIdentifier());

        $participants = $sortieRepository->findParticipantsBySortieId($id);
        $nbDeParticipants = count($participants);

        $isSubscrible = $sortie->getEtat() === "Ouvert" && $nbDeParticipants < $sortie->getNbIncriptionsMax() && $sortie->getOrganisateur()->getId() !== $participant->getId();

        return $this->render('sorties/detailsSortie.html.twig', [
            'utilisateur' => $utilisateur,
            'sortie' => $sortie,
            'isSubscrible' => $isSubscrible,
            'participants' => $participants,
        ]);
    }



    #[Route(['/inscription/{name}/{idSortie}'], name: 'app_inscription')]
    #[isGranted("ROLE_USER")]
    public function indexInscription(string $name, $idSortie, ParticipantRepository $participantRepository, SortieRepository $sortieRepository ): Response
    {

        $utilisateur = $this->getUser();
        $participant = $participantRepository->findOneByPseudo($utilisateur->getUserIdentifier());
        $idUtilisateur = $participant->getId();
        var_dump($name);
        if($name === "s'inscrire") {
            $sortieRepository->addInscription($idUtilisateur, $idSortie);
        } else if ($name === "se desinscrire") {
            $sortieRepository->removeInscription($idUtilisateur, $idSortie);
        }


        return $this->redirectToRoute('app_allSorties');
    }
}
