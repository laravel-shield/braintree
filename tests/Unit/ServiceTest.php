<?php

namespace Shield\Braintree\Test\Unit;

use Braintree_Configuration;
use Braintree_WebhookNotification;
use Braintree_WebhookTesting;
use PHPUnit\Framework\Assert;
use Shield\Braintree\Braintree;
use Shield\Shield\Contracts\Service;
use Shield\Testing\TestCase;

/**
 * Class ServiceTest
 *
 * @package \Shield\Braintree\Test
 */
class ServiceTest extends TestCase
{
    /**
     * @var Braintree
     */
    protected $service;

    protected function setUp()
    {
        parent::setUp();

        $this->app['config']['shield.services.braintree.options'] = [
            'environment' => 'development',
            'merchant_id' => 'some-merchant-id',
            'public_key' => 'some-public-key',
            'private_key' => 'some-private-key',
        ];

        Braintree_Configuration::environment($this->app['config']['shield.services.braintree.options.environment']);
        Braintree_Configuration::merchantId($this->app['config']['shield.services.braintree.options.merchant_id']);
        Braintree_Configuration::publicKey($this->app['config']['shield.services.braintree.options.public_key']);
        Braintree_Configuration::privateKey($this->app['config']['shield.services.braintree.options.private_key']);

        $this->service = new Braintree;
    }

    /** @test */
    public function it_is_a_service()
    {
        Assert::assertInstanceOf(Braintree::class, new Braintree);
    }

    /** @test */
    public function it_can_verify_a_valid_request()
    {
        $sampleNotification = Braintree_WebhookTesting::sampleNotification(
            Braintree_WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE,
            'my_id'
        );

        $request = $this->request();
        $request->replace($sampleNotification);

        Assert::assertTrue($this->service->verify($request, collect($this->app['config']['shield.services.braintree.options'])));
    }

    /** @test */
    public function it_will_not_verify_a_bad_request()
    {
        $this->app['config']['shield.services.braintree.options.public_key'] = 'invalid-public-key';

        $sampleNotification = Braintree_WebhookTesting::sampleNotification(
            Braintree_WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE,
            'my_id'
        );

        $request = $this->request();
        $request->replace($sampleNotification);

        Assert::assertFalse($this->service->verify($request, collect($this->app['config']['shield.services.braintree.options'])));
    }

    /** @test */
    public function it_has_correct_headers_required()
    {
        Assert::assertArraySubset([], $this->service->headers());
    }
}
