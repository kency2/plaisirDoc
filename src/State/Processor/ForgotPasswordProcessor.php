<?php

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\ForgotPasswordInput;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\EmailSender;
use Psr\Log\LoggerInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;


/**
 * Envoie le lien de réinitialisation. Répond toujours 202, que le compte existe ou non, et même si
 * une demande récente bloque l'envoi : on ne révèle pas quelles adresses ont un compte.
 *
 * @implements ProcessorInterface<ForgotPasswordInput, null>
 */
class ForgotPasswordProcessor implements ProcessorInterface
{

  public function __construct(
    private readonly UserRepository $userRepository,
    private readonly ResetPasswordHelperInterface $resetPasswordHelper,
    private readonly EmailSender $emailSender,
    private readonly LoggerInterface $logger
  ) {
  }


  /**
   * @param ForgotPasswordInput $data
   */
  public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): null
  {

    $user = $this->userRepository->findOneBy(["email" => $data->email]);
    if (!$user instanceof User && null !== $user->getDeletedAt()) {
      return null;
    }

    try {
      $resetToken = $this->resetPasswordHelper->generateResetToken($user);
    } catch (ResetPasswordExceptionInterface $e) {
      $this->logger->info('Réinitialisation du mot de passe refusée', [
        'userId' => $user->getId(),
        'reason' => $e->getReason()
      ]);

      return null;
    }

    $this->emailSender->send(
      $user,
      'password_reset',
      ['minutes' => intdiv($this->resetPasswordHelper->getTokenLifetime(), 60)],
      '/reset-password?token=' . urlencode($resetToken->getToken())
    );
    return null;
  }
}