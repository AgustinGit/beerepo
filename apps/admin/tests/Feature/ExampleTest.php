<?php

declare(strict_types=1);

it('returns a successful response from the homepage', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
