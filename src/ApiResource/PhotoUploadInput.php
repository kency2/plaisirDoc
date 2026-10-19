<?php

namespace App\ApiResource;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

//  Upload d'une photo d'offre - multipart/form-data  - champ: file et altText
class PhotoUploadInput
{

  #[Assert\NotNull(message: 'photo.file_required')]
  #[Assert\Image(
    maxSize: '10M',
    mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
    mimeTypesMessage: 'photo.file.invalid_type',
    maxWidth: 8000,
    maxHeight: 8000
  )]
  public ?UploadedFile $file = null;

  #[Assert\Length(max: 200)]
  public ?string $altText = null;
}
