<?php

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\EmailVerification;

class ResendVerificationProcessor implements ProcessorInterface
{

  public function __construct(
    private readonly UserRepository $userRepository,
    private readonly EmailVerification $emailVerification 
  )
  {
  }
  public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
  {
    $user = $this->userRepository->findOneBy(['email' => $data->email]);
    if($user instanceof User && $user->getDeletedAt() !== null){
      $this->emailVerification->resendCode($user);
    }      

    return null;
  }
}