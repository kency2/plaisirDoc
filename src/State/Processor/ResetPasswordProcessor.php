<?php

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\ResetPasswordInput;
use App\Entity\User;
use App\Exception\ApiException;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshToken;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

/**
 * Remplace le mot de passe à partir du jeton reçu par e-mail. Le jeton est à usage unique, et toutes
 * les sessions ouvertes (refresh tokens) sont fermées : si le compte avait été compromis, l'intrus
 * est déconnecté au plus tard à l'expiration de son JWT.
 *
 * @implements ProcessorInterface<ResetPasswordInput, null>
 */
class ResetPasswordProcessor implements ProcessorInterface
{

  public function __construct(
    private readonly EntityManagerInterface $em,
    private readonly ResetPasswordHelperInterface $resetPasswordHelper,
    private readonly UserPasswordHasherInterface $userPasswordHasher,
  ) {

  }

  /**
   * @param ResetPasswordInput $data
   */
  public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): null
  {
    try {
      $user = $this->resetPasswordHelper->validateTokenAndFetchUser($data->token);
    } catch (ResetPasswordExceptionInterface) {
      throw ApiException::unprocessable('auth.reset_link_invalid');
    }

    if (!$user instanceof User || $user->getDeletedAt()) {
      throw ApiException::unprocessable('auth.reset_link_invalid');
    }

    $this->resetPasswordHelper->removeResetRequest($data->token);

    $user->setPassword($this->userPasswordHasher->hashPassword($user, $data->plainPassword));

    foreach ($this->em->getRepository(RefreshToken::class)->findBy(['username' => $user->getEmail()]) as $token) {
      $this->em->remove($token);
    }

    $this->em->flush();

    return null;
  }
}