<?php

namespace App\EventSubscriber;

use App\Entity\Evenement;
use CalendarBundle\CalendarEvents;
use CalendarBundle\Entity\Event;
use CalendarBundle\Event\CalendarEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Repository\EvenementRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\VarDumper\Cloner\Data;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CalendarSubscriber implements EventSubscriberInterface
{        private  $router;

    private $evenementRepository;

    // Inject the EvenementRepository into the constructor
    public function __construct(EvenementRepository $evenementRepository,UrlGeneratorInterface $router)
    {
        $this->evenementRepository = $evenementRepository;
        $this->router =$router;
    }
   
    public static function getSubscribedEvents()
    {
        return [
            CalendarEvents::SET_DATA => 'onCalendarSetData',
        ];
    }

    public function onCalendarSetData(CalendarEvent $calendar)
    {
        $start = $calendar->getStart();
        $end = $calendar->getEnd();
        $filters = $calendar->getFilters();

        // Corrected variable name to use the repository from $this
        $events = $this->evenementRepository->findAll();

        foreach ($events as $event) {
            $levent = new Event(
                $event->getNom(),
                $event->getDate(),
            );
            if(strpos("conférence",$event->getNom())!==false)
            $levent->setOptions([
                'backgroundColor' => 'red',
                'borderColor' => 'red',
            ]);
            if(strpos("sortie",$event->getNom())!==false)
            $levent->setOptions([
                'backgroundColor' => 'purple',
                'borderColor' => 'purple',
            ]);
            if(strpos("cercle de discussion",$event->getNom())!==false)
            $levent->setOptions([
                'backgroundColor' => 'green',
                'borderColor' => 'green',
            ]);
            if($event->getDate()<new DateTime())
            $levent->setOptions([
                'backgroundColor' => 'grey',
                'borderColor' => 'red',
            ]);
            $levent->addOption(
                'url',
                $this->router->generate('app_evenement_edit', [
                    'idevenement' => $event->getIdevenement(),
                ])
            );
            

            $calendar->addEvent($levent);
        }
    }
}
