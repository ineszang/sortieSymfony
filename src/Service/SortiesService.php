<?php

namespace App\Service;

use App\Repository\SortieRepository;

class SortiesService
{



    //vérifier si l'on peut s'inscrire
    //affichage du bouton s'inscrire

    //list des
    public function listInscription(SortieRepository $sortieRepository, int $userId)
    {
         $sorties = $sortieRepository->findBySearchParameters(null, null, null, null, false, false, true, false, $userId);
        //état = ouvert
        $sortiesOuvertes = [];
        foreach ($sorties as $sortie) {
            // Vérifiez si l'état de la sortie est "ouvert"
            if ($sortie->getEtat() && mb_strtolower($sortie->getEtat()->getLibelle()) === 'ouverte' && $sortie->getOrganisateur()->getId() !==$userId) {
                $sortiesOuvertes[] = $sortie;
            }
        }



        return $sortiesOuvertes;
    }


    //affichage du bouton ouvrir

    //se desister

    //cloturer

    //supprimer




}