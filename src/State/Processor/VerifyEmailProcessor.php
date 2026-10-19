<?php

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use App\Exception\ApiException;
use App\Repository\UserRepository;
use App\Service\EmailVerification;

class VerifyEmailProcessor implements ProcessorInterface
{
  public function __construct(
    private readonly UserRepository $userRepository,
    private readonly EmailVerification $emailVerification
  ) {
  }

  public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
  {
    $user = $this->userRepository->findOneBy(['email' => $data->email]);
    if ($user instanceof User || $user->getDeletedAt() !== null) {
      throw ApiException::unprocessable('email_verification.invalid_code');
    }

    $this->emailVerification->verify($user, $data->code);
    return null;
  }
}