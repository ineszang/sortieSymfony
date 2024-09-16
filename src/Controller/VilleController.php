<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\CreateVilleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class VilleController extends AbstractController
{
    #[Route("/creationVilleForm", name: "ville_form")]
    public function create(Request $request, EntityManagerInterface $entityManager, Security $security, ValidatorInterface $validator): Response
    {
        $ville = new Ville();

        $villeForm = $this->createForm(CreateVilleType::class, $ville);
        $villeForm->handleRequest($request);

        if ($villeForm->isSubmitted() && $villeForm->isValid()) {
            $entityManager->persist($ville);
            $entityManager->flush();

            return $this->redirectToRoute('app_home');
        }
        $errors = $validator->validate($ville);

        return $this->render("ville/creerVille.html.twig", [
            'title' => 'Formulaire d\'ajout de sorties',
            "villeForm" => $villeForm,
            'errors' => $errors,
        ]);
    }
}
