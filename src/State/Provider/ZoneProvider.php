<?php

namespace App\State\Provider;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\ZoneRepository;
use App\Entity\Zone;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * GET /zones et GET /zones/{slug}
 * Retourne les offres publiées par type dans une zone
 * @implements ProviderInterface<Zone>
 */
class ZoneProvider implements ProviderInterface
{

    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.collection_provider')]
        private readonly ProviderInterface $collectionProvider,
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private readonly ProviderInterface $itemProvider,
        private readonly ZoneRepository $zoneRepository
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof CollectionOperationInterface) {

            $zones = $this->collectionProvider->provide($operation, $uriVariables, $context);
            $count = $this->zoneRepository->countPublishedOffersByZone();

            foreach ($zones as $zone) {
                $this->applyCounts($zone, $count);
            }
            return $zones;
        }

        $zone = $this->itemProvider->provide($operation, $uriVariables, $context);
        if ($zone instanceof Zone) {
            $this->applyCounts($zone, $this->zoneRepository->countPublishedOffersByZone());
        }

        return $zone;
    }

    public function applyCounts(Zone $zone, array $counts): void
    {
        if (isset($counts[$zone->getId()])) {
            $zone->setCounts($counts[$zone->getId()]);
        }
    }
}
