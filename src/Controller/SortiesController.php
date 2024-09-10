<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\CreationSortieType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use function PHPUnit\Framework\throwException;

class SortiesController extends AbstractController
{
    #[Route("/sortieAjoutForm", name: "sortie_form")]
    public function create(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $sortie = new Sortie();
        $sortieForm = $this->createForm(CreationSortieType::class, $sortie);

        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $imageFile = $sortieForm->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    throwException($e);
                }

                $sortie->setUrlPhoto('/uploads/images/'.$newFilename);
            }

            $entityManager->persist($sortie);
            $entityManager->flush();

            return $this->redirectToRoute('list');
        }

        return $this->render("sorties/creerSortie.html.twig", [
            'title' => 'Formulaire d\'ajout de sorties',
            "sortieForm" => $sortieForm->createView()
        ]);
    }
}
