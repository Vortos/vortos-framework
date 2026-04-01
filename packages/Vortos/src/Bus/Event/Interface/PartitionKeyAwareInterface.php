<?php

namespace Vortos\Bus\Event\Interface;

interface PartitionKeyAwareInterface
{
    public function getPartitionKey():string;
}