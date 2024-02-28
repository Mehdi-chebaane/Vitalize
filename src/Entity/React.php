<?php

namespace App\Entity;

use App\Repository\ReactRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReactRepository::class)]
class React
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reacts')]
    private ?Users $id_user = null;

    #[ORM\ManyToOne(inversedBy: 'reacts')]
    private ?Publication $id_pub = null;

    #[ORM\Column(name: "like_count", nullable: true)]
    private ?int $likeCount = null;

    #[ORM\Column(name: "dislike_count", nullable: true)]
    private ?int $dislikeCount = null;

    public function incrementLikeCount(): void
    {
        // Increment the like count by 1
        $this->likeCount++;
    }

    public function decrementLikeCount(): void
    {
        // Decrement the like count by 1
        $this->likeCount--;
    }

    public function incrementDislikeCount(): void
    {
        // Increment the dislike count by 1
        $this->dislikeCount++;
    }

    public function decrementDislikeCount(): void
    {
        // Decrement the dislike count by 1
        $this->dislikeCount--;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdUser(): ?Users
    {
        return $this->id_user;
    }

    public function setIdUser(?Users $id_user): self
    {
        $this->id_user = $id_user;
        return $this;
    }

    public function getIdPub(): ?Publication
    {
        return $this->id_pub;
    }

    public function setIdPub(?Publication $id_pub): self
    {
        $this->id_pub = $id_pub;
        return $this;
    }

    public function getLikeCount(): ?int
    {
        return $this->likeCount;
    }

    public function setLikeCount(int $likeCount): self
    {
        $this->likeCount = $likeCount;
        return $this;
    }

    public function getDislikeCount(): ?int
    {
        return $this->dislikeCount;
    }

    public function setDislikeCount(int $dislikeCount): self
    {
        $this->dislikeCount = $dislikeCount;
        return $this;
    }
}
