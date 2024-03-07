<?php

namespace App\Entity;

use App\Repository\PublicationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PublicationRepository::class)]
class Publication
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Type Cannot be empty")]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Titre Cannot be empty")]
    private ?string $titre = null;

    #[ORM\Column(length: 5000)]
    #[Assert\NotBlank(message: "Description cannot be empty")]
    private ?string $description = null;

    #[ORM\Column(length: 3000)]
    #[Assert\NotBlank(message: "Image cannot be empty")]
    private ?string $image = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $video = null;

    #[ORM\ManyToOne(inversedBy: 'publications')]
    #[Assert\NotBlank(message: "id_user Cannot be empty")]
    private ?Users $id_user = null;

    #[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'id_pub')]
    private Collection $commentaires;

    #[ORM\OneToMany(targetEntity: React::class, mappedBy: 'id_pub')]
    private Collection $reacts;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $views = null;

    #[ORM\OneToMany(mappedBy: 'id_pub', targetEntity: PublicationView::class)]
    private Collection $publicationViews;

    public function __construct()
    {
        $this->commentaires = new ArrayCollection();
        $this->reacts = new ArrayCollection();
        $this->publicationViews = new ArrayCollection();
    }

    public function getViews(): ?int
    {
        return $this->views;
    }

    public function setViews(?int $views): static
    {
        $this->views = $views;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(?string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

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

    public function getVideo(): ?string
    {
        return $this->video;
    }

    public function setVideo(?string $video): static
    {
        $this->video = $video;

        return $this;
    }

    public function getIdUser(): ?Users
    {
        return $this->id_user;
    }

    public function setIdUser(?Users $id_user): static
    {
        $this->id_user = $id_user;

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
            $commentaire->setIdPub($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): static
    {
        if ($this->commentaires->removeElement($commentaire)) {
            // set the owning side to null (unless already changed)
            if ($commentaire->getIdPub() === $this) {
                $commentaire->setIdPub(null);
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
            $react->setIdPub($this);
        }

        return $this;
    }

    public function removeReact(React $react): static
    {
        if ($this->reacts->removeElement($react)) {
            // set the owning side to null (unless already changed)
            if ($react->getIdPub() === $this) {
                $react->setIdPub(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PublicationView>
     */
    public function getPublicationViews(): Collection
    {
        return $this->publicationViews;
    }

    public function addPublicationView(PublicationView $publicationView): static
    {
        if (!$this->publicationViews->contains($publicationView)) {
            $this->publicationViews->add($publicationView);
            $publicationView->setIdPub($this);
        }

        return $this;
    }

    public function removePublicationView(PublicationView $publicationView): static
    {
        if ($this->publicationViews->removeElement($publicationView)) {
            // set the owning side to null (unless already changed)
            if ($publicationView->getIdPub() === $this) {
                $publicationView->setIdPub(null);
            }
        }

        return $this;
    }
}
