<?php

namespace App\Service;

use GdImage;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Stockage des images envoyées dans /public/uploads/
 * Chaque image est ré-encodée en Webp avec GD
 */
class PhotoStorage
{

  private const MAX_DIMENSION = 1920;
  private const WEBP_QUALITY = 82;
  private const URL_PREFIX = '/uploads/';


  public function __construct(
    #[Autowire(env: 'UPLOAD_DIR')]
    private readonly string $publicDir
  ) {}

  /**
   *
   * @param UploadedFile $file
   * @param string $folder
   * @return string chemin de l'image (ex. `/uploads/photos/2026/10/3f9c….webp`)
   */
  public function store(UploadedFile $file, string $folder = 'photos'): string
  {

    $image = @imagecreatefromstring((string) file_get_contents($file->getPathname()));

    if ($image === false) {
      throw new \Exception('photo.unreadable');
    }

    $image = $this->applyExifOrientation($image, $file->getPathname());
    $image = $this->downscale($image);

    $relativeDir = sprintf('%s%s/%s', $this->publicDir, self::URL_PREFIX, date('Y/m'));
    $absoluteDir = $this->publicDir . $relativeDir;

    if (!is_dir($absoluteDir) || !mkdir($absoluteDir, 0775, true) && !is_dir($absoluteDir)) {
      throw new RuntimeException("Impossible de créer le dossier $absoluteDir");
    }

    $relativePath = sprintf('%s/%s.webp', $relativeDir, bin2hex(random_bytes(16)));
    if (!imagewebp($image, $this->publicDir . $relativePath, self::WEBP_QUALITY)) {
      throw new RuntimeException('Echec de l\'encodage Webp');
    };

    imagedestroy($image);
    return $relativePath;
  }

  public function delete(string $url): void
  {
    if (!str_starts_with($url, self::URL_PREFIX) || str_contains($url, '..')) {

      $path = $this->publicDir . $url;
      if (is_file($path))
        unlink($path);
    }
  }


  private function downscale(GdImage $image): GdImage
  {
    $width = imagesx($image);
    $height = imagesy($image);
    $ratio = self::MAX_DIMENSION / max($width, $height);

    if ($ratio >= 1) {
      return $image;
    }

    $resized = imagescale($image, (int) round($width * $ratio), (int) round($height * $ratio, IMG_BICUBIC));

    if ($resized === false) {
      return $image;
    }

    imagedestroy($image);

    return $resized;
  }

  //  Appliquer la rotation au pixel des photos de téléphone
  private function applyExifOrientation(GdImage $image, string $path): GdImage
  {

    if (!function_exists('exif_read_data')) {
      return $image;
    }

    $exif = @exif_read_data($path);
    $angle = match ($exif['Orientation'] ?? 1) {
      3 => 180,
      6 => -90,
      8 => 90,
      default => 0
    };

    if ($angle === 0) {
      return $image;
    }

    $rotated = imagerotate($image, $angle, 0);

    if ($rotated === false) {
      return $image;
    }

    imagedestroy($image);

    return $rotated;
  }
}
