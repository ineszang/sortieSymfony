<?php
// src/Controller/RegistrationController.php
namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Site;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new Participant();

        $repository = $entityManager->getRepository(Site::class);
        $sites = $repository->findAll();
        
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

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

            // Rediriger vers la page d'accueil après l'inscription
            return $this->redirectToRoute('app_home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'sites' => $sites
        ]);
    }
}