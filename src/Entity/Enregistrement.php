<?php

namespace App\Entity;

use App\Repository\EnregistrementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EnregistrementRepository::class)]
class Enregistrement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'enregistrementsDirection1')]
    private ?LigneArret $ligneArretDirection1 = null;

    #[ORM\ManyToOne(inversedBy: 'enregistrementsDirection2')]
    private ?LigneArret $ligneArretDirection2 = null;

    #[ORM\Column(nullable: true)]
    private ?\DateInterval $tempsVersProchainPassage = null;

    #[ORM\ManyToOne(inversedBy: 'enregistrements')]
    private ?EnregistrementGroup $enregistrementGroup = null;

    #[ORM\Column(nullable: true)]
    private ?\DateInterval $tempsVersProchainPassage2 = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLigneArretDirection1(): ?LigneArret
    {
        return $this->ligneArretDirection1;
    }

    public function setLigneArretDirection1(?LigneArret $ligneArretDirection1): static
    {
        $this->ligneArretDirection1 = $ligneArretDirection1;

        return $this;
    }

    public function getLigneArretDirection2(): ?LigneArret
    {
        return $this->ligneArretDirection2;
    }

    public function setLigneArretDirection2(?LigneArret $ligneArretDirection2): static
    {
        $this->ligneArretDirection2 = $ligneArretDirection2;

        return $this;
    }

    public function getTempsVersProchainPassage(): ?\DateInterval
    {
        return $this->tempsVersProchainPassage;
    }

    public function setTempsVersProchainPassage(?\DateInterval $tempsVersProchainPassage): static
    {
        $this->tempsVersProchainPassage = $tempsVersProchainPassage;

        return $this;
    }

    public function getEnregistrementGroup(): ?EnregistrementGroup
    {
        return $this->enregistrementGroup;
    }

    public function setEnregistrementGroup(?EnregistrementGroup $enregistrementGroup): static
    {
        $this->enregistrementGroup = $enregistrementGroup;

        return $this;
    }

    public function getTempsVersProchainPassage2(): ?\DateInterval
    {
        return $this->tempsVersProchainPassage2;
    }

    public function setTempsVersProchainPassage2(?\DateInterval $tempsVersProchainPassage2): static
    {
        $this->tempsVersProchainPassage2 = $tempsVersProchainPassage2;

        return $this;
    }
}
