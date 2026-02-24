<?php

namespace App\Models;

class InstagramMetrics
{
    public string $email;
    public int $followersCount;
    public int $followingCount;
    public int $mediaCount;
    public float $engagementRate;

    public function __construct(
        string $email,
        int $followersCount = 0,
        int $followingCount = 0,
        int $mediaCount = 0,
        float $engagementRate = 0.0
    ) {
        $this->email = $email;
        $this->followersCount = $followersCount;
        $this->followingCount = $followingCount;
        $this->mediaCount = $mediaCount;
        $this->engagementRate = $engagementRate;
    }
}
