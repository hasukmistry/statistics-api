<?php

namespace App\Response;

class OverTime
{
    private int $reviewCount;
    private float $averageScore;
    private string $dateGroup;

    public function getReviewCount(): int
    {
        return $this->reviewCount;
    }

    public function getAverageScore(): float
    {
        return $this->averageScore;
    }

    public function getDateGroup(): string
    {
        return $this->dateGroup;
    }

    public function setReviewCount(int $reviewCount): self
    {
        $this->reviewCount = $reviewCount;

        return $this;
    }

    public function setAverageScore(float $averageScore): self
    {
        $this->averageScore = $averageScore;

        return $this;
    }

    public function setDateGroup(int $dateRange): self
    {
        $group = 'month';

        if ($dateRange > 0 && $dateRange <= 20) {
            $group = 'day';
        }

        if ($dateRange > 30 && $dateRange <= 89) {
            $group = 'calendar-week';
        }

        if ($dateRange > 90) {
            $group = 'month';
        }

        $this->dateGroup = $group;

        return $this;
    }
}