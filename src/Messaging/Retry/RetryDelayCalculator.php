<?php

declare(strict_types=1);

namespace Vortos\Messaging\Retry;

/**
 * Calculates the delay in milliseconds before a given retry attempt.
 *
 * Supports fixed and exponential backoff strategies. Exponential backoff
 * caps at RetryPolicy::$maxDelayMs and optionally applies jitter to
 * prevent thundering herd when multiple consumers fail simultaneously.
 */
final class RetryDelayCalculator
{
    public function calculate(RetryPolicy $policy, int $attemptNumber): int
    {
        if ($policy->backoffStrategy === 'fixed') {
            return $policy->initialDelayMs;
        }

        if ($policy->backoffStrategy === 'exponential') {
            $delay = $policy->initialDelayMs * ($policy->multiplier ** ($attemptNumber - 1));

            $delay = min($delay, $policy->maxDelayMs);

            if ($policy->jitter === true) {
                $delay = $delay + random_int(0, (int)($delay * 0.2));
            }

            $delay = min($delay, $policy->maxDelayMs);

            return (int)$delay;
        }

        return $policy->initialDelayMs;
    }
}
