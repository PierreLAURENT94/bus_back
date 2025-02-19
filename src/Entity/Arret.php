<?php

namespace App\Entity;

use App\Repository\ArretRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArretRepository::class)]
class Arret
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nomId = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    /**
     * @var Collection<int, LigneArret>
     */
    #[ORM\OneToMany(targetEntity: LigneArret::class, mappedBy: 'arret')]
    private Collection $ligneArrets;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ville = null;

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

    /**
     * @return Collection<int, LigneArret>
     */
    public function getLigneArrets(): Collection
    {
        return $this->ligneArrets;
    }

    public function addLigneArret(LigneArret $ligneArret): static
    {
        if (!$this->ligneArrets->contains($ligneArret)) {
            $this->ligneArrets->add($ligneArret);
            $ligneArret->setArret($this);
        }

        return $this;
    }

    public function removeLigneArret(LigneArret $ligneArret): static
    {
        if ($this->ligneArrets->removeElement($ligneArret)) {
            // set the owning side to null (unless already changed)
            if ($ligneArret->getArret() === $this) {
                $ligneArret->setArret(null);
            }
        }

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(?string $ville): static
    {
        $this->ville = $ville;

        return $this;
    }
}
