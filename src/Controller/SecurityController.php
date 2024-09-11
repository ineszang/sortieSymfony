<?php

namespace App\Controller;

use App\Entity\Participant;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Doctrine\ORM\EntityManagerInterface;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils,
         EntityManagerInterface $entityManager): Response
    {
        // Récupération des paramètres 
        $form = $request->get('form');
        /*$paramUsername = $request->request->get('pseudo');
        $paramPassword = $request->request->get('mot_de_passe');*/

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

       /* $repository = $entityManager->getRepository(Participant::class);
        $userDB = $repository->findByUser($paramUsername, $paramPassword);*/

       // var_dump($paramUsername);
       var_dump($form);

        /*if ( $userDB) {
            return $this->redirectToRoute('app_home', [
                'user' => $userDB
            ]);
        }*/

        
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            //'pseudo' => $paramUsername
           // 'mot_de_passe' => $paramPassword
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
