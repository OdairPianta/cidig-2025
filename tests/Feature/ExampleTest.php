<?php

it('returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
})->skip('Laravel starter-kit test for a route this app does not have');
