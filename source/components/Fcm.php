<?php

namespace aksafan\fcm\components;

use aksafan\fcm\builders\StaticBuilderFactory;
use aksafan\fcm\requests\AbstractRequest;
use aksafan\fcm\requests\GroupManagementRequest;
use aksafan\fcm\requests\Request;
use aksafan\fcm\requests\StaticRequestFactory;
use aksafan\fcm\responses\StaticResponseFactory;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionProperty;
use yii\base\Component;

/**
 * Class Fcm.
 */
class Fcm extends Component
{
    /** @var $api  */
    public $apiVersion;

    /** @var $oldApiParams array */
    public $apiParams;

    /**
     * @param string $reason A reason to create request for.
     * Can be: for topic management or for message sending (for default).
     *
     * @return Request|AbstractRequest|GroupManagementRequest
     *
     * @throws \ReflectionException
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function createRequest(string $reason = StaticBuilderFactory::FOR_TOKEN_SENDING): Request
    {
        $this->validateConfigs();
        $request = StaticRequestFactory::build($this->apiVersion, $this->apiParams, $reason);
        $request->setResponse(StaticResponseFactory::build($this->apiVersion, $request));

        return $request;
    }

    /**
     * Validates required params.
     *
     * @throws \ReflectionException
     * @throws InvalidArgumentException
     */
    private function validateConfigs()
    {
        foreach ((new ReflectionClass($this))->getProperties(ReflectionProperty::IS_PUBLIC) as $param) {
            if (! $this->{$param->getName()}) {
                throw new InvalidArgumentException($param->getName().' param must be set.');
            }
        }
    }
}
