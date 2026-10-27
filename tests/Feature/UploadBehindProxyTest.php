<?php

use Illuminate\Support\Facades\URL;

/**
 * On DigitalOcean TLS ends at the proxy, so Laravel receives plain http plus X-Forwarded-Proto.
 * Livewire's signed upload URL is built with https (forceScheme) and must still validate.
 */
it('accepts the signed upload url when the proxy forwards https', function () {
    URL::forceScheme('https');
    $signed = URL::temporarySignedRoute('livewire.upload-file', now()->addMinutes(5));
    URL::forceScheme(null);

    // What the app server sees: an http request with the forwarded protocol header.
    $internal = preg_replace('#^https://#', 'http://', $signed);

    $response = $this->postJson($internal, [], [
        'X-Forwarded-Proto' => 'https',
        'X-Forwarded-Host' => parse_url($signed, PHP_URL_HOST),
        'X-Forwarded-For' => '203.0.113.10',
    ]);

    // 422 = signature accepted, the empty upload then fails validation; 401 = signature rejected.
    expect($response->status())->not->toBe(401);
});
