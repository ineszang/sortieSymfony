<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Site;
use App\Form\ProfilFormType;
use App\Repository\ParticipantRepository;
use App\Repository\SiteRepository;
use App\Service\FileUploaderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'app_profil')]
    #[isGranted("ROLE_USER")]
    public function index(Request $request,ParticipantRepository $participantRepository, Security $security,
        EntityManagerInterface $entityManager, FileUploaderService $fileUploaderService, SiteRepository $siteRepository,
                          UserPasswordHasherInterface $userPasswordHasher): Response
    {
        //Chargement de la liste des sites
        $sites = $entityManager->getRepository(Site::class)->findAll();

        //Récupération des informations de l'utilisateur connecté
        $this->security = $security;
        $user = $this->security->getUser();
        //Récupérer le participant connecté
        $participant = $participantRepository->findOneByPseudo($user->getUserIdentifier());
        $motDePasse = $participant->getMotDePasse();
        $photoProfile = $participant->getUrlPhoto();


        //Création du formulaire
        $form = $this->createForm(ProfilFormType::class, $user);
        $form->handleRequest($request);


        //Enregistrement des modifications du profil
        if ($form->isSubmitted()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = $fileUploaderService->upload($imageFile);
                $participant->setUrlPhoto('uploads/images/' . $newFilename);
            }
            //On récupère le mot de passe si des champs sont renseignés
            if($form->get('mot_de_passe')->getData() != null) {
                $plainPassword = $form->get('mot_de_passe')->getData();
                $hashedPassword = $userPasswordHasher->hashPassword($user, $plainPassword);
                $participant->setMotDePasse($hashedPassword);
            } else {
                $participant->setMotDePasse($motDePasse);
            }

            //on renseigne le nouveau site
            $siteId = $request->get('site_id');
            $site = $siteRepository->find($siteId);
            $participant->setSite($site);

            $entityManager->persist($participant);
            $entityManager->flush();

            return $this->redirectToRoute('app_profil');
        }

        return $this->render('profil/index.html.twig', [
            'ProfilForm' => $form->createView(),
            'sites' => $sites,
            'user' => $user,
            'photoProfile' => $photoProfile
        ]);
    }

    #[Route('/profil/{id}', name: 'app_profil_show')]
    public function show(Participant $participant, EntityManagerInterface $entityManager, 
        $id): Response
    {
        $user = $entityManager->getRepository(Participant::class)->find($id);

        return $this->render('profil/show.html.twig', [
            'user' => $user,
        ]);
    }
    #[Route('/adminView', name: 'app_admin_view')]
    #[isGranted("ROLE_ADMIN")]
    public function users(Request $request, ParticipantRepository $participantRepository,
                          EntityManagerInterface $entityManager): Response
    {
        // Récupérer tous les participants avec les inactifs en premier
        $participants = $participantRepository->findBy([], ['actif' => 'ASC']);

        return $this->render('profil/adminView.html.twig', [
            'participants' => $participants
        ]);
    }
    #[Route('/admin/toggle-actif/{id}', name: 'app_admin_toggle_actif')]
    #[isGranted("ROLE_ADMIN")]
    public function toggleActif(int $id, ParticipantRepository $participantRepository,
                                EntityManagerInterface $entityManager): Response
    {
        // Récupérer le participant
        $participant = $participantRepository->find($id);

        if ($participant) {
            // Inverser l'état actif/inactif
            $participant->setActif(!$participant->isActif());

            // Sauvegarder les modifications
            $entityManager->flush();
        }

        // Redirection après action
        return $this->redirectToRoute('app_admin_view');
    }
    #[Route('/admin/delete-participant/{id}', name: 'app_admin_delete_participant')]
    #[isGranted("ROLE_ADMIN")]
    public function deleteParticipant(int $id, ParticipantRepository $participantRepository,
                                      EntityManagerInterface $entityManager): Response
    {
        // Récupérer le participant
        $participant = $participantRepository->find($id);

        if ($participant) {
            // Supprimer le participant
            $entityManager->remove($participant);
            $entityManager->flush();
        }

        // Redirection après action
        return $this->redirectToRoute('app_admin_view');
    }

}
