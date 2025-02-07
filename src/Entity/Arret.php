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
     * @var Collection<int, Ligne>
     */
    #[ORM\ManyToMany(targetEntity: Ligne::class, inversedBy: 'arrets')]
    private Collection $Lignes;

    public function __construct()
    {
        $this->Lignes = new ArrayCollection();
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
     * @return Collection<int, Ligne>
     */
    public function getLignes(): Collection
    {
        return $this->Lignes;
    }

    public function addLigne(Ligne $ligne): static
    {
        if (!$this->Lignes->contains($ligne)) {
            $this->Lignes->add($ligne);
        }

        return $this;
    }

    public function removeLigne(Ligne $ligne): static
    {
        $this->Lignes->removeElement($ligne);

        return $this;
    }
}
