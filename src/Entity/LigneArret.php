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

    // Si null, alors l'arrêt n'est pas desservi dans cette direction
    #[ORM\Column(nullable: true)]
    private ?int $indexDirection1 = null;

    // Si null, alors l'arrêt n'est pas desservi dans cette direction
    #[ORM\Column(nullable: true)]
    private ?int $indexDirection2 = null;

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

    public function getIndexDirection1(): ?int
    {
        return $this->indexDirection1;
    }

    public function setIndexDirection1(?int $indexDirection1): static
    {
        $this->indexDirection1 = $indexDirection1;

        return $this;
    }

    public function getIndexDirection2(): ?int
    {
        return $this->indexDirection2;
    }

    public function setIndexDirection2(?int $indexDirection2): static
    {
        $this->indexDirection2 = $indexDirection2;

        return $this;
    }
}
