<?php

namespace App\Entity;

use App\Repository\PublicationViewRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PublicationViewRepository::class)]
class PublicationView
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'publicationViews')]
    private ?Users $id_user = null;

    #[ORM\ManyToOne(inversedBy: 'publicationViews')]
    private ?Publication $id_pub = null;

    #[ORM\Column]
    private ?int $View = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getIdPub(): ?Publication
    {
        return $this->id_pub;
    }

    public function setIdPub(?Publication $id_pub): static
    {
        $this->id_pub = $id_pub;

        return $this;
    }

    public function getView(): ?int
    {
        return $this->View;
    }

    public function setView(int $View): static
    {
        $this->View = $View;

        return $this;
    }
}
