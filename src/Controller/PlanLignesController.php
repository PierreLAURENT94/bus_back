<?php

namespace App\Controller;

use App\Entity\Ligne;
use App\Repository\EnregistrementRepository;
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
    public function ligne(Ligne $ligne, EnregistrementRepository $enregistrementRepository): Response
    {
        if (!$ligne->isInitialisee()) {
            return $this->redirectToRoute('app_liste_lignes');
        }

        $arretsDirection1 = [];
        $arretsDirection2 = [];

        $now = new \DateTime();
        foreach ($ligne->getLigneArrets() as $ligneArret) {
            if ($ligneArret->getIndexDirection1() !== null) {
                $arretsDirection1[$ligneArret->getIndexDirection1()]["nom"] = $ligneArret->getArret()->getNom();
                $arretsDirection1[$ligneArret->getIndexDirection1()]["ville"] = $ligneArret->getArret()->getVille();
                
                $enregistrement = $enregistrementRepository->findLatestEnregistrementDirection1ForLigneArret($ligneArret);
                if ($enregistrement) {
                    $interval = $now->diff($enregistrement->getProchainPassage());
                    $arretsDirection1[$ligneArret->getIndexDirection1()]["prochainPassage"] = $interval->format('%i min %s sec');
                }
                else {
                    $arretsDirection1[$ligneArret->getIndexDirection1()]["prochainPassage"] = null;
                }
            }
            if ($ligneArret->getIndexDirection2() !== null) {
                $arretsDirection2[$ligneArret->getIndexDirection2()]["nom"] = $ligneArret->getArret()->getNom();
                $arretsDirection2[$ligneArret->getIndexDirection2()]["ville"] = $ligneArret->getArret()->getVille();
                
                $enregistrement = $enregistrementRepository->findLatestEnregistrementDirection2ForLigneArret($ligneArret);
                if ($enregistrement) {
                    $interval = $now->diff($enregistrement->getProchainPassage());
                    $arretsDirection2[$ligneArret->getIndexDirection2()]["prochainPassage"] = $interval->format('%i min %s sec');
                }
                else {
                    $arretsDirection2[$ligneArret->getIndexDirection2()]["prochainPassage"] = null;
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
