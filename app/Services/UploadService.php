<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;

class UploadService
{
    /**
     * Client ID
     *
     * @var string
    */
    public string $clientId;

    /**
     * Client Secret
     *
     * @var string
    */
    public string $clientSecret;

    /**
     * File/Image path to be uploaded
     *
     * @var UploadedFile
    */
    public $asset;

    public bool $uploaded = false;

    public function __construct(array $data)
    {
        $this->asset = $data['asset'];

        $this->setEncryptedClientId();
    }

    public function handle(): self
    {
        $this->setClientSecret();

        $this->upload();

        return $this;
    }

    private function upload(): void
    {
        $response = Http::acceptJson()
            ->withToken($this->clientSecret)
            ->attach("image", fopen(storage_path('app/public/' . $this->asset->hashName()), 'r'))
            ->post(env('UPLOAD_PATH'));

        if($response->ok()){
            $this->uploaded();
        }
    }

    private function setEncryptedClientId(): void
    {
        $this->clientId = base64_encode(
            Str::random('16') . encrypt(env('CLIENT_ID'))
        );
    }

    private function setClientSecret(): void
    {
        $url = env('CLIENT_PATH') . $this->clientId;

        $response = Http::asJson()
            ->acceptJson()
            ->get($url);

        if($response->ok()){
            $clientSecret = base64_decode($response->json('encryptedClientSecret', $this->clientId));
            $clientSecret = decrypt(Str::substr($clientSecret, 16));

            if(isset($clientSecret)){
                $this->clientSecret = $clientSecret;
                return;
            }
        }

        throw new Exception("Invalid client secret.");
    }

    private function uploaded(): void
    {
        $this->uploaded = true;
    }

    public function isUploaded(): bool
    {
        return $this->uploaded;
    }
}
