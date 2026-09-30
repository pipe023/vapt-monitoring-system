<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class ConfigureDocumentPush extends Command
{
    protected $signature = 'documents:configure-push';

    protected $description = 'Generate missing document push keys in the local .env file';

    public function handle(): int
    {
        $path = app()->environmentFilePath();
        if (! is_file($path) || ! is_writable($path)) {
            $this->error('A writable .env file is required.');

            return self::FAILURE;
        }
        $contents = file_get_contents($path);
        if (config('document-alerts.public_key') || config('document-alerts.private_key')
            || preg_match('/^DOCUMENT_PUSH_(PUBLIC|PRIVATE)_KEY=[ \t]*[^\s\r\n][^\r\n]*\r?$/m', $contents)) {
            $this->error('Push keys already exist. Existing keys will not be replaced.');

            return self::FAILURE;
        }
        $keys = VAPID::createVapidKeys();
        foreach (['DOCUMENT_PUSH_PUBLIC_KEY' => $keys['publicKey'], 'DOCUMENT_PUSH_PRIVATE_KEY' => $keys['privateKey']] as $name => $value) {
            $contents = preg_replace('/^'.preg_quote($name, '/').'=.*\R?/m', '', $contents);
            $contents = rtrim($contents).PHP_EOL.$name.'='.$value.PHP_EOL;
        }
        if (file_put_contents($path, $contents, LOCK_EX) === false) {
            $this->error('Could not save push keys.');

            return self::FAILURE;
        }
        $this->info('Push keys saved to .env. Set DOCUMENT_PUSH_SUBJECT to your contact email (mailto:...) or HTTPS URL, then run php artisan config:clear.');

        return self::SUCCESS;
    }
}
