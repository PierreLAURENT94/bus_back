<?php

namespace App\Entity;

use App\Repository\EnregistrementGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EnregistrementGroupRepository::class)]
class EnregistrementGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $heure = null;

    /**
     * @var Collection<int, Enregistrement>
     */
    #[ORM\OneToMany(targetEntity: Enregistrement::class, mappedBy: 'enregistrementGroup')]
    private Collection $enregistrements;

    #[ORM\ManyToOne(inversedBy: 'enregistrementGroups')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ligne $ligne = null;

    public function __construct()
    {
        $this->enregistrements = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHeure(): ?\DateTimeInterface
    {
        return $this->heure;
    }

    public function setHeure(\DateTimeInterface $heure): static
    {
        $this->heure = $heure;

        return $this;
    }

    /**
     * @return Collection<int, Enregistrement>
     */
    public function getEnregistrements(): Collection
    {
        return $this->enregistrements;
    }

    public function addEnregistrement(Enregistrement $enregistrement): static
    {
        if (!$this->enregistrements->contains($enregistrement)) {
            $this->enregistrements->add($enregistrement);
            $enregistrement->setEnregistrementGroup($this);
        }

        return $this;
    }

    public function removeEnregistrement(Enregistrement $enregistrement): static
    {
        if ($this->enregistrements->removeElement($enregistrement)) {
            // set the owning side to null (unless already changed)
            if ($enregistrement->getEnregistrementGroup() === $this) {
                $enregistrement->setEnregistrementGroup(null);
            }
        }

        return $this;
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
}
