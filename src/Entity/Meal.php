<?php

namespace App\Entity;

use App\Repository\MealRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Users;

#[ORM\Entity(repositoryClass: MealRepository::class)]
class Meal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
 
    #[Assert\NotBlank(message:'meal name is required')] 
     private ?string $nomRepas = null;

    #[Assert\NotBlank(message:'meal ingredients is required')] 
    #[ORM\Column(type: 'text')] 
    private ?string $ingredients = null;

#[Assert\NotBlank(message:'meal recipe is required')] 
    #[ORM\Column(type: 'text')] 
    private ?string $recette = null;

    #[ORM\Column(length: 255)]
    #[Assert\Choice(choices: ['Breakfast', 'Lunch', 'Dinner'])]
    private ?string $typeRepas = null;

#[Assert\NotBlank(message:'meal image is required')] 
    #[ORM\Column(length: 255)]
    private ?string $image = null;

#[Assert\NotBlank(message:'the number of people is required')] 
    #[ORM\Column(type: 'integer')]
    private ?int $nombrePersonnes = null;

#[Assert\NotBlank(message:'the duration is required')] 
    #[ORM\Column(length: 255)]
    private ?string $dureePreparation = null;

 

#[Assert\NotBlank(message:'the price is required')] 
    #[ORM\Column(type: 'float')]
    private ?float $prix = null;


    #[ORM\ManyToMany(targetEntity: Commande::class, inversedBy: 'meals')]
    private Collection $commande;

    #[ORM\Column]
    private ?int $quantity = null;

  

    public function __construct()
    {
        $this->commande = new ArrayCollection();
       
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomRepas(): ?string
    {
        return $this->nomRepas;
    }

    public function setNomRepas(?string $nomRepas): static
    {
        $this->nomRepas = $nomRepas;

        return $this;
    }

    public function getIngredients(): ?string
    {
        return $this->ingredients;
    }

    public function setIngredients(?string $ingredients): static
    {
        $this->ingredients = $ingredients;

        return $this;
    }

    public function getRecette(): ?string
    {
        return $this->recette;
    }

    public function setRecette(?string $recette): static
    {
        $this->recette = $recette;

        return $this;
    }

    public function getTypeRepas(): ?string
    {
        return $this->typeRepas;
    }

    public function setTypeRepas(?string $typeRepas): static
    {
        $this->typeRepas = $typeRepas;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getNombrePersonnes(): ?int
    {
        return $this->nombrePersonnes;
    }

    public function setNombrePersonnes(?int $nombrePersonnes): static
    {
        $this->nombrePersonnes = $nombrePersonnes;

        return $this;
    }

    public function getDureePreparation(): ?string
    {
        return $this->dureePreparation;
    }

    public function setDureePreparation(?string $dureePreparation): static
    {
        $this->dureePreparation = $dureePreparation;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(?float $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    /**
     * @return Collection<int, Commande>
     */
    public function getCommande(): Collection
    {
        return $this->commande;
    }

    public function addCommande(Commande $commande): static
    {
        if (!$this->commande->contains($commande)) {
            $this->commande->add($commande);
        }

        return $this;
    }

    public function removeCommande(Commande $commande): static
    {
        $this->commande->removeElement($commande);

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }
    
}
