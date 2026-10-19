<?php

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Zone;
use App\Repository\ZoneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Override;
use Symfony\Component\String\Slugger\SluggerInterface;

class CreateZoneProcessor implements ProcessorInterface
{

  public function __construct(
    private readonly EntityManagerInterface $em,
    private readonly ZoneRepository $zoneRepository,
    private readonly SluggerInterface $slugger
  ) {}

  #[Override]
  public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
  {
    $zone = new Zone(
      name: $data->name,
      slug: $this->generateUniqueSlug($data->name),
      description: $data->description,
      centerLatitude: $data->centerLatitude,
      centerLongitude: $data->centerLongitude
    );

    $zone->setDisplayOrder($data->displayOrder);


    $this->em->persist($zone);
    $this->em->flush();

    return $zone;
  }

  private function generateUniqueSlug(string $name): string
  {
    $base = strtolower($this->slugger->slug($name)->toString());
    $slug = $base;
    $suffix = 1;

    while (null !== $this->zoneRepository->findOneBy(['slug' => $slug])) {
      $slug = $base . '-' . ++$suffix;
    }

    return $slug;
  }
}
