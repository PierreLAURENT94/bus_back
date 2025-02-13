<?php

namespace App\Entity;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\LigneRepository;

#[ORM\Entity(repositoryClass: LigneRepository::class)]
class Ligne
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nomId = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 6)]
    private ?string $couleurHexa = null;

    #[ORM\Column(length: 6)]
    private ?string $texteCouleurHexa = null;

    /**
     * @var Collection<int, LigneArret>
     */
    #[ORM\OneToMany(targetEntity: LigneArret::class, mappedBy: 'ligne')]
    private Collection $ligneArrets;

    public function __construct()
    {
        $this->ligneArrets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomId(): ?string
    {
        return $this->nomId;
    }

    public function setNomId(string $nomId): static
    {
        $this->nomId = $nomId;
        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getCouleurHexa(): ?string
    {
        return $this->couleurHexa;
    }

    public function setCouleurHexa(string $couleurHexa): static
    {
        $this->couleurHexa = $couleurHexa;
        return $this;
    }

    public function getTexteCouleurHexa(): ?string
    {
        return $this->texteCouleurHexa;
    }

    public function setTexteCouleurHexa(string $texteCouleurHexa): static
    {
        $this->texteCouleurHexa = $texteCouleurHexa;
        return $this;
    }

    /**
     * @return Collection<int, LigneArret>
     */
    public function getLigneArrets(): Collection
    {
        return $this->ligneArrets;
    }

    /**
     * @return Collection<int, LigneArret>
     */
    public function getLigneArretsByOrdre(): Collection
    {
        $criteria = Criteria::create()->orderBy(['ordre' => Criteria::ASC]);

        return $this->ligneArrets->matching($criteria);
    }

    public function addLigneArret(LigneArret $ligneArret): static
    {
        if (!$this->ligneArrets->contains($ligneArret)) {
            $this->ligneArrets->add($ligneArret);
            $ligneArret->setLigne($this);
        }

        return $this;
    }

    public function removeLigneArret(LigneArret $ligneArret): static
    {
        if ($this->ligneArrets->removeElement($ligneArret)) {
            // set the owning side to null (unless already changed)
            if ($ligneArret->getLigne() === $this) {
                $ligneArret->setLigne(null);
            }
        }

        return $this;
    }
}
