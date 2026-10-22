<?php

// needs constructor arguments
class mod_eta_TaggingCapability extends \Icms\Tests\Fixtures\Capability\TaggingStub
{
    public function __construct(private readonly string $required)
    {
    }
}
