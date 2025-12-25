<?php

namespace Fortizan\Tekton\Persistence\Contract;

interface PersistenceManagerInterface
{
    public function sourceWriter(): SourceWriterInterface;
    public function sourceReader(): SourceReaderInterface;
    public function projectionReader(): ProjectionReaderInterface;
}