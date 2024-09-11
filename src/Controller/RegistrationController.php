<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Site;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new Participant();
        $site = new Site();

        $repository = $entityManager->getRepository(Site::class);
        $sites = $repository->findAll();
        
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('mot_de_passe')->getData();
            $siteId = $request->get('site_id');
            $site = $repository->findOneBySomeField($siteId);

            dump($site);


            // encode the plain password

            //$hashedPassword = $userPasswordHasher->hashPassword($user, $plainPassword);
            $user->setmotDePasse($plainPassword);
            $user->setSite($site);

            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_sorties');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
            'sites' => $sites
        ]);
    }
}
