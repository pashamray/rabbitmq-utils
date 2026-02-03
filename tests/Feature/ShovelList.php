<?php

it('shovel:list command', function () {
    $this->artisan('shovel:list')
      // ->expectsOutput('')
        ->assertExitCode(0);
});
