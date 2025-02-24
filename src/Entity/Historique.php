<?php

namespace App\Entity;

use App\Repository\HistoriqueRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HistoriqueRepository::class)]
class Historique
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'historiques')]
    #[ORM\JoinColumn(nullable: false)]
    private ?actif $actif = null;

    #[ORM\Column(length: 90)]
    private ?string $action = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateAction = null;

    #[ORM\ManyToOne(inversedBy: 'historiques')]
    private ?user $Actionneur = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $details = [];

    #[ORM\Column(length: 255)]
    private ?string $Etat = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getActif(): ?actif
    {
        return $this->actif;
    }

    public function setActif(?actif $actif): static
    {
        $this->actif = $actif;

        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function getDateAction(): ?\DateTimeImmutable
    {
        return $this->dateAction;
    }

    public function setDateAction(\DateTimeImmutable $dateAction): static
    {
        $this->dateAction = $dateAction;

        return $this;
    }

    public function getActionneur(): ?user
    {
        return $this->Actionneur;
    }

    public function setActionneur(?user $Actionneur): static
    {
        $this->Actionneur = $Actionneur;

        return $this;
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    public function setDetails(array $details): static
    {
        $this->details = $details;

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->Etat;
    }

    public function setEtat(string $Etat): static
    {
        $this->Etat = $Etat;

        return $this;
    }
}
