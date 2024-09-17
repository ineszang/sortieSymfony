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

class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'app_profil')]
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
}
