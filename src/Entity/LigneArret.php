<?php

namespace App\Entity;

use App\Repository\LigneArretRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    /**
     * @var Collection<int, Enregistrement>
     */
    #[ORM\OneToMany(targetEntity: Enregistrement::class, mappedBy: 'ligneArretDirection1')]
    private Collection $enregistrementsDirection1;

    /**
     * @var Collection<int, Enregistrement>
     */
    #[ORM\OneToMany(targetEntity: Enregistrement::class, mappedBy: 'ligneArretDirection2')]
    private Collection $enregistrementsDirection2;

    public function __construct()
    {
        $this->enregistrementsDirection1 = new ArrayCollection();
        $this->enregistrementsDirection2 = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, Enregistrement>
     */
    public function getEnregistrementsDirection1(): Collection
    {
        return $this->enregistrementsDirection1;
    }

    public function addEnregistrementsDirection1(Enregistrement $enregistrementsDirection1): static
    {
        if (!$this->enregistrementsDirection1->contains($enregistrementsDirection1)) {
            $this->enregistrementsDirection1->add($enregistrementsDirection1);
            $enregistrementsDirection1->setLigneArretDirection1($this);
        }

        return $this;
    }

    public function removeEnregistrementsDirection1(Enregistrement $enregistrementsDirection1): static
    {
        if ($this->enregistrementsDirection1->removeElement($enregistrementsDirection1)) {
            // set the owning side to null (unless already changed)
            if ($enregistrementsDirection1->getLigneArretDirection1() === $this) {
                $enregistrementsDirection1->setLigneArretDirection1(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Enregistrement>
     */
    public function getEnregistrementsDirection2(): Collection
    {
        return $this->enregistrementsDirection2;
    }

    public function addEnregistrementsDirection2(Enregistrement $enregistrementsDirection2): static
    {
        if (!$this->enregistrementsDirection2->contains($enregistrementsDirection2)) {
            $this->enregistrementsDirection2->add($enregistrementsDirection2);
            $enregistrementsDirection2->setLigneArretDirection2($this);
        }

        return $this;
    }

    public function removeEnregistrementsDirection2(Enregistrement $enregistrementsDirection2): static
    {
        if ($this->enregistrementsDirection2->removeElement($enregistrementsDirection2)) {
            // set the owning side to null (unless already changed)
            if ($enregistrementsDirection2->getLigneArretDirection2() === $this) {
                $enregistrementsDirection2->setLigneArretDirection2(null);
            }
        }

        return $this;
    }
}
