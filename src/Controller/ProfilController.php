<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Site;
use App\Form\ProfilFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;

class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'app_profil')]
    public function index(Security $security, 
        EntityManagerInterface $entityManager): Response
    {
        //Changement de la liste des sites
        $sites = $entityManager->getRepository(Site::class)->findAll();

        //Récupération des informations de l'utilisateur connecté
        $this->security = $security;
        $user = $this->security->getUser();

        //Création du formulaire
        $form = $this->createForm(ProfilFormType::class, $user);


        //Enregistrement des modifications du profil
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('mot_de_passe')->getData();
            $siteId = $request->get('site_id');
            $site = $repository->find($siteId);

            // Hacher le mot de passe
            $hashedPassword = $userPasswordHasher->hashPassword($user, $plainPassword);
            $user->setMotDePasse($hashedPassword);
            $user->setSite($site);

            $entityManager->persist($user);
            $entityManager->flush();
        }

        return $this->render('profil/index.html.twig', [
            'ProfilForm' => $form->createView(),
            'sites' => $sites,
            'user' => $user
        ]);
    }
}
