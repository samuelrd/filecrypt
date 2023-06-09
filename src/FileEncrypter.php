<?php

namespace Samuelrd\FileCrypt;

use Exception;
use Illuminate\Support\Str;
use RuntimeException;

class FileEncrypter
{
    /**
     * Number of blocks that should be read from the source file.
     */
    protected const FILE_ENCRYPTION_BLOCKS = 255;

    /**
     * Map the supported encryption algorithms with their correct key length
     */
    protected const SUPPORTED_ENCRYPTION_ALGORITHMS = [
        'AES-128-CBC' => 16,
        'AES-256-CBC' => 32
    ];

    /**
     * Encryption key.
     */
    protected string $key;

    /**
     * Algorithm used for encryption.
     */
    protected string $algorithm;

    /**
     *
     * @throws \RuntimeException
     */
    public function __construct(string $key, string $algorithm = 'AES-128-CBC')
    {
        // If the key starts with "base64:", we will need to decode the key before passing it to the encrypter.
        if (Str::startsWith($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        if (!static::validAlgorithm($algorithm)) {
            throw new RuntimeException('Only AES-128-CBC or AES-256-CBC are supported.');
        }

        if (!static::validKey($key, $algorithm)) {
            throw new RuntimeException('Make sure the encription key is the right length.');
        }

        $this->key = $key;
        $this->algorithm = $algorithm;
    }

    public static function validAlgorithm(string $algorithm): bool
    {
        return in_array($algorithm, array_keys(self::SUPPORTED_ENCRYPTION_ALGORITHMS));
    }

    public static function validKey(string $key, string $algorithm): bool
    {
        $length = mb_strlen($key, '8bit');

        return (static::validAlgorithm($algorithm) 
            && self::SUPPORTED_ENCRYPTION_ALGORITHMS[$algorithm] == $length);
    }

    /**
     * Encrypts the source file and saves the result in a new file.
     *
     * @param string $sourcePath Path to file that should be encrypted
     * @param string $targetPath Path where the encryped file should be written to.
     * @return bool
     * @throws Exception
     */
    public function encrypt(string $sourcePath, string $targetPath): bool
    {
        $targetFileHandler = $this->openTargetFile($targetPath);
        $sourceFileHandler = $this->openSourceFile($sourcePath);

        // Start with the initialization vector
        $iv = openssl_random_pseudo_bytes(16);
        fwrite($targetFileHandler, $iv);

        while (!feof($sourceFileHandler)) {
            $plaintext = fread($sourceFileHandler, 16 * self::FILE_ENCRYPTION_BLOCKS);
            $encryptedText = openssl_encrypt($plaintext, $this->algorithm, $this->key, OPENSSL_RAW_DATA, $iv);

            // Use the first 16 bytes of the encryptedText as the next initialization vector
            $iv = substr($encryptedText, 0, 16);
            fwrite($targetFileHandler, $encryptedText);
        }

        fclose($sourceFileHandler);
        fclose($targetFileHandler);

        return true;
    }

    /**
     * Decrypts the source file and saves the result in a new file.
     *
     * @param string $sourcePath Path to file that should be decrypted
     * @param string $targetPath Path where the decryped file should be written to.
     * @return bool
     * @throws Exception
     */
    public function decrypt(string $sourcePath, string $targetPath): bool
    {
        $targetFileHandler = $this->openTargetFile($targetPath);
        $sourceFileHandler = $this->openSourceFile($sourcePath);

        // Get the initialization vector from the beginning of the file
        $iv = fread($sourceFileHandler, 16);

        while (! feof($sourceFileHandler)) {
            // We have to read one block more for decrypting than for encrypting because of the initialization vector
            $encryptedText = fread($sourceFileHandler, 16 * (self::FILE_ENCRYPTION_BLOCKS + 1));
            $plaintext = openssl_decrypt($encryptedText, $this->algorithm, $this->key, OPENSSL_RAW_DATA, $iv);

            if ($plaintext === false) {
                throw new Exception('Decryption failed');
            }

            // Get the first 16 bytes of the encryptedText as the next initialization vector
            $iv = substr($encryptedText, 0, 16);
            fwrite($targetFileHandler, $plaintext);
        }

        fclose($sourceFileHandler);
        fclose($targetFileHandler);

        return true;
    }

    /**
     * @throws Exception
     */
    protected function openTargetFile($targetPath)
    {
        if (($targetFileHandler = fopen($targetPath, 'w')) === false) {
            throw new Exception('Cannot open file for writing');
        }

        return $targetFileHandler;
    }

    /**
     * @throws Exception
     */
    protected function openSourceFile($sourcePath)
    {
        if (($sourceFileHandler = fopen($sourcePath, 'r')) === false) {
            throw new Exception('Cannot open file for reading');
        }

        return $sourceFileHandler;
    }
}