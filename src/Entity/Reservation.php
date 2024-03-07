<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\Column]
    private  $ID_user = null;

    #[ORM\Column]
    private  $ID_event = null;

    public function getId() 
    {
        return $this->id;
    }

    public function getIDUser()
    {
        return $this->ID_user;
    }

    public function setIDUser(int $ID_user)
    {
        $this->ID_user = $ID_user;

        return $this;
    }

    public function getIDEvent() 
    {
        return $this->ID_event;
    }

    public function setIDEvent(int $ID_event)
    {
        $this->ID_event = $ID_event;

        return $this;
    }
}
