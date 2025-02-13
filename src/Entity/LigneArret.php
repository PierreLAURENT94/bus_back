<?php

namespace App\Entity;

use App\Repository\LigneArretRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LigneArretRepository::class)]
class LigneArret
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'ligneArrets')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Ligne $ligne = null;

    #[ORM\Column]
    private ?int $ordre = null;

    #[ORM\ManyToOne(inversedBy: 'ligneArrets')]
    private ?Arret $arret = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLigne(): ?Ligne
    {
        return $this->ligne;
    }

    public function setLigne(?Ligne $ligne): static
    {
        $this->ligne = $ligne;

        return $this;
    }

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): static
    {
        $this->ordre = $ordre;

        return $this;
    }

    public function getArret(): ?Arret
    {
        return $this->arret;
    }

    public function setArret(?Arret $arret): static
    {
        $this->arret = $arret;

        return $this;
    }
}
