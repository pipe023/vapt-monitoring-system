<?php

namespace Tests\Feature;

use Tests\TestCase;

class ConfigureDocumentPushTest extends TestCase
{
    public function test_configuration_generates_keys_without_replacing_existing_keys(): void
    {
        config(['document-alerts.public_key' => null, 'document-alerts.private_key' => null]);
        $path = tempnam(sys_get_temp_dir(), 'document-push-test-');
        $this->app->useEnvironmentPath(dirname($path))->loadEnvironmentFrom(basename($path));
        try {
            file_put_contents($path, "APP_NAME=Test\r\nDOCUMENT_PUSH_PUBLIC_KEY=\r\nDOCUMENT_PUSH_PRIVATE_KEY=\r\n");
            $this->artisan('documents:configure-push')->assertSuccessful();
            $contents = file_get_contents($path);
            $this->assertMatchesRegularExpression('/DOCUMENT_PUSH_PUBLIC_KEY=[A-Za-z0-9_-]{87}/', $contents);
            $this->assertMatchesRegularExpression('/DOCUMENT_PUSH_PRIVATE_KEY=[A-Za-z0-9_-]{43}/', $contents);
            $this->assertStringContainsString('APP_NAME=Test', $contents);
            $this->artisan('documents:configure-push')->assertFailed();
            $this->assertSame($contents, file_get_contents($path));
        } finally {
            unlink($path);
        }
    }
}
