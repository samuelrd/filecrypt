<?php

namespace Samuelrd\FileCrypt;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileCrypt
{
    /**
     * The storage disk.
     */
    protected string $disk;

    /**
     * The encryption key.
     */
    protected string $key;

    /**
     * The algorithm used for encryption.
     */
    protected string $algorithm;

    public function __construct()
    {
        $this->disk = config('file-vault.disk');
        $this->key = config('file-vault.key');
        $this->algorithm = config('file-vault.algorithm');
    }

    public function disk(string $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    public function key($key): static
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Create a new encryption key for the given algorithm.
     *
     * @throws \Exception
     */
    public static function generateKey(): string
    {
        return random_bytes(config('file-vault.algorithm') === 'AES-128-CBC' ? 16 : 32);
    }

    /**
     * Encrypt the passed file and saves the result in a new file with '.enc' suffix.
     *
     * @param string $sourceFile Path to file that should be encrypted, relative to the storage disk specified
     * @param string|null $targetFile Path where the encrypted file should be written to, relative to the storage disk specified
     * @param bool $deleteSource If false, the source file is kept after encrypting.
     * @return $this
     * @throws \Exception
     */
    public function encrypt(string $sourceFile, ?string $targetFile = null, bool $deleteSource = true): static
    {

        if ($targetFile === null) {
            $targetFile = "{$sourceFile}.enc";
        }

        $sourcePath = $this->getFilePath($sourceFile);
        $targetPath = $this->getFilePath($targetFile);
        $encrypter = new FileEncrypter($this->key, $this->algorithm);

        // If encryption is successful, delete the source file
        if ($encrypter->encrypt($sourcePath, $targetPath) && $deleteSource) {
            Storage::disk($this->disk)->delete($sourceFile);
        }

        return $this;
    }

    /**
     * Decrypt the passed file and saves the result in a new file, removing the '.enc' suffix
     *
     * @param string $sourceFile Path to file that should be decrypted
     * @param string|null $targetFile Path where the decrypted file should be written to.
     * @param bool $deleteSource if false, the source file is kept after encrypting.
     * @return $this
     * @throws \Exception
     */
    public function decrypt(string $sourceFile, ?string $targetFile = null, bool $deleteSource = true): static
    {

        if ($targetFile === null) {
            $targetFile = Str::endsWith($sourceFile, '.enc')
                ? Str::replaceLast('.enc', '', $sourceFile)
                : $sourceFile . '.dec';
        }

        $sourcePath = $this->getFilePath($sourceFile);
        $targetPath = $this->getFilePath($targetFile);

        $encrypter = new FileEncrypter($this->key, $this->algorithm);

        // If decryption is successful, delete the source file
        if ($encrypter->decrypt($sourcePath, $targetPath) && $deleteSource) {
            Storage::disk($this->disk)->delete($sourceFile);
        }

        return $this;
    }

    /**
     * @throws \Exception
     */
    public function streamDecrypt(string $sourceFile): bool
    {
        $sourcePath = $this->getFilePath($sourceFile);
        $encrypter = new FileEncrypter($this->key, $this->algorithm);

        return $encrypter->decrypt($sourcePath, 'php://output');
    }

    protected function getFilePath(string $file): string
    {
        return Storage::disk($this->disk)->path($file);
    }
}
