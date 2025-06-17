<?php

namespace App\Infrastructure\Messaging\Catalogue;

use Symfony\Component\Console\Command\Command;

class UpdateQuantityMessageHandler extends Command
{
    public function __construct(
    ) {
        parent::__construct('app:update-quantity');
    }
}
