<?php

namespace Tests\Unit\Services;

use App\Models\Subscription;
use App\Services\Newsletter\MailchimpService;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MailchimpServiceSdkTest extends TestCase
{
    use RefreshDatabase;

    /** @var MockObject */
    private $clientMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure Mailchimp is enabled for unit tests that exercise SDK calls
    putenv('MAILCHIMP_ENABLED=1');
    putenv('MAILCHIMP_LIST_ID=test_list');
    $_ENV['MAILCHIMP_ENABLED'] = '1';
    $_ENV['MAILCHIMP_LIST_ID'] = 'test_list';
    $_SERVER['MAILCHIMP_ENABLED'] = '1';
    $_SERVER['MAILCHIMP_LIST_ID'] = 'test_list';

    $this->clientMock = new \stdClass();
    }

    public function test_subscribe_calls_sdk()
    {
        $email = 'user@example.com';

        // Create nested mocks for lists namespace
        $lists = new class {
            public $called = false;
            public function setListMember($listId, $hash, $body) {
                $this->called = true;
                return ['id' => 'mc_test_id'];
            }
        };

        // Configure client mock to expose lists property
        $this->clientMock->lists = $lists;

        $service = new MailchimpService($this->clientMock);

        $result = $service->subscribe($email, ['FNAME' => 'Test']);

        $this->assertTrue($result);
        $this->assertTrue($lists->called, 'Expected lists->setListMember to be called');
    }

    public function test_unsubscribe_calls_sdk()
    {
        $email = 'user2@example.com';

        $lists = new class {
            public $called = false;
            public function setListMember($listId, $hash, $body) {
                $this->called = true;
                return ['id' => 'mc_test_id'];
            }
        };

        $this->clientMock->lists = $lists;

        $service = new MailchimpService($this->clientMock);

        $result = $service->unsubscribe($email);

        $this->assertTrue($result);
        $this->assertTrue($lists->called, 'Expected lists->setListMember to be called for unsubscribe');
    }

    public function test_sync_subscription_updates_local_record()
    {
        $sub = Subscription::factory()->create(['email' => 'syncme@example.com', 'mailchimp_status' => 'pending']);

        $lists = new class {
            public $called = false;
            public function setListMember($listId, $hash, $body) {
                $this->called = true;
                return ['id' => 'mc_test_id_sync'];
            }
        };

        $this->clientMock->lists = $lists;

        $service = new MailchimpService($this->clientMock);

        $result = $service->syncSubscription($sub->id);

        $this->assertTrue($result);
        $sub->refresh();
        $this->assertEquals('subscribed', $sub->mailchimp_status);
        $this->assertEquals('mc_test_id_sync', $sub->mailchimp_subscriber_id);
    }
}
