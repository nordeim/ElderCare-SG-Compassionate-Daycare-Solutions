<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class MailchimpHealthCommandTest extends TestCase
{
    public function test_disabled_returns_2()
    {
        putenv('MAILCHIMP_ENABLED=0');
        $_ENV['MAILCHIMP_ENABLED'] = '0';
        $_SERVER['MAILCHIMP_ENABLED'] = '0';

        $exit = Artisan::call('mailchimp:health');

        $this->assertEquals(2, $exit);
    }

    public function test_missing_env_returns_3()
    {
        putenv('MAILCHIMP_ENABLED=1');
        $_ENV['MAILCHIMP_ENABLED'] = '1';
        $_SERVER['MAILCHIMP_ENABLED'] = '1';

        // Clear other required envs
        putenv('MAILCHIMP_API_KEY');
        putenv('MAILCHIMP_SERVER');
        putenv('MAILCHIMP_LIST_ID');

        $exit = Artisan::call('mailchimp:health');

        $this->assertEquals(3, $exit);
    }

    public function test_sdk_missing_returns_4()
    {
        putenv('MAILCHIMP_ENABLED=1');
        $_ENV['MAILCHIMP_ENABLED'] = '1';
        $_SERVER['MAILCHIMP_ENABLED'] = '1';

        putenv('MAILCHIMP_API_KEY=dummy');
        $_ENV['MAILCHIMP_API_KEY'] = 'dummy';
        $_SERVER['MAILCHIMP_API_KEY'] = 'dummy';

        putenv('MAILCHIMP_SERVER=dummy');
        $_ENV['MAILCHIMP_SERVER'] = 'dummy';
        $_SERVER['MAILCHIMP_SERVER'] = 'dummy';

        putenv('MAILCHIMP_LIST_ID=dummy');
        $_ENV['MAILCHIMP_LIST_ID'] = 'dummy';
        $_SERVER['MAILCHIMP_LIST_ID'] = 'dummy';

        // Ensure Mailchimp SDK class is not present in test environment
        if (class_exists('\\MailchimpMarketing\\ApiClient')) {
            $this->markTestSkipped('Mailchimp SDK present in environment; cannot test missing-sdk case');
        }

        $exit = Artisan::call('mailchimp:health');

        $this->assertEquals(4, $exit);
    }
}
