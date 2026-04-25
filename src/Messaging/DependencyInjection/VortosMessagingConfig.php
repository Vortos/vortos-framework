<?php

declare(strict_types=1);

namespace Vortos\Messaging\DependencyInjection;

final class VortosMessagingConfig
{
    private array $driverConfig = [];

    public function driver(): DriverConfig
    {
        return new DriverConfig($this->driverConfig);
    }

    public function toArray(): array
    {
        return ['driver' => $this->driverConfig];
    }
}
