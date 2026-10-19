<?php

namespace App\Trait;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

trait DateTrait
{
  #[ORM\Column(name: 'created_at')]
  private \DateTimeImmutable $createdAt;

  #[ORM\Column(name: 'updated_at')]
  private \DateTimeImmutable $updatedAt;

  #[ORM\PrePersist]
  public function initializeDates(): void
  {
    $this->createdAt = new DateTimeImmutable();
    $this->updatedAt = new DateTimeImmutable();
  }

  #[ORM\PreUpdate]
  public function refreshUpdatedAt(): void
  {
    $this->updatedAt = new DateTimeImmutable();
  }

  public function getCreatedAt(): ?\DateTimeImmutable
  {
    return $this->createdAt;
  }

  public function getUpdatedAt(): ?\DateTimeImmutable
  {
    return $this->updatedAt;
  }
}
