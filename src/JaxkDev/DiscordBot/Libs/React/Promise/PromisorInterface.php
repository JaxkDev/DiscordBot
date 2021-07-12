<?php

namespace JaxkDev\DiscordBot\Libs\React\Promise;

interface PromisorInterface
{
    /**
     * Returns the promise of the deferred.
     *
     * @return PromiseInterface
     */
    public function promise(): PromiseInterface;
}
