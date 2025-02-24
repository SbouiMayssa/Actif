<?php

namespace App\Entity;

use App\Repository\EmplacementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmplacementRepository::class)]
class Emplacement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 70)]
    private ?string $Adresse = null;

    #[ORM\Column(length: 25)]
    private ?string $Batiment = null;

    #[ORM\Column]
    private ?int $NumEtage = null;

    /**
     * @var Collection<int, Actif>
     */
    #[ORM\OneToMany(targetEntity: Actif::class, mappedBy: 'location')]
    private Collection $actifs;

    public function __construct()
    {
        $this->actifs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdresse(): ?string
    {
        return $this->Adresse;
    }

    public function setAdresse(string $Adresse): static
    {
        $this->Adresse = $Adresse;

        return $this;
    }

    public function getBatiment(): ?string
    {
        return $this->Batiment;
    }

    public function setBatiment(string $Batiment): static
    {
        $this->Batiment = $Batiment;

        return $this;
    }

    public function getNumEtage(): ?int
    {
        return $this->NumEtage;
    }

    public function setNumEtage(int $NumEtage): static
    {
        $this->NumEtage = $NumEtage;

        return $this;
    }

    /**
     * @return Collection<int, Actif>
     */
    public function getActifs(): Collection
    {
        return $this->actifs;
    }

    public function addActif(Actif $actif): static
    {
        if (!$this->actifs->contains($actif)) {
            $this->actifs->add($actif);
            $actif->setLocation($this);
        }

        return $this;
    }

    public function removeActif(Actif $actif): static
    {
        if ($this->actifs->removeElement($actif)) {
            // set the owning side to null (unless already changed)
            if ($actif->getLocation() === $this) {
                $actif->setLocation(null);
            }
        }

        return $this;
    }
}
