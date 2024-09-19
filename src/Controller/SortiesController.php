<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Participant;
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
use Symfony\Component\HttpFoundation\JsonResponse;
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
            $etat = $entityManager->getRepository(Etat::class)->findOneBy(['libelle' => 'créée']);
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

    #[Route("/sortieAnnuler/{id}", name: "app_annuler_sortie", methods: ["POST"])]
    public function annulerSortie(
        int $id,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        $sortie = $entityManager->getRepository(Sortie::class)->find($id);

        if (!$sortie) {
            throw $this->createNotFoundException('Sortie non trouvée.');
        }

        $now = new \DateTime();
        if ($sortie->getDateHeureDebut() < $now) {
            $this->addFlash('error', 'La sortie ne peut plus être annulée car la date de début est déjà passée.');
            return $this->redirectToRoute('app_allSorties');
        }

        if ($this->isCsrfTokenValid('annuler' . $sortie->getId(), $request->request->get('_token'))) {
            $etatAnnule = $entityManager->getRepository(Etat::class)->find(6);
            if (!$etatAnnule) {
                throw $this->createNotFoundException('État "annulé" non trouvé.');
            }
            $sortie->setEtat($etatAnnule);
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'La sortie a été annulée avec succès.');
        } else {
            $this->addFlash('error', 'Échec de la validation du token CSRF.');
        }

        return $this->redirectToRoute('app_allSorties');
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
        $mesSortiesCreer = $sortiesService->listMySortieCree($sortieRepository, $idUtilisateur);

        $sortiesSubscription = $sortiesService->listDesinscription($sortieRepository,$idUtilisateur);

        $sortiesParticipants = [];
        foreach ($sorties as $sortie) {
            $nbParticipants = $sortieRepository->getTotalParticipant($sortie->getId());
            $sortiesParticipants[$sortie->getId()] = $nbParticipants;
        }



        $sortiesModifiable = $sortieRepository->findBySearchParameters(null, null, null, null, true, false, false, false, $participant->getId(), "Créée");

        $sortiesAnnulable = array_merge(
            $sortieRepository->findBySearchParameters(null, null, null, null, true, false, false, false, $participant->getId(), "Créée"),
            $sortieRepository->findBySearchParameters(null, null, null, null, true, false, false, false, $participant->getId(), "Ouverte")
        );

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
            'sortiesSubscription' => $sortiesSubscription,
            'mesSortiesCreer' => $mesSortiesCreer,
            'sortiesModifiable' => $sortiesModifiable,
            'sortiesAnnulable' => $sortiesAnnulable,
            'sortiesParticipants' => $sortiesParticipants

        ]);
    }

    #[Route("/desinscrire-participants", name: "app_desinscrire_participants", methods: ["POST"])]
    public function desinscrireParticipants(
        Request $request,
        EntityManagerInterface $entityManager,
        SortieRepository $sortieRepository
    ): Response {
        $postData = $request->request->all();

        $sortieId = $postData['sortie_id'] ?? null;
        $participantIds = $postData['participants'] ?? [];

        $sortie = $sortieRepository->find($sortieId);
        if (!$sortie) {
            $this->addFlash('error', 'Sortie non trouvée.');
            return $this->redirectToRoute('app_allSorties');
        }

        if (!empty($participantIds)) {
            foreach ($participantIds as $id) {
                $participant = $entityManager->getRepository(Participant::class)->find($id);
                if ($participant) {
                    $sortie->removeParticipant($participant);
                }
            }
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Les participants sélectionnés ont été désinscrits avec succès.');
        } else {
            $this->addFlash('error', 'Aucun participant sélectionné pour la désinscription.');
        }

        return $this->redirectToRoute('app_details_sorties' . $sortieId );
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
        $nbDeParticipants = $sortieRepository->getTotalParticipant($id);

        $nbParticipants = $sortieRepository->getTotalParticipant($sortie->getId());
        $placeRestante =  $sortie->getNbIncriptionsMax() - $nbParticipants;
        $maSortie = false;

        if ($sortie->getOrganisateur()->getId() === $participant->getId()) {
            $maSortie = true;
        }

        $isSubscrible = $sortie->getEtat() === "Ouvert" && $nbDeParticipants < $sortie->getNbIncriptionsMax() && $sortie->getOrganisateur()->getId() !== $participant->getId();

        return $this->render('sorties/detailsSortie.html.twig', [
            'utilisateur' => $utilisateur,
            'sortie' => $sortie,
            'isSubscrible' => $isSubscrible,
            'participants' => $participants,
            'maSortie' => $maSortie,
            'placeRestante' => $placeRestante
        ]);
    }



    #[Route(['/inscription/{name}/{idSortie}'], name: 'app_inscription')]
    #[isGranted("ROLE_USER")]
    public function indexInscription(
        string $name,
        int $idSortie,
        ParticipantRepository $participantRepository,
        SortieRepository $sortieRepository
    ): Response {
        // Récupérer l'utilisateur et son id
        $utilisateur = $this->getUser();
        $participant = $participantRepository->findOneByPseudo($utilisateur->getUserIdentifier());
        $idUtilisateur = $participant->getId();
        $sortie = $sortieRepository->findOneBySomeField($idSortie);
        $chevauchement = false;
        // Vérifier si un chevauchement est détecté
        if ($name === "s'inscrire") {
            // Ajouter l'inscription si pas de chevauchement
            $sortieRepository->addInscription($idUtilisateur, $idSortie);
            if($sortieRepository->getTotalParticipant($idSortie) === $sortie->getNbIncriptionsMax()) {
                try {
                    $sortieRepository->changeState($idSortie, "Clôturée");
                } catch (\Exception $e) {
                }
            }
            $listchevauchement = $sortieRepository->getOverlappingSorties($idSortie, $idUtilisateur);
            if (!empty($listchevauchement)) {

                // Rediriger vers une route qui affichera un message d'avertissement
                return $this->redirectToRoute('app_inscription_chevauchement', [
                    'idSortie' => $idSortie,
                    'idUtilisateur' => $idUtilisateur,
                ]);
            }

        } else if ($name === "se desinscrire") {
            if($sortieRepository->getTotalParticipant($idSortie) === $sortie->getNbIncriptionsMax() && $sortie->getDateClotureInscription() > new \DateTime()) {
                try {
                    $sortieRepository->changeState($idSortie, "Ouverte");
                } catch (\Exception $e) {
                }
            }
            $sortieRepository->removeInscription($idUtilisateur, $idSortie);
        }

        // Rediriger vers la liste des sorties
        return $this->redirectToRoute('app_allSorties');
    }

    #[Route('/inscription-chevauchement/{idSortie}/{idUtilisateur}', name: 'app_inscription_chevauchement')]
    #[isGranted("ROLE_USER")]
    public function inscriptionChevauchement(int $idSortie, int $idUtilisateur,SortieRepository $sortieRepository): Response
    {

        $listchevauchement = $sortieRepository->getOverlappingSorties($idSortie, $idUtilisateur);

        // Préparer la chaîne HTML pour la pop-up
        $chevauchementsHtml = '';
        foreach ($listchevauchement as $sortie) {
            $dateDebut = $sortie->getDateHeureDebut()->format('Y-m-d H:i');
            $dateFin = $sortie->getDateHeureFin()->format('Y-m-d H:i');
            $organisateur = $sortie->getOrganisateur()->getNom() . ' ' . $sortie->getOrganisateur()->getPrenom();
            $chevauchementsHtml .= "<p>{$sortie->getNomSortie()} - Début : {$dateDebut} - Fin : {$dateFin} - Organisateur : {$organisateur}</p>";
        }
        // Afficher un template où la pop-up sera affichée
        return $this->render('sorties/chevauchement.html.twig', [
            'idSortie' => $idSortie,
            'idUtilisateur' => $idUtilisateur,
            'chevauchementsHtml' => $chevauchementsHtml
        ]);
    }



    #[Route(['/app_publier_sortie/{idSortie}'], name: 'app_publier_sortie')]
    #[isGranted("ROLE_USER")]
    public function indexPublier($idSortie, SortieRepository $sortieRepository ): Response
    {
        $sortieRepository->changeState($idSortie, "Ouverte");



        return $this->redirectToRoute('app_allSorties');
    }
}


