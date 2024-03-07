<?php

namespace App\Entity;

use App\Repository\ActiviteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ActiviteRepository::class)]
class Activite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_activite;

    #[ORM\Column(length: 255)]
    private $type_activite;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotNull(message: "Description ne peut pas être nulle.")]
    #[Assert\Length(min: 6, minMessage: "Description must have more than 5 characters.")]
    private $Description;
    #[Assert\NotNull(message: "Heure ne peut pas être nulle.")]
    #[Assert\Range(min: 1, minMessage: "Heure doit être positive.")]
    private $hours;
    #[Assert\NotNull(message: "Minute ne peut pas être nulle.")]
    #[Assert\Range(min: 1, minMessage: "Minute doit être positive.")]
    private $minutes;

    #[ORM\Column]
    private $duree;

    #[ORM\ManyToOne(targetEntity: Evenement::class, inversedBy: 'idevenement')]
    #[ORM\JoinColumn(name: 'idevenement', referencedColumnName: 'idevenement')]
    private $idevenement;

    #[ORM\Column(length: 255)]
    private $imageAct;

    public function getId_Activite(): ?int
    {
        return $this->id_activite;
    }

    public function getLabel(): string
    {
        return $this->getId_Activite();
    }

    public function getTypeActivite(): ?string
    {
        return $this->type_activite;
    }

    public function setTypeActivite(string $type_activite): self
    {
        $this->type_activite = $type_activite;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(string $Description): self
    {
        $this->Description = $Description;
        return $this;
    }

    public function getMinutes(): ?int
    {
        return $this->minutes;
    }

    public function setMinutes(int $minutes): self
    {
        $this->minutes = $minutes;
        return $this;
    }

    public function getHours(): ?int
    {
        return $this->hours;
    }

    public function setHours(int $hours): self
    {
        $this->hours = $hours;
        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(): self
    {
        $this->duree = $this->minutes + $this->hours * 60;
        return $this;
    }

    public function getIdevenement(): ?Evenement
    {
        return $this->idevenement;
    }

    public function setIdevenement(?Evenement $idevenement): self
    {
        $this->idevenement = $idevenement;
        return $this;
    }

    public function getImageAct(): ?string
    {
        return $this->imageAct;
    }

    public function setImageAct(string $imageAct): self
    {
        $this->imageAct = $imageAct;
        return $this;
    }
}
