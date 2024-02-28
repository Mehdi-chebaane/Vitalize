<?php

namespace App\Entity;

use App\Repository\RendezVousRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RendezVousRepository::class)]
class RendezVous
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "rdv_id", type: "integer")]
    private ?int $rdvId = null;
   

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $date;

    #[ORM\Column]
    private ?string $lien;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: "id_doctor", referencedColumnName: "id")]
    private ?Users $doctor;


    #[ORM\Column]
    private ?bool $is_available = null;

    public function getRdvId(): ?int
    {
        return $this->rdvId;
    }
    public function getDoctor(): ?Users
    {
        return $this->doctor;
    }

    public function setDoctor(?Users $doctor): self
    {
        $this->doctor = $doctor;
        return $this;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getLien(): ?string
    {
        return $this->lien;
    }

    public function setLien(?string $lien): self
    {
        $this->lien = $lien;
        return $this;
    }

    public function getUser(): ?Users
    {
        return $this->doctor;
    }

    public function setUser(?Users $user): self
    {
        $this->user = $user;
        return $this;
    }

   
    public function getId(): ?int
{
    return $this->rdvId;
}

    public function isIsAvailable(): ?bool
    {
        return $this->is_available;
    }

    public function setIsAvailable(bool $is_available): static
    {
        $this->is_available = $is_available;

        return $this;
    }

}