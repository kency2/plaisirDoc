<?php

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\PhotoUploadInput;
use App\Entity\Zone;
use App\Repository\ZoneRepository;
use App\Service\PhotoStorage;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

/**
 * Remplacer l'image de couverture d'une zone et supprimer l'ancien fichier
 */
class UploadZoneCoverProcessor implements ProcessorInterface
{

  public function __construct(
    private readonly EntityManagerInterface $em,
    private readonly ZoneRepository $zoneRepository,
    private readonly PhotoStorage $photoStorage
  ) {}


  /**
   * Undocumented function
   *
   * @param PhotoUploadInput $data
   */
  public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Zone
  {
    $zone = $this->zoneRepository->findOneBy(['slug'  => $uriVariables['slug']]);
    if (!$zone instanceof Zone) {
      // throw ApiException::notFound('zone.slug_not_found', ['slug' => $uriVariables['slug']]);
      throw new Exception('zone.slug_not_found');
    }

    $previous = $zone->getCoverImageUrl();
    $zone->setCoverImageUrl($this->photoStorage->store($data->file, 'zones'));
    $this->em->flush();

    if ($previous !== null) {
      $this->photoStorage->delete($previous);
    }

    return $zone;
  }
}
