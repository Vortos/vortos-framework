<?php

declare(strict_types=1);

namespace Vortos\Messaging\Retry;

/**
 * Determines whether a failed handler should be retried and how long to wait.
 *
 * ConsumerRunner calls shouldRetry() before each attempt and getDelayMs()
 * to calculate the sleep duration. RetryPolicy is read from the consumer
 * definition — each consumer can have independent retry configuration.
 */
final class RetryDecider
{
    public function __construct(
        private RetryDelayCalculator $calculator
    ){
    }

    public function shouldRetry(RetryPolicy $policy, int $attemptNumber):bool
    {
        if($attemptNumber <= $policy->maxAttempts){
            return true;
        }

        return false;
    }

    public function getDelayMs(RetryPolicy $policy, int $attemptNumber): int
    {
        return $this->calculator->calculate($policy, $attemptNumber);
    }
}