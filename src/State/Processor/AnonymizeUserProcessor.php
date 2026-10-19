<?php

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\RefreshToken;
use App\Entity\User;
use App\Enum\ListingStatus;
use App\Enum\ProviderType;
use App\Repository\ActivityRepository;
use App\Repository\EventRepository;
use App\Repository\ListingRepository;
use App\Repository\ProductRepository;
use App\Repository\ProviderProfileRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;


/**
 * Le "DELETE /me" ne supprime pas la ligne `User` : il l'anonymise (RGPD). 
 * L'historique transactionnel — une vraie suppression casserait ces enregistrements (comptabilité, avis affichés aux autres utilisateurs).
 * @implements ProcessorInterface<User, void>
 */
class AnonymizeUserProcessor implements ProcessorInterface
{
  public function __construct(
    private readonly EntityManagerInterface $em,
    private readonly ProviderProfileRepository $providerProfileRepository,
    private readonly ListingRepository $listingRepository,
    private readonly ActivityRepository $activiyRepository,
    private readonly EventRepository $eventRepository,
    private readonly ProductRepository $productRepository
  ) {
  }

  /**
   * @param User $data
   */
  public function process($data, Operation $operation, array $uriVariables = [], array $context = []): void
  {
    $this->archivePublishedOffers($data);
    $this->revokeRefreshTokens($data->getEmail());
    $this->anonymize($data);

    $this->em->flush();
  }

  private function archivePublishedOffers(User $user): void
  {
    foreach ($this->providerProfileRepository->findBy(['user' => $user]) as $providerProfile) {
      match ($providerProfile->getProviderType()) {
        ProviderType::HounsingHost => $this->archiveListings($providerProfile->getId()),
        ProviderType::ActivityPartner => $this->archiveActivities($providerProfile->getId()),
        ProviderType::Producer => $this->archiveProducts($providerProfile->getId()),
        ProviderType::EventOrganizer => $this->archiveEvents($providerProfile->getId()),
      };
    }
  }

  private function revokeRefreshTokens(string $email): void
  {
    $tokens = $this->em->getRepository(RefreshToken::class)->findBy(['username' => $email]);
    foreach ($tokens as $token) {
      $this->em->remove($token);
    }
  }

  private function anonymize(User $user): void
  {
    $user->setEmail(sprintf('deleted-%d@plaisir-doc.invalid', $user->getEmail()));
    $user->setFirstName('Utilisateur');
    $user->setLastName('supprimé');
    $user->setPhone(null);
    $user->setAvatarUrl(null);
    $user->setMarketingOptIn(false);
    $user->setPassword(bin2hex(random_bytes(32)));
    $user->setDeletedAt(new DateTimeImmutable());
  }

  private function archiveListings(int $providerProfileId): void
  {
    $listings = $this->listingRepository->createQueryBuilder('l')
      ->andWhere('l.host = :hostId')
      ->andWhere('l.status = :status')
      ->setParameter('hostId', $providerProfileId)
      ->setParameter('status', ListingStatus::Published)
      ->getQuery()
      ->getResult();

    foreach ($listings as $listing) {
      $listing->setStatus(ListingStatus::Archived);
    }
  }
  private function archiveActivities(int $providerProfileId): void
  {
    $activities = $this->activiyRepository->createQueryBuilder('a')
      ->andWhere('a.provider = :providerId')
      ->andWhere('a.status = :status')
      ->setParameter('providerId', $providerProfileId)
      ->setParameter('status', ListingStatus::Published)
      ->getQuery()
      ->getResult();

    foreach ($activities as $activity) {
      $activity->setStatus(ListingStatus::Archived);
    }
  }
  private function archiveProducts(int $providerProfileId): void
  {
    $products = $this->productRepository->createQueryBuilder('p')
      ->andWhere('p.provider = :providerId')
      ->andWhere('p.status = :status')
      ->setParameter('providerId', $providerProfileId)
      ->setParameter('status', ListingStatus::Published)
      ->getQuery()
      ->getResult();

    foreach ($products as $product) {
      $product->setStatus(ListingStatus::Archived);
    }
  }
  private function archiveEvents(int $providerProfileId): void
  {
    $events = $this->eventRepository->createQueryBuilder('e')
      ->andWhere('e.provider = :providerId')
      ->andWhere('e.status = :status')
      ->setParameter('providerId', $providerProfileId)
      ->setParameter('status', ListingStatus::Published)
      ->getQuery()
      ->getResult();

    foreach ($events as $event) {
      $event->setStatus(ListingStatus::Archived);
    }
  }
}