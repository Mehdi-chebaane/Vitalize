<?php

namespace App\Repository;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Label\Alignment\LabelAlignmentLeft;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use App\Entity\Evenement;
use App\Entity\Reservation;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\HttpFoundation\Response;

use Doctrine\Persistence\ManagerRegistry;
use benhall14\phpCalendar\Calendar as Calendar;
use CalendarBundle\CalendarEvents;
use CalendarBundle\Entity\Event;
use CalendarBundle\Event\CalendarEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends ServiceEntityRepository<Evenement>
 *
 * @method Evenement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Evenement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Evenement[]    findAll()
 * @method Evenement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EvenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evenement::class);
    }

    //    /**
    //     * @return Evenement[] Returns an array of Evenement objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Evenement
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function QRcode($nom, $date, $localisation, $capacite, $organisateur, $description)
    {
        $text = sprintf(
            "Nom: %s\nDate: %s\nLocalisation: %s\nCapacité: %s\nOrganisateur: %s\nDescription: %s",
            $nom,
            $date ? $date->format('Y-m-d') : '',
            $localisation,
            $capacite,
            $organisateur,
            $description
        );
        $qr_code = QrCode::create($text)
            ->setSize(200)
            ->setMargin(5)
            ->setForegroundColor(new Color(255, 208, 52))
            ->setBackgroundColor(new Color(6, 66, 92));

        $writer = new PngWriter;

        $result = $writer->write($qr_code);

        header("Content-Type: image/png");
        $imageData = base64_encode($result->getString());
        return 'data:image/png;base64,' . $imageData;
    }
    public function addReservation($idevenement, $userid)
    {
        $entityManager = $this->getEntityManager();

        $reservation = new Reservation();
        $reservation->setIDUser($userid);
        $reservation->setIDEvent($idevenement);

        $entityManager->persist($reservation);
        $entityManager->flush();
    }

    public function deleteReservation($idevenement, $userid)
    {
        $entityManager = $this->getEntityManager();
        $reservation = $entityManager->getRepository(Reservation::class)->findOneBy(['ID_event' => $idevenement, 'ID_user' => $userid]);

        if ($reservation) {
            $entityManager->remove($reservation);
            $entityManager->flush();
        }
    }
    public function Recherche($idevenement, $userid)
    {
        $entityManager = $this->getEntityManager();
        $reservation = $entityManager->getRepository(Reservation::class)->findOneBy(['ID_event' => $idevenement, 'ID_user' => $userid]);
        if ($reservation)
            return true;
        else
            return false;
    }
    public function Calendar()
    {
    }
    public function searchAndSort($search, $sort)
{
    $query = $this->createQueryBuilder('e');

    if ($search) {
        $query->andWhere('e.nom LIKE :search')->setParameter('search', '%'.$search.'%');
    }

    return $query->orderBy('e.'.$sort, 'ASC')
        ->getQuery()
        ->getResult();
}
}
