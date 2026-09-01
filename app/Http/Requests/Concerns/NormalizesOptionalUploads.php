<?php

namespace App\Http\Requests\Concerns;

use Symfony\Component\HttpFoundation\File\UploadedFile;

trait NormalizesOptionalUploads
{
    /**
     * Drop an optional file input that the user left empty.
     *
     * A browser always submits <input type="file"> — even when nothing was
     * chosen — as an empty part. Most of the time PHP reports UPLOAD_ERR_NO_FILE
     * and Symfony turns that into null, but when it does not (or when a client
     * sends the part some other way) the empty upload survives as an invalid
     * UploadedFile. Laravel then fails the field with "failed to upload", which
     * blocks the whole form — including a request that only wanted to *remove*
     * the existing image.
     *
     * Removing the key here lets the `nullable` rule do its job. Genuine upload
     * failures such as UPLOAD_ERR_INI_SIZE are deliberately left in place so
     * they still surface as a real error.
     */
    protected function dropEmptyUpload(string $key): void
    {
        if (! $this->files->has($key)) {
            return;
        }

        $file = $this->files->get($key);

        if ($file === null) {
            $this->files->remove($key);

            return;
        }

        if ($file instanceof UploadedFile && $file->getError() === UPLOAD_ERR_NO_FILE) {
            $this->files->remove($key);
        }
    }
}
