<?php

namespace App\Service;

use App\Repository\SortieRepository;

class SortiesService
{



    //vérifier si l'on peut s'inscrire
    //affichage du bouton s'inscrire

    //list des
    public function listInscription(SortieRepository $sortieRepository, int $userId): array
    {
         $sorties = $sortieRepository->findBySearchParameters(null, null, null, null, false, false, true, false, $userId);

         //état = ouvert
        $sortiesOuvertes = [];
        foreach ($sorties as $sortie) {
            // Vérifiez si l'état de la sortie est "ouvert"
            $sortieNbParticipant = $sortieRepository->getTotalParticipant($sortie->getId());
            if ($sortie->getEtat() && mb_strtolower($sortie->getEtat()->getLibelle()) === 'ouverte' && $sortie->getOrganisateur()->getId() !==$userId && $sortieNbParticipant < $sortie->getNbIncriptionsMax()) {
                $sortiesOuvertes[] = $sortie;
            }
        }

        return $sortiesOuvertes;
    }

    public function listDesinscription(SortieRepository $sortieRepository, int $userId)
    {
        $sorties = $sortieRepository->findBySearchParameters(null, null, null, null, false, true, false, false, $userId);
        //état = ouvert
        $sortiesAQuitter = [];
        foreach ($sorties as $sortie) {
            // Vérifiez si l'état de la sortie est "ouvert"
            if ($sortie->getEtat() && (mb_strtolower($sortie->getEtat()->getLibelle()) === 'ouverte' || mb_strtolower($sortie->getEtat()->getLibelle()) === 'clôturée')  && $sortie->getOrganisateur()->getId() !==$userId) {
                $sortiesAQuitter[] = $sortie;
            }
        }



        return $sortiesAQuitter;
    }




    //affichage du bouton publier
    public function listMySortieCree(SortieRepository $sortieRepository, int $userId)
    {
        $sorties = $sortieRepository->findBySearchParameters(null, null, null, null, true, false, false, false, $userId);

        $sortiesCréées = [];
        foreach ($sorties as $sortie) {
            // Vérifiez si l'état de la sortie est "ouvert"
            if ($sortie->getEtat() && mb_strtolower($sortie->getEtat()->getLibelle()) === 'créée' && $sortie->getOrganisateur()->getId()    ===$userId) {
                $sortiesCréées[] = $sortie;
            }
        }



        return $sortiesCréées;

    }





}