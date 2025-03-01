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

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateTime = null;

    #[ORM\ManyToOne(inversedBy: 'enregistrementsDirection1')]
    private ?LigneArret $ligneArretDirection1 = null;

    #[ORM\ManyToOne(inversedBy: 'enregistrementsDirection2')]
    private ?LigneArret $ligneArretDirection2 = null;

    #[ORM\Column(nullable: true)]
    private ?\DateInterval $tempsVersProchainPassage = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateTime(): ?\DateTimeInterface
    {
        return $this->dateTime;
    }

    public function setDateTime(\DateTimeInterface $dateTime): static
    {
        $this->dateTime = $dateTime;

        return $this;
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
}
