<?php

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\ChangePasswordInput;
use App\Entity\RefreshToken;
use App\Entity\User;
use App\Service\EmailSender;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Changement de mot de passe depuis « Mon compte » (l'ancien est vérifié par la validation). Toutes
 * les sessions sont fermées (refresh tokens supprimés : le front se reconnecte avec le nouveau mot
 * de passe) et un e-mail prévient le titulaire, pour qu'un changement non voulu ne passe pas inaperçu.
 *
 * @implements ProcessorInterface<ChangePasswordInput, null>
 */
class ChangePasswordProcessor implements ProcessorInterface
{
  public function __construct(
    private readonly EntityManagerInterface $em,
    private readonly UserPasswordHasherInterface $passwordHasher,
    private readonly Security $security,
    private readonly EmailSender $emailSender
  ) {
  }

  /**
   * @param ChangePasswordInput $data
   */
  public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
  {
    $user = $this->security->getUser();
    if (!$user instanceof User) {
      throw new AccessDeniedHttpException();
    }

    $user->setPassword($this->passwordHasher->hashPassword($user, $data->newPassword));
    $tokensRefreshByUser = $this->em->getRepository(RefreshToken::class)->findBy(['username' => $user->getEmail()]);
    foreach ($tokensRefreshByUser as $token) {
      $this->em->remove($token);
    }

    $this->em->flush();
    $this->emailSender->send($user, 'password_changed', [], '/forgot-password');
    return null;
  }
}