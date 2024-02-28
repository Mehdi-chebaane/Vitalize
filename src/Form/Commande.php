<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotBlank(message:' quantity is required')] 
    private ?int $quantite = 0;

    

    #[Assert\NotBlank(message:' your adress is required')] 
    #[ORM\Column(length: 255)]
    private ?string $adresseClient = null;

    #[Assert\NotBlank(message:'Total amount is required')] 
    #[ORM\Column]
    private ?float $totaleCommande = null;
    #[Assert\NotBlank(message:'method of payment is required')] 
    #[Assert\Choice(choices: ['à la livraison', 'e-paiement'])]
    #[ORM\Column(length: 255)]
    private ?string $methodePaiement = null;

    #[ORM\Column(length: 255)]
    private ?string $etatCommande = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $instructionSpeciale = null;

    #[ORM\ManyToMany(targetEntity: Meal::class, inversedBy: 'commandes')]
    private Collection $meals;

    #[ORM\Column(length: 255)]
    private ?string $clientName = null;

    #[ORM\Column(length: 255)]
    private ?string $clientAdresse = null;

    #[ORM\Column(length: 255)]
    private ?string $clientPhone = null;

    #[ORM\Column]
    private array $mealQuantite = [];

    public function __construct()
    {
        $this->meals = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(?int $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getAdresseClient(): ?string
    {
        return $this->adresseClient;
    }

    public function setAdresseClient(?string $adresseClient): static
    {
        $this->adresseClient = $adresseClient;

        return $this;
    }

    public function getTotaleCommande(): ?float
    {
        return $this->totaleCommande;
    }

    public function setTotaleCommande(?float $totaleCommande): static
    {
        $this->totaleCommande = $totaleCommande;

        return $this;
    }

    public function getMethodePaiement(): ?string
    {
        return $this->methodePaiement;
    }

    public function setMethodePaiement(?string $methodePaiement): static
    {
        $this->methodePaiement = $methodePaiement;

        return $this;
    }

    public function getEtatCommande(): ?string
    {
        return $this->etatCommande;
    }

    public function setEtatCommande(?string $etatCommande): static
    {
        $this->etatCommande = "En attente";

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getInstructionSpeciale(): ?string
    {
        return $this->instructionSpeciale;
    }

    public function setInstructionSpeciale(?string $instructionSpeciale): static
    {
        $this->instructionSpeciale = $instructionSpeciale;

        return $this;
    }

    /**
     * @return Collection<int, Meal>
     */
    public function getMeals(): Collection
    {
        return $this->meals;
    }

    public function addMeal(Meal $meal): static
    {
        if (!$this->meals->contains($meal)) {
            $this->meals->add($meal);
            $meal->addCommande($this);
        }

        return $this;
    }

    public function removeMeal(Meal $meal): static
    {
        if ($this->meals->removeElement($meal)) {
            $meal->removeCommande($this);
        }

        return $this;
    }

    public function getClientName(): ?string
    {
        return $this->clientName;
    }

    public function setClientName(string $clientName): static
    {
        $this->clientName = $clientName;

        return $this;
    }

    public function getClientAdresse(): ?string
    {
        return $this->clientAdresse;
    }

    public function setClientAdresse(string $clientAdresse): static
    {
        $this->clientAdresse = $clientAdresse;

        return $this;
    }

    public function getClientPhone(): ?string
    {
        return $this->clientPhone;
    }

    public function setClientPhone(string $clientPhone): static
    {
        $this->clientPhone = $clientPhone;

        return $this;
    }

    public function getMealQuantite(): array
    {
        return $this->mealQuantite;
    }

    public function setMealQuantite(array $mealQuantite): static
    {
        $this->mealQuantite = $mealQuantite;

        return $this;
    }
}
