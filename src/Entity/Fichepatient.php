<?php

namespace App\Entity;

use App\Repository\FichepatientRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FichepatientRepository::class)]
class Fichepatient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    #[Assert\NotBlank(message: "Weight cannot be blank.")]
    #[Assert\Length(max: 3, maxMessage: "Weight cannot be longer than {{ limit }} characters.")]
    private ?int $weight = null;

    #[ORM\Column(nullable: true)]
    #[Assert\NotBlank(message: "Muscle mass cannot be blank.")]
    private ?int $muscle_mass = null;

    #[ORM\Column(nullable: true)]
    #[Assert\NotBlank(message: "Height cannot be blank.")]
    #[Assert\Length(max: 3, maxMessage: "Height cannot be longer than {{ limit }} characters.")]
    private ?int $height = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: "Allergies cannot be longer than {{ limit }} characters.")]
    private ?string $allergies = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: "Illnesses cannot be longer than {{ limit }} characters.")]
    private ?string $illnesses = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: "Breakfast cannot be blank.")]
    #[Assert\Length(max: 255, maxMessage: "Breakfast cannot be longer than {{ limit }} characters.")]
    private ?string $breakfast = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: "Midday cannot be blank.")]
    #[Assert\Length(max: 255, maxMessage: "Midday cannot be longer than {{ limit }} characters.")]
    private ?string $midday = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: "Dinner cannot be blank.")]
    #[Assert\Length(max: 255, maxMessage: "Dinner cannot be longer than {{ limit }} characters.")]
    private ?string $dinner = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: "Snacks cannot be longer than {{ limit }} characters.")]
    private ?string $snacks = null;

    #[ORM\Column(nullable: true)]
    #[Assert\NotBlank(message: "Calories cannot be blank.")]
    #[Assert\Length(max: 4, maxMessage: "Calories cannot be longer than {{ limit }} characters.")]
    private ?int $calories = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $other = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: "id", referencedColumnName: "id")]
    private ?Users $doctor;

    public function getDoctor(): ?Users
    {
        return $this->doctor;
    }

    public function setDoctor(?Users $doctor): self
    {
        $this->doctor = $doctor;
        return $this;
    }
    public function getNom(): ?string
{
    if ($this->doctor) {
        return $this->doctor->getNom();
    }
    
    return null;
}
public function getPrenom(): ?string
{
    if ($this->doctor) {
        return $this->doctor->getPrenom();
    }
    
    return null;
}


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(?int $weight): static
    {
        $this->weight = $weight;

        return $this;
    }

    public function getMuscleMass(): ?int
    {
        return $this->muscle_mass;
    }

    public function setMuscleMass(?int $muscle_mass): static
    {
        $this->muscle_mass = $muscle_mass;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(?int $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function getAllergies(): ?string
    {
        return $this->allergies;
    }

    public function setAllergies(?string $allergies): static
    {
        $this->allergies = $allergies;

        return $this;
    }

    public function getIllnesses(): ?string
    {
        return $this->illnesses;
    }

    public function setIllnesses(?string $illnesses): static
    {
        $this->illnesses = $illnesses;

        return $this;
    }

    public function getBreakfast(): ?string
    {
        return $this->breakfast;
    }

    public function setBreakfast(?string $breakfast): static
    {
        $this->breakfast = $breakfast;

        return $this;
    }

    public function getMidday(): ?string
    {
        return $this->midday;
    }

    public function setMidday(?string $midday): static
    {
        $this->midday = $midday;

        return $this;
    }

    public function getDinner(): ?string
    {
        return $this->dinner;
    }

    public function setDinner(?string $dinner): static
    {
        $this->dinner = $dinner;

        return $this;
    }

    public function getSnacks(): ?string
    {
        return $this->snacks;
    }

    public function setSnacks(?string $snacks): static
    {
        $this->snacks = $snacks;

        return $this;
    }

    public function getCalories(): ?int
    {
        return $this->calories;
    }

    public function setCalories(?int $calories): static
    {
        $this->calories = $calories;

        return $this;
    }

    public function getOther(): ?string
    {
        return $this->other;
    }

    public function setOther(?string $other): static
    {
        $this->other = $other;

        return $this;
    }

    public function getAllAttributes(): array
{
    return [
        'id' => $this->getId(),
        'weight' => $this->getWeight(),
        'muscle_mass' => $this->getMuscleMass(),
        'height' => $this->getHeight(),
        'allergies' => $this->getAllergies(),
        'illnesses' => $this->getIllnesses(),
        'breakfast' => $this->getBreakfast(),
        'midday' => $this->getMidday(),
        'dinner' => $this->getDinner(),
        'snacks' => $this->getSnacks(),
        'calories' => $this->getCalories(),
        'other' => $this->getOther(),
    ];
}


}
