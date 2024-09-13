<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Site;
use App\Form\ProfilFormType;
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
    public function index(Request $request, Security $security,
        EntityManagerInterface $entityManager, FileUploaderService $fileUploaderService, SiteRepository $siteRepository, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        //Changement de la liste des sites
        $sites = $entityManager->getRepository(Site::class)->findAll();

        //Récupération des informations de l'utilisateur connecté
        $this->security = $security;
        $userConnecte = $this->security->getUser();

        //Création du formulaire
        $form = $this->createForm(ProfilFormType::class, $userConnecte);
        $form->handleRequest($request);

        //Enregistrement des modifications du profil
        if ($form->isSubmitted() && $form->get('mot_de_passe')->getData() != null) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = $fileUploaderService->upload($imageFile);

                $userConnecte->setUrlPhoto('/uploads/images/' . $newFilename);
            }
            /** @var string $plainPassword */
            $plainPassword = $form->get('mot_de_passe')->getData();
            $siteId = $request->get('site_id');
            $site = $siteRepository->findOneBySomeField($siteId);

            // Hachage du nouveau mot de passe
            $hashedPassword = $userPasswordHasher->hashPassword($userConnecte, $plainPassword);
            $userConnecte->setMotDePasse($hashedPassword);
            $userConnecte->setSite($site);

            $entityManager->persist($userConnecte);
            $entityManager->flush();

        } elseif ($form->isSubmitted()  && $form->get('mot_de_passe')->getData() == null) {

            $entityManager->persist($userConnecte);
            $entityManager->flush();
        }

        

        return $this->render('profil/index.html.twig', [
            'ProfilForm' => $form->createView(),
            'sites' => $sites,
            'user' => $userConnecte,
        ]);
    }
}
