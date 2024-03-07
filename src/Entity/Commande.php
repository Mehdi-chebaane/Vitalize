<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{

    public function __construct()
{
    $this->meals = new ArrayCollection();
   
    
}
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message:'Client name  is required')] 
    #[ORM\Column(length: 255)]
    private ?string $clientName = null;

    #[Assert\NotBlank(message:'Client Family Name  is required')] 
    #[ORM\Column(length: 255)]
    private ?string $clientFamilyName = null;

    #[Assert\NotBlank(message:'Client adress  is required')] 
    #[ORM\Column(length: 255)]
    private ?string $clientAdresse = null;

    #[Assert\NotBlank(message:'Client phone  is required')] 
    #[ORM\Column(length: 255)]
    private ?string $clientPhone = null;
    
    #[ORM\Column(type: "json", nullable: true)]
    private ?array $mealQuantities = null;
    
    
    #[Assert\NotBlank(message:'method of payment is required')] 
    #[Assert\Choice(choices: ['à la livraison', 'e-paiement'])]
    #[ORM\Column(length: 255)]
    private ?string $methodePaiement = null;

    #[Assert\NotBlank(message:' order state  is required')] 
    #[ORM\Column(length: 255)]
    private ?string $etatCommande = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $instructionSpeciale = null;

    #[ORM\ManyToMany(targetEntity: Meal::class, mappedBy: 'commande')]
    private Collection $meals;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    private ?Users $User = null;

    #[ORM\Column]
    private ?int $prixtotal = null;

    

   

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getClientName(): ?string
    {
        return $this->clientName;
    }

    public function setClientName(?string $clientName): static
    {
        $this->clientName = $clientName;

        return $this;
    }
    public function getClientFamilyName(): ?string
    {
        return $this->clientFamilyName;
    }

    public function setClientFamilyName(?string $clientFamilyName): static
{
    $this->clientFamilyName = $clientFamilyName;
    return $this;
}
    public function getClientAdresse(): ?string
    {
        return $this->clientAdresse;
    }

    public function setClientAdresse(?string $clientAdresse): static
    {
        $this->clientAdresse = $clientAdresse;

        return $this;
    }

    public function getClientPhone(): ?string
    {
        return $this->clientPhone;
    }

    public function setClientPhone(?string $clientPhone): static
    {
        $this->clientPhone = $clientPhone;

        return $this;
    }

    public function getMealQuantities(): ?array
    {
        return $this->mealQuantities;
    }

    public function setMealQuantities(?array $mealQuantities): void
    {
        $this->mealQuantities = $mealQuantities;
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
    $this->etatCommande = $etatCommande;

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

    public function getUser(): ?Users
    {
        return $this->User;
    }

    public function setUser(?Users $User): static
    {
        $this->User = $User;

        return $this;
    }

    public function getPrixtotal(): ?int
    {
        return $this->prixtotal;
    }

    public function setPrixtotal(int $prixtotal): static
    {
        $this->prixtotal = $prixtotal;

        return $this;
    }

  

    


}
