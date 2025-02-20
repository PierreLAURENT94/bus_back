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
            "lignes" => $lignes
        ]);
    }

    #[Route("/lignes/{id}", name: "app_ligne")]
    public function ligne(Ligne $ligne): Response
    {
        $arrets = [];

        foreach ($ligne->getLigneArretsByOrdre() as $ligneArret) {
            $arrets[$ligneArret->getId()] = $ligneArret->getArret()->getNom();
        }

        ksort($arrets);

        return $this->render("ligne.html.twig", [
            "ligne" => $ligne,
            "arrets" => $arrets
        ]);
    }
}
