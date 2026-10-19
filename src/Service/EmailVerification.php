<?php

namespace App\Service;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Vérification de l'adresse email par code à 6 chiffres (double authentification à l'inscription).
 * Tant que l'adresse n'est pas vérifiée, la connexion est refusée
 * 
 * code valable 15min, 
 * 5 essais max avant de demander à nouveau, 
 * nouvel envoi possible au plus toutes les 60s
 */
final class EmailVerification
{

  public const CODE_TTL_MINUTES = 15;
  public const MAX_ATTEMPTS = 5;
  public const RESEND_DELAY_SECONDS = 60;

  public function __construct(
    private readonly EntityManagerInterface $em,
    private readonly EmailSender $emailSender,
    #[Autowire('%kernel.secret%')]
    private readonly string $secret
  ) {
  }

  /**
   * Génère un nouveau code (remplace le précédent) et l'envoie.
   * @param User $user
   * @return void
   */
  public function sendCode(User $user): void
  {
    $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $user->startEmailVerification(
      $this->hash($user, $code),
      new DateTimeImmutable(sprintf('+%d minutes', self::CODE_TTL_MINUTES))
    );
    $this->em->flush();

    $this->emailSender->send(
      $user,
      'email_verification',
      [
        'code' => $code,
        'minutes' => self::CODE_TTL_MINUTES,
      ]
    );
  }

  private function hash(User $user, string $code): string
  {
    return hash_hmac('sha256', $user->getId() . '|' . $code, $this->secret);
  }
}