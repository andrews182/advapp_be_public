<?php
declare(strict_types=1);

namespace App\Utility;

use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\Filesystem\Folder;
use SodiumException;

/**
 * Custom Encrypt Utility (Libsodium PHP)
 *
// * @method generateKey()
 */
class EncryptUtility
{
    private int $custom_chunk_size;
    private string $file_tmp_path;
    private string $file_enc_path;
    private $key;

    /**
     * Utility Construct
     */
    public function __construct()
    {
        // Set chunk size
        $this->custom_chunk_size = 8192;

        // File path
        $this->file_tmp_path = Configure::read('custom.file_storage') . 'tmp/';
        $this->file_enc_path = Configure::read('custom.file_storage') . 'encrypted/';

        // Create folders and key if not present
        $this->createFoldersAndKey();
    }

    /**
     * Create out folders if they don't exist
     *
     * @return void
     */
    public function createFoldersAndKey()
    {
        // Tmp
        if (!is_dir($this->file_tmp_path)) {
            new Folder($this->file_tmp_path, true, 0755);
        }
        // Encrypted
        if (!is_dir($this->file_enc_path)) {
            new Folder($this->file_enc_path, true, 0755);
        }
    }

    /**
     * Fetch our temporary path
     *
     * @return string
     */
    public function getTmpPath(): string
    {
        return $this->file_tmp_path;
    }

    /**
     * Fetch our path for encrypted files
     *
     * @return string
     */
    public function getEncPath(): string
    {
        return $this->file_enc_path;
    }

    /**
     * Destroy the original file
     *
     * @param string $file The filename
     * @return bool
     */
    public function destroyTmp(string $file): bool
    {
        return unlink($this->getTmpPath() . $file);
    }

    /**
     * Destroy encrypted
     *
     * @param string $file The filename*
     * @return bool
     */
    public function destroyEncrypted(string $file): bool
    {
        return unlink($this->getEncPath() . $file);
    }

    /**
     * Generate our key to a file
     * Must be stored securely to retrieve files
     *
     * @return string
     * @throws \Exception
     */
    public function generateKey(): string
    {
        $this->key = bin2hex(random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES));

        return $this->key;// 256 bit
    }

    /**
     * Read a key
     *
     * @return string The key
     * @throws \Exception
     */
    public function readKey(): string
    {
        return $this->generateKey();
    }

    /**
     * Encrypt a message
     *
     * @param string $message - message to encrypt
     * @param string $key - encryption key
     * @return string Encrypted string
     * @throws \Exception
     */
    public function encryptString(string $message, string $key): string
    {
        $nonce = random_bytes(
            SODIUM_CRYPTO_SECRETBOX_NONCEBYTES
        );

        //Converts hexadecimal data to binary
        $key = hex2bin($key);
        $cipher = base64_encode(
            $nonce .
                sodium_crypto_secretbox(
                    $message,
                    $nonce,
                    $key
                )
        );

        sodium_memzero($message);
        sodium_memzero($key);

        return $cipher;
    }

    /**
     * Decrypt a message
     *
     * @param string $encrypted - message encrypted with safeEncrypt()
     * @param string $key - encryption key
     * @return string
     * @throws SodiumException
     */
    public function decryptString(string $encrypted, string $key): string
    {
        $decoded = base64_decode($encrypted);
        if ($decoded === false) {
            throw new Exception('The encoding failed');
        }
        if (mb_strlen($decoded, '8bit') < SODIUM_CRYPTO_SECRETBOX_NONCEBYTES + SODIUM_CRYPTO_SECRETBOX_MACBYTES) {
            throw new Exception('The message was truncated');
        }
        $nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
        $ciphertext = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');

        //Converts hexadecimal data to binary
        $key = hex2bin($key);
        $plain = sodium_crypto_secretbox_open(
            $ciphertext,
            $nonce,
            $key
        );
        if ($plain === false) {
            throw new Exception('The message was tampered with in transit');
        }
        sodium_memzero($ciphertext);
        sodium_memzero($key);

        return $plain;
    }

    /**
     * Encrypt a file
     *
     * @ref https://stackoverflow.com/q/11716047
     * @param string $inputFilename Input filename
     * @param string $outputFilename Output filename
     * @param string $key Our 256 bit key
     * @return bool
     * @throws SodiumException
     */
    public function encryptFile(string $inputFilename, string $outputFilename, string $key): bool
    {
        // Check exists and not empty
        if (!file_exists($this->file_tmp_path . $inputFilename) || !filesize($this->file_tmp_path . $inputFilename)) {
            throw new Exception('Input file not found or is empty.');
        }

        $iFP = fopen($this->file_tmp_path . $inputFilename, 'rb');
        $oFP = fopen($this->file_enc_path . $outputFilename, 'wb');

        //Converts hexadecimal data to binary
        $key = hex2bin($key);
        [$state, $header] = sodium_crypto_secretstream_xchacha20poly1305_init_push($key);

        fwrite($oFP, $header, 24); // Write the header first:
        $size = fstat($iFP)['size'];
        for ($pos = 0; $pos < $size; $pos += $this->custom_chunk_size) {
            $chunk = fread($iFP, $this->custom_chunk_size);
            $encrypted = sodium_crypto_secretstream_xchacha20poly1305_push($state, $chunk);
            fwrite($oFP, $encrypted, $this->custom_chunk_size + 17);
            sodium_memzero($chunk);
        }

        fclose($iFP);
        fclose($oFP);

        return true;
    }

    /**
     * Decrypt a file
     *
     * @ref https://stackoverflow.com/q/11716047
     * @param string $inputFilename Input filename
     * @param string $outputFilename Output filename
     * @param string $key Our 256 bit key
     * @return bool
     * @throws SodiumException
     */
    public function decryptFile(string $inputFilename, string $outputFilename, string $key): bool
    {
        // Check exists and not empty
        if (!file_exists($this->file_enc_path . $inputFilename) || !filesize($this->file_enc_path . $inputFilename)) {
            throw new Exception('Input file not found or is empty.');
        }

        // Files
        $iFP = fopen($this->file_enc_path . $inputFilename, 'rb');
        $oFP = fopen($this->file_tmp_path . $outputFilename, 'wb');

        // Decrypt
        $header = fread($iFP, 24);
        //Converts hexadecimal data to binary
        $key = hex2bin($key);
        $state = sodium_crypto_secretstream_xchacha20poly1305_init_pull($header, $key);
        $size = fstat($iFP)['size'];
        $readChunkSize = $this->custom_chunk_size + 17;

        for ($pos = 24; $pos < $size; $pos += $readChunkSize) {
            $chunk = fread($iFP, $readChunkSize);
            [$plain, $tag] = sodium_crypto_secretstream_xchacha20poly1305_pull($state, $chunk);
            fwrite($oFP, $plain, $this->custom_chunk_size);

            if (!$plain) {
                throw new Exception('Key may be incorrect.');
            }

            sodium_memzero($plain);
        }
        fclose($iFP);
        fclose($oFP);

        return true;
    }
}
