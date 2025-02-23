<?php

namespace App\Controller;

use App\Entity\Ligne;
use App\Repository\LigneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PlanLignesController extends AbstractController
{
    #[Route("/", name: "app_liste_lignes")]
    public function listeLignes(LigneRepository $ligneRepository): Response
    {
        $lignes = $ligneRepository->findBy(["initialisee" => true], ['nom' => 'ASC']);

        return $this->render("liste_lignes.html.twig", [
            "lignes" => $lignes,
            "all" => false
        ]);
    }

    #[Route("/all", name: "app_liste_lignes_all")]
    public function listeLignesAll(LigneRepository $ligneRepository): Response
    {
        $lignes = $ligneRepository->findBy([], ['nom' => 'ASC']);

        return $this->render("liste_lignes.html.twig", [
            "lignes" => $lignes,
            "all" => true
        ]);
    }

    #[Route("/lignes/{id}", name: "app_ligne")]
    public function ligne(Ligne $ligne): Response
    {
        if (!$ligne->isInitialisee()) {
            return $this->redirectToRoute('app_liste_lignes');
        }

        $arretsDirection1 = [];
        $arretsDirection2 = [];

        // foreach ($ligne->getLigneArrets() as $ligneArret) {
        //     if(count($ligneArret->getEnregistrementsDirection2())){
        //         dump($ligneArret->getArret()->getNom() . " • " . $ligneArret->getEnregistrementsDirection2()[0]->getProchainPassage()->format("H:i"));
        //     }
        // }
        // die();
        foreach ($ligne->getLigneArrets() as $ligneArret) {
            if ($ligneArret->getIndexDirection1() !== null) {
                if(count($ligneArret->getEnregistrementsDirection1())){
                    $now = new \DateTime();
                    $interval = $now->diff($ligneArret->getEnregistrementsDirection1()[0]->getProchainPassage());
                    $arretsDirection1[$ligneArret->getIndexDirection1()] = $ligneArret->getArret()->getNom() . " • " . $interval->format('%i min %s sec');
                } else {
                    $arretsDirection1[$ligneArret->getIndexDirection1()] = $ligneArret->getArret()->getNom() . " • " . $ligneArret->getArret()->getVille();
                }
            }
            if ($ligneArret->getIndexDirection2() !== null) {
                $arretsDirection2[$ligneArret->getIndexDirection2()] = $ligneArret->getArret()->getNom() . " • " . $ligneArret->getArret()->getVille();
                if(count($ligneArret->getEnregistrementsDirection2())){
                    $now = new \DateTime();
                    $interval = $now->diff($ligneArret->getEnregistrementsDirection2()[0]->getProchainPassage());

                    $arretsDirection2[$ligneArret->getIndexDirection2()] = $ligneArret->getArret()->getNom() . " • " . $interval->format('%i min %s sec');
                } else {
                    $arretsDirection2[$ligneArret->getIndexDirection2()] = $ligneArret->getArret()->getNom() . " • " . $ligneArret->getArret()->getVille();
                }
            }
        }

        ksort($arretsDirection1);
        ksort($arretsDirection2);

        return $this->render("ligne.html.twig", [
            "ligne" => $ligne,
            "arretsDirection1" => $arretsDirection1,
            "arretsDirection2" => $arretsDirection2
        ]);
    }
}
