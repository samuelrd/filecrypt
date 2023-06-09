<p align="center">
    <img src="/FileCrypt.png" alt="FileCrypt Logo">
</p>

# Laravel package for file encryption and decryption.

With this package, you can encrypt and decrypt files of any size in your Laravel project. This package uses streams and [CBC encryption](https://en.wikipedia.org/wiki/Block_cipher_mode_of_operation#Cipher_Block_Chaining_(CBC)), encrypting / decrypting a segment of data at a time.

## Installation and usage

This package requires PHP 8.0 and Laravel 10.0 or higher.  To install this package, add the following to the composer.json file:

```
    "repositories":[
        {
            "type": "vcs",
            "url": "https://github.com/samuelrd/filecrypt.git"
        }
    ]
```

and then add it to the require section:

```
    "require": {
        ...
        "samuelrd/filecrypt": "dev-development"
    },
```

## Usage

### Description
This package will automatically register a facade called `FileCrypt`. The `FileCrypt` facade is using the Laravel `Storage` and will allow you to specify a `disk`, just as you would normally do when working with Laravel Storage. All file names/paths that you will have to pass into the package encrypt/decrypt functions are relative to the disk root folder. By default, the `local` disk is used, but you can either specify a different disk each time you call one of `FileCrypt` methods, or you can set the default disk to something else, by publishing this package's config file.

If you want to change the default `disk` or change the `key`/`cipher` used for encryption, you can publish the config file:

```
php artisan vendor:publish --provider="Samuelrd\FileCrypt\FileCryptServiceProvider"
```

This is the contents of the published file:
``` php
return [
    /*
     * The default key used for all file encryption / decryption
     * This package will look for a FILECRYPT_KEY in your env file
     * If no FILECRYPT_KEY is found, then it will use your Laravel APP_KEY
     */
    'key' => env('FILECRYPT_KEY', env('APP_KEY')),

    /*
     * The algorithm used for encryption.
     * Supported options are AES-128-CBC and AES-256-CBC
     */
    'algorithm' => 'AES-256-CBC',

    /*
     * The Storage disk used by default to locate your files.
     */
    'disk' => 'local',
];
```


### Encrypting a file

The `encrypt` method will search for a file, encrypt it and save it in the same directory. By default, it deletes the original file but this can be changed by passing `false` to the `$deleteSource` parameter.

``` php
public function encrypt(string $sourceFile, string $destFile = null, $deleteSource = true)
```

#### Examples:

The following example will search for `file.txt` into the `local` disk, save the encrypted file as `file.txt.enc` and delete the original `file.txt`:
``` php
FileCrypt::encrypt('file.txt');
```

You can also specify a different name for the encrypted file by passing in a second parameter. The following example will search for `file.txt` into the `local` disk, save the encrypted file as `encrypted.txt` and delete the original `file.txt`:
``` php
FileCrypt::encrypt('file.txt', 'encrypted.txt');
```

### Decrypting a file

The `decrypt` method will search for a file, decrypt it and save it in the same directory. By default, it deletes the original file but this can be changed by passing `false` to the `$deleteSource` parameter.

``` php
public function decrypt(string $sourceFile, string $destFile = null, $deleteSource = true)
```

The `decryptCopy` method will search for a file, decrypt it and save it in the same directory, while preserving the encrypted file.

``` php
public function decryptCopy(string $sourceFile, string $destFile = null)
```

#### Examples:

The following example will search for `file.txt.enc` into the `local` disk, save the decrypted file as `file.txt` and delete the encrypted file `file.txt.enc`:
``` php
FileCrypt::decrypt('file.txt.enc');
```

If the file that needs to be decrypted doesn't end with the `.enc` extension, the decrypted file will have the `.dec` extention. The following example will search for `encrypted.txt` into the `local` disk, save the decrypted file as `encrypted.txt.dec` and delete the encrypted file `encrypted.txt`:
``` php
FileCrypt::decrypt('encrypted.txt');
```

You can also specify a different name for the decrypted file by passing in a second parameter. The following example will search for `encrypted.txt` into the `local` disk, save the decrypted file as `decrypted.txt` and delete the original `encrypted.txt`:
``` php
FileCrypt::decrypt('encrypted.txt', 'decrypted.txt');
```

### Streaming a decrypted file

Sometimes you will only want to allow users to download the decrypted file, but you don't need to store the actual decrypted file. For this, you can use the `streamDecrypt` function that will decrypt the file and will write it to the `php://output` stream. You can use the Laravel [`streamDownload` method](https://laravel.com/docs/10.x/responses#streamed-downloads) (available since 5.6) in order to generate a downloadable response:

``` php
return response()->streamDownload(function () {
    FileCrypt::streamDecrypt('file.txt')
}, 'laravel-readme.md');
```

### Using a different key for each file

You may need to use different keys to encrypt your files. You can explicitly specify the key used for encryption using the `key` method.

``` php
FileCrypt::key($encryptionKey)->encrypt('file.txt');
```

Please note that the encryption key must be 16 bytes long for the `AES-128-CBC` cipher and 32 bytes long for the `AES-256-CBC` cipher.

You can generate a key with the correct length (based on the cipher specified in the config file) by using the `generateKey` method:

``` php
$encryptionKey = FileCrypt::generateKey();
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
