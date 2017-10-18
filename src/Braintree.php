<?php

namespace Shield\Braintree;

use Braintree_Configuration;
use Braintree_Exception_InvalidSignature;
use Braintree_WebhookNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Shield\Shield\Contracts\Service;

/**
 * Class Service
 *
 * @package \Shield\Braintree
 */
class Braintree implements Service
{
    public function verify(Request $request, Collection $config): bool
    {
        $this->configure($config);

        try {
            Braintree_WebhookNotification::parse($request->bt_signature, $request->bt_payload);
        } catch (Braintree_Exception_InvalidSignature $exception) {
            return false;
        }

        return true;
    }

    protected function configure(Collection $config)
    {
        Braintree_Configuration::environment($config->get('environment'));
        Braintree_Configuration::merchantId($config->get('merchant_id'));
        Braintree_Configuration::publicKey($config->get('public_key'));
        Braintree_Configuration::privateKey($config->get('private_key'));
    }

    public function headers(): array
    {
        return [];
    }
}
