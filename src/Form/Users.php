<?php

namespace App\Entity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection; 

use App\Repository\UsersRepository;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UsersRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'Vous etes deja inscrit')]



class Users implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: "L email ne peut pas être vide.")]
    #[Assert\Email(message: "L'adresse e-mail '{{ value }}' n'est pas valide.")]
    private ?string $email = null;


    #[ORM\Column(type:"json")]
    private  $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
/*     #[Assert\NotBlank(message: "Le mot de passe ne peut pas être vide.")]
#[Assert\Length(
    min: 6,
    max: 255,
    minMessage: "Le mot de passe doit contenir au moins {{ limit }} caractères.",
    maxMessage: "Le mot de passe ne peut pas dépasser {{ limit }} caractères."
)]
#[Assert\Regex(
    pattern: "/[a-zA-Z]/",
    message: "Le mot de passe doit contenir au moins une lettre."
)]
#[Assert\Regex(
    pattern: "/\d/",
    message: "Le mot de passe doit contenir au moins un chiffre."
)]
#[Assert\Regex(
    pattern: "/[@$!%*?&]/",
    message: "Le mot de passe doit contenir au moins un caractère spécial."
)] */
    private ?string $password = null;


    
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom ne peut pas être vide.")]
    #[Assert\Length(
       min: 3,
        max: 50,
        minMessage: "Votre nom doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Votre nom ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $nom = null;


    

   
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le prenom ne peut pas être vide.")]
    #[Assert\Length(
       min: 3,
        max: 50,
        minMessage: "Votre prenom doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Votre prenom ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $prenom = null;

    #[ORM\Column]
    private ?bool $status = false;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le Telephone ne peut pas être vide.")]
    private ?string $tel = null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $numCnam = null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $Adresse = null;

    #[ORM\OneToMany(targetEntity: Publication::class, mappedBy: 'id_user')]
    private Collection $publications;

    #[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'id_user')]
    private Collection $commentaires;

    #[ORM\OneToMany(targetEntity: React::class, mappedBy: 'id_user')]
    private Collection $reacts;

    public function __construct()
    {
        $this->publications = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
        $this->reacts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_PATIENT';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        
        // $this->plainPassword = null;
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }
    public function getSalt()
    {
        
        return null; 
    }

    public function getUsername()
    {
        
        return $this->email; 
    }
    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function setTel(string $tel): static
    {
        $this->tel = $tel;

        return $this;
    }

    public function getNumCnam(): ?string
    {
        return $this->numCnam;
    }

    public function setNumCnam(?string $numCnam): static
    {
        $this->numCnam = $numCnam;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->Adresse;
    }

    public function setAdresse(?string $Adresse): static
    {
        $this->Adresse = $Adresse;

        return $this;
    }


     /**
     * @return Collection<int, Publication>
     */
    public function getPublications(): Collection
    {
        return $this->publications;
    }

    public function addPublication(Publication $publication): static
    {
        if (!$this->publications->contains($publication)) {
            $this->publications->add($publication);
            $publication->setIdUser($this);
        }

        return $this;
    }

    public function removePublication(Publication $publication): static
    {
        if ($this->publications->removeElement($publication)) {
            // set the owning side to null (unless already changed)
            if ($publication->getIdUser() === $this) {
                $publication->setIdUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Commentaire>
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(Commentaire $commentaire): static
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires->add($commentaire);
            $commentaire->setIdUser($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): static
    {
        if ($this->commentaires->removeElement($commentaire)) {
            // set the owning side to null (unless already changed)
            if ($commentaire->getIdUser() === $this) {
                $commentaire->setIdUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, React>
     */
    public function getReacts(): Collection
    {
        return $this->reacts;
    }

    public function addReact(React $react): static
    {
        if (!$this->reacts->contains($react)) {
            $this->reacts->add($react);
            $react->setIdUser($this);
        }

        return $this;
    }

    public function removeReact(React $react): static
    {
        if ($this->reacts->removeElement($react)) {
            // set the owning side to null (unless already changed)
            if ($react->getIdUser() === $this) {
                $react->setIdUser(null);
            }
        }

        return $this;
    }
}
