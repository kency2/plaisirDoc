<?php

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\RegisterInput;
use App\Entity\User;
use App\Service\EmailVerification;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @implements ProcessorInterface<RegisterInput, User>
 */
final class RegisterUserProcessor implements ProcessorInterface
{

  public function __construct(
    private readonly EntityManagerInterface $em,
    private readonly UserPasswordHasherInterface $passwordHasher,
    private readonly EmailVerification $emailVerification
  ) {
  }

  /**
   * @param RegisterInput $data
   */
  public function process($data, Operation $operation, array $uriVariables = [], array $context = []): User
  {
    $user = new User(
      email: $data->email,
      firstName: $data->firstName,
      lastName: $data->lastName,
      cgvAcceptedAt: new DateTimeImmutable()
    );
    $user->setPhone($data->phone);
    $user->setLocale($data->locale);
    $user->setMarketingOptIn($data->marketingOptIn);
    $user->setPassword($this->passwordHasher->hashPassword($user, $data->plainPassword));

    $this->em->persist($user);
    $this->em->flush();

    //  Compte innactif tant que le code reçu par email n'est pas saisi
    $this->emailVerification->sendCode($user);
    return $user;
  }
}