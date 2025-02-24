<?php

namespace App\Entity;

use App\Entity\Emplacement;
use App\Entity\Employer;
use App\Entity\User;
use App\Repository\ActifRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActifRepository::class)]
class Actif
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateAcquisation = null;

    #[ORM\ManyToOne(inversedBy: 'actifs')]
    private ?Emplacement $location = null;

    /**
     * @var Collection<int, Employer> 
     */
    #[ORM\ManyToMany(targetEntity: Employer::class, inversedBy: 'actifs')]
    private Collection $UserAssigned;

    #[ORM\Column(length: 255,unique:true)]
    private ?string $numSerie = null;

    #[ORM\ManyToOne(inversedBy: 'actifs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $createdBy = null;

    #[ORM\Column(length: 255)]
    private ?string $etat = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $DeletedAt = null;

    /**
     * @var Collection<int, Historique>
     */
    #[ORM\OneToMany(targetEntity: Historique::class, mappedBy: 'actif')]
    private Collection $historiques;

    public function __construct()
    {
        $this->UserAssigned = new ArrayCollection();
        $this->historiques = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }



    public function getDateAcquisation(): ?\DateTimeInterface
    {
        return $this->dateAcquisation;
    }

    public function setDateAcquisation(\DateTimeInterface $dateAcquisation): static
    {
        $this->dateAcquisation = $dateAcquisation;

        return $this;
    }

    public function getLocation(): ?Emplacement
    {
        return $this->location;
    }

    public function setLocation(?Emplacement $location): static
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return Collection<int, Employer>
     */
    public function getUserAssigned(): Collection
    {
        return $this->UserAssigned;
    }

    public function addUserAssigned(Employer $userAssigned): static
    {
        if (!$this->UserAssigned->contains($userAssigned)) {
            $this->UserAssigned->add($userAssigned);
        }

        return $this;
    }

    public function removeUserAssigned(Employer $userAssigned): static
    {
        $this->UserAssigned->removeElement($userAssigned);

        return $this;
    }

    public function getNumSerie(): ?string
    {
        return $this->numSerie;
    }

    public function setNumSerie(string $numSerie): static
    {
        $this->numSerie = $numSerie;

        return $this;
    }

    public function getCreatedBy(): ?user
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?user $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->DeletedAt;
    }

    public function setDeletedAt(?\DateTimeImmutable $DeltedAt): static
    {
        $this->DeletedAt = $DeltedAt;

        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->DeletedAt !== null;
    }

    /**
     * @return Collection<int, Historique>
     */
    public function getHistoriques(): Collection
    {
        return $this->historiques;
    }

    public function addHistorique(Historique $historique): static
    {
        if (!$this->historiques->contains($historique)) {
            $this->historiques->add($historique);
            $historique->setActif($this);
        }

        return $this;
    }

    public function removeHistorique(Historique $historique): static
    {
        if ($this->historiques->removeElement($historique)) {
            // set the owning side to null (unless already changed)
            if ($historique->getActif() === $this) {
                $historique->setActif(null);
            }
        }

        return $this;
    }
}
