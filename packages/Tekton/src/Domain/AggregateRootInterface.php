<?php

namespace Fortizan\Tekton\Domain;

interface AggregateRootInterface
{
    public function releaseEvents():array;
}