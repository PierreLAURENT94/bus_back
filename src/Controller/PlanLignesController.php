<?php

namespace App\Controller;

use App\Entity\Ligne;
use App\Repository\EnregistrementGroupRepository;
use App\Repository\EnregistrementRepository;
use App\Repository\LigneRepository;
use DateTime;
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
    public function ligne(Ligne $ligne, EnregistrementRepository $enregistrementRepository, EnregistrementGroupRepository $enregistrementGroupRepository): Response
    {
        if (!$ligne->isInitialisee()) {
            return $this->redirectToRoute('app_liste_lignes');
        }

        $arretsDirection1 = [];
        $arretsDirection2 = [];

        $enregistrementGroup = $enregistrementGroupRepository->findLastedEnregistrementGroupForLigne($ligne);

        foreach ($ligne->getLigneArrets() as $ligneArret) {
            if ($ligneArret->getIndexDirection1() !== null) {
                $arretsDirection1[$ligneArret->getIndexDirection1()]["nom"] = $ligneArret->getArret()->getNom();
                $arretsDirection1[$ligneArret->getIndexDirection1()]["ville"] = $ligneArret->getArret()->getVille();
                
                $enregistrement = $enregistrementRepository->findOneBy(["ligneArretDirection1" => $ligneArret, "enregistrementGroup" => $enregistrementGroup]);
                if ($enregistrement) {
                    $arretsDirection1[$ligneArret->getIndexDirection1()]["prochainPassageStr"] = $enregistrement->getTempsVersProchainPassage()->format('%i min');
                    $arretsDirection1[$ligneArret->getIndexDirection1()]["prochainPassage"] = (new DateTime())->setTimeStamp(0)->add($enregistrement->getTempsVersProchainPassage())->getTimeStamp();
                }
                else {
                    $arretsDirection1[$ligneArret->getIndexDirection1()]["prochainPassageStr"] = null;
                    $arretsDirection1[$ligneArret->getIndexDirection1()]["prochainPassage"] = null;
                }
            }
            if ($ligneArret->getIndexDirection2() !== null) {
                $arretsDirection2[$ligneArret->getIndexDirection2()]["nom"] = $ligneArret->getArret()->getNom();
                $arretsDirection2[$ligneArret->getIndexDirection2()]["ville"] = $ligneArret->getArret()->getVille();
                
                $enregistrement = $enregistrementRepository->findOneBy(["ligneArretDirection2" => $ligneArret, "enregistrementGroup" => $enregistrementGroup]);
                if ($enregistrement) {
                    $arretsDirection2[$ligneArret->getIndexDirection2()]["prochainPassageStr"] = $enregistrement->getTempsVersProchainPassage()->format('%i min');
                    $arretsDirection2[$ligneArret->getIndexDirection2()]["prochainPassage"] = (new DateTime())->setTimeStamp(0)->add($enregistrement->getTempsVersProchainPassage())->getTimeStamp();
                }
                else {
                    $arretsDirection2[$ligneArret->getIndexDirection2()]["prochainPassageStr"] = null;
                    $arretsDirection2[$ligneArret->getIndexDirection2()]["prochainPassage"] = null;
                }
            }
        }

        ksort($arretsDirection1);
        ksort($arretsDirection2);

        $busArrayDirection1 = [];
        $firstNotNull = false;
        $nbNull = 0;
        foreach ($arretsDirection1 as $key => $arret) {
            if($arret["prochainPassage"] !== null && !$firstNotNull) {
                $busArrayDirection1[] = $key * 150 - 35 ;
                $firstNotNull = true;
                continue;
            }
            if($arret["prochainPassage"] === null) {
                $nbNull++;
                continue;
            }
            $pos = 55 + ($key - 1) * 150;
            if ($arret["prochainPassage"] < $arretsDirection1[$key - (1 + $nbNull)]["prochainPassage"]) {
                $busArrayDirection1[] = $pos;
                $nbNull = 0;
            }
        }

        $busArrayDirection2 = [];
        $firstNotNull = false;
        $nbNull = 0;
        foreach ($arretsDirection2 as $key => $arret) {
            if($arret["prochainPassage"] !== null && !$firstNotNull) {
                $busArrayDirection2[] = $key * 150 - 35 ;
                $firstNotNull = true;
                continue;
            }
            if($arret["prochainPassage"] === null) {
                $nbNull++;
                continue;
            }
            $pos = 55 + ($key - 1) * 150;
            if ($arret["prochainPassage"] < $arretsDirection2[$key - (1 + $nbNull)]["prochainPassage"]) {
                $busArrayDirection2[] = $pos;
                $nbNull = 0;
            }
        }

        return $this->render("ligne.html.twig", [
            "ligne" => $ligne,
            "arretsDirection1" => $arretsDirection1,
            "arretsDirection2" => $arretsDirection2,
            "busArrayDirection1" => $busArrayDirection1,
            "busArrayDirection2" => $busArrayDirection2,
            "heure" => $enregistrementGroup->getHeure()->format('H:i:s')
        ]);
    }
}
