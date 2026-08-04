<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Subida de archivos con la extensión decidida por el servidor.
 *
 * Filament ya genera un nombre aleatorio, pero conserva la extensión que traía
 * el archivo. Un JPEG legítimo llamado «payload.html» pasa la validación de
 * tipo —su MIME es image/jpeg— y queda guardado como .html en el disco
 * público: el servidor lo entrega como HTML y se ejecuta en el navegador de
 * cualquier visitante. Con un servidor que mapee .phtml o .pht a PHP deja de
 * ser XSS y pasa a ser ejecución de código.
 *
 * Aquí la extensión sale siempre del MIME validado, nunca del nombre.
 */
class SubidaSegura extends FileUpload
{
    /** MIME admitidos y la extensión con la que se guarda cada uno. */
    public const array EXTENSIONES_POR_MIME = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'application/pdf' => 'pdf',
    ];

    /**
     * Extensión de último recurso. Si el MIME no está en la lista el archivo
     * no debería haber pasado la validación, pero guardarlo con una extensión
     * inerte es más barato que confiar en que así sea.
     */
    public const string EXTENSION_DESCONOCIDA = 'bin';

    protected function setUp(): void
    {
        parent::setUp();

        $this->disk('public')
            ->maxSize(5120)
            ->getUploadedFileNameForStorageUsing(
                static fn (TemporaryUploadedFile $file): string => Str::ulid()
                    .'.'.self::extensionPara($file->getMimeType())
            );
    }

    /** Las cinco imágenes del panel: portadas, logos, fotos y cabeceras. */
    public function imagen(): static
    {
        return $this->image()
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->helperText('JPG, PNG o WebP, máximo 5 MB.');
    }

    /**
     * Los formatos oficiales de la guía normativa. Van al disco privado: se
     * sirven por `GuiaController`, que comprueba que el requisito esté
     * publicado. En el disco público esa comprobación era decorativa, porque
     * el mismo PDF quedaba accesible por /storage sin pasar por ninguna parte.
     */
    public function documentoPdf(): static
    {
        return $this->disk('local')
            ->acceptedFileTypes(['application/pdf'])
            ->helperText('PDF, máximo 5 MB. Se descarga desde la guía con un nombre limpio.');
    }

    public static function extensionPara(?string $mime): string
    {
        return self::EXTENSIONES_POR_MIME[strtolower(trim((string) $mime))] ?? self::EXTENSION_DESCONOCIDA;
    }
}
