<?php

namespace App\Infrastructure\Messaging\Bucket;

use Symfony\Component\Console\Command\Command;

class ClearBucketMessageHandler extends Command
{
    public function __construct()
    {
        parent::__construct('app:clear-bucket');
    }
}
