<?php

namespace App\Models;

class InstagramToken
{
    public string $email;
    public string $accessToken;
    public string $tokenType;
    public ?string $expiresAt;
    public ?string $instagramUserId;
    public ?string $instagramUsername;

    public function __construct(
        string $email,
        string $accessToken,
        string $tokenType = 'long_lived',
        ?string $expiresAt = null,
        ?string $instagramUserId = null,
        ?string $instagramUsername = null
    ) {
        $this->email = $email;
        $this->accessToken = $accessToken;
        $this->tokenType = $tokenType;
        $this->expiresAt = $expiresAt;
        $this->instagramUserId = $instagramUserId;
        $this->instagramUsername = $instagramUsername;
    }
}
