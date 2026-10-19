<?php

namespace App\Repository;

use App\Entity\Activity;
use App\Entity\Event;
use App\Entity\Listing;
use App\Entity\Zone;
use App\Enum\ListingStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Zone>
 */
class ZoneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Zone::class);
    }

    /**
     * Nombre d'offres (par type) publiés dans les différentes zones
     *
     * @return array
     */
    public function countPublishedOffersByZone(): array
    {
        $counts = [];
        $types = [
            'listings'      => Listing::class,
            'activities'    => Activity::class,
            'events'        => Event::class
        ];

        foreach ($types as $key => $type) {
            $rows = $this->getEntityManager()->createQueryBuilder()
                ->select('IDENTITY(o.zone) AS zoneId', 'COUNT(o.id) AS total')
                ->from($type, 'o')
                ->where('o.status = :status')
                ->setParameter('status', ListingStatus::Published)
                ->groupBy('o.zone')
                ->getQuery()
                ->getArrayResult();

            foreach ($rows as $row) {
                $zoneId = (int) $row['zoneId'];
                $counts[$zoneId] ??= ['listings' => 0, 'activities' => 0, 'events' => 0];
                $counts[$zoneId][$key] = (int) $row['total'];
            }
        }
        return $counts;
    }

    //    /**
    //     * @return Zone[] Returns an array of Zone objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('z')
    //            ->andWhere('z.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('z.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Zone
    //    {
    //        return $this->createQueryBuilder('z')
    //            ->andWhere('z.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
