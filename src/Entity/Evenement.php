<?php

namespace App\Entity;

use App\Repository\EvenementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private $idevenement;

    
    
    #[ORM\Column(length: 255)]
    #[Assert\NotNull(message: "Nom ne peut pas être nul.")]
    #[Assert\Length(min: 2, minMessage: "Le nom doit comporter plus de 2 caractères.")]
    private $nom;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: "date ne peut pas être nulle.")]

    #[Assert\GreaterThanOrEqual("today", message: "La date ne peut pas être dans le passé.")]
    #[Assert\LessThanOrEqual("+1 year", message: "La date ne peut pas être postérieure à plus d'un an.")]
    private $date;

    #[ORM\Column(length: 255)]
    #[Assert\NotNull(message: "Localisation ne peut pas être nulle.")]

    #[Assert\Length(min: 3, minMessage: "la localisation doit comporter plus de 3 caractères.")]
    private $localisation;

    #[ORM\Column]
    #[Assert\NotNull(message: "Capacité ne peut pas être nulle.")]

    #[Assert\Range(min: 1, minMessage: "Capacité doit être positive.")]

    private $capacite;

    #[ORM\Column(length: 255)]
    #[Assert\NotNull(message: "Nom de l'organisateur ne peut pas être nul.")]

    #[Assert\Length(min: 2, minMessage: "Le nom d'organisateur doit comporter plus de 2 caractères.")]
    private $organisateur;

    #[ORM\Column(length: 255)]
    #[Assert\NotNull(message: "Description ne peut pas être nulle.")]

    #[Assert\Length(min: 5, minMessage: "La description doit comporter plus de 5 caractères.")]
    private $description;

    #[ORM\Column(length: 255)]
    private $imageEve;


    public function getIdevenement()
    {
        return $this->idevenement;
    }

    public function getNom()
    {
        return $this->nom;
    }

    public function setNom($nom)
    {
        $this->nom = $nom;
        return $this;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    public function getLocalisation()
    {
        return $this->localisation;
    }

    public function setLocalisation($localisation)
    {
        $this->localisation = $localisation;
        return $this;
    }

    public function getCapacite()
    {
        return $this->capacite;
    }

    public function setCapacite($capacite)
    {
        $this->capacite = $capacite;
        return $this;
    }

    public function getOrganisateur()
    {
        return $this->organisateur;
    }

    public function setOrganisateur($organisateur)
    {
        $this->organisateur = $organisateur;
        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function getImageEve()
    {
        return $this->imageEve;
    }

    public function setImageEve($imageEve)
    {
        $this->imageEve = $imageEve;
        return $this;
    }
}
