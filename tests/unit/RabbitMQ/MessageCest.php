<?php
/**
 * Created by Chocolife.me.
 * User: User
 * Date: 15.01.2020
 * Time: 10:22
 */

use Chocofamily\PubSub\Adapter\RabbitMQ\Message\OutputMessage;
use Chocofamily\PubSub\Adapter\RabbitMQ\Message\InputMessage;

class MessageCest
{
    /**
     * @dataProvider outputMessageProvider
     *
     * @param UnitTester           $I
     * @param \Codeception\Example $data
     */
    public function tryToTestOutputMessage(\UnitTester $I, \Codeception\Example $data)
    {
        $message = new OutputMessage($data['body'], $data['headers'], $data['params']);

        $I->assertEquals($data['result']['body'], $message->getBody());
        $I->assertEquals($data['result']['headers'], $message->getHeaders());
        $I->assertEquals($data['result']['params'], $message->getParams());

        $payload = $message->getPayload();

        $I->assertTrue($payload instanceof \PhpAmqpLib\Message\AMQPMessage);

        $I->assertEquals($data['result']['body'], json_decode($payload->getBody(), true));

        $properties = $payload->get_properties();
        unset($properties['application_headers']);

        $I->assertEquals($data['result']['params'], $properties);
        $I->assertEquals($data['result']['headers'], $payload->get('application_headers')->getNativeData());
    }

    /**
     * @dataProvider repeatProvider
     *
     * @param UnitTester           $I
     * @param \Codeception\Example $data
     *
     * @throws Exception
     */
    public function tryToRepeatOutputMessage(\UnitTester $I, \Codeception\Example $data)
    {
        /** @var OutputMessage $message */
        $message = \Codeception\Stub::make(OutputMessage::class, [
            'publishAttempts' => $data['repeats'],
        ]);

        $attempts = 0;

        do {
            $attempts++;
        } while ($message->isRepeatable());

        $I->assertEquals($data['repeats'], $attempts - 1);
    }

    /**
     * @dataProvider outputMessageProvider
     *
     * @param UnitTester           $I
     * @param \Codeception\Example $data
     */
    public function tryToTestInputMessage(\UnitTester $I, \Codeception\Example $data)
    {
        $message = new OutputMessage($data['body'], $data['headers'], $data['params']);

        $input = new InputMessage($message->getPayload());

        $I->assertEquals($data['result']['body'], $input->getBody());
        $I->assertEquals($data['result']['headers'], $input->getHeaders());
        $I->assertEquals($data['result']['params'], $input->getParams());
    }

    /**
     * @dataProvider repeatProvider
     *
     * @param UnitTester           $I
     * @param \Codeception\Example $data
     *
     * @throws Exception
     */
    public function tryToRepeatInputMessage(\UnitTester $I, \Codeception\Example $data)
    {
        $repeats = $data['repeats'];

        /** @var InputMessage $message */
        $message = \Codeception\Stub::make(InputMessage::class, [
            'headers' => ['receive_attempts' => $repeats],
        ]);

        $attempts = 0;

        do {
            $isRepeatable = $message->isRepeatable();

            $attempts++;

            $message = \Codeception\Stub::make(InputMessage::class, [
                'headers' => ['receive_attempts' => --$repeats],
            ]);

        } while ($isRepeatable);

        $I->assertEquals($data['repeats'], $attempts - 1);
    }

    private function repeatProvider()
    {
        return [
            ['repeats' => 0],
            ['repeats' => 1],
            ['repeats' => 2],
            ['repeats' => 5],
        ];
    }

    private function outputMessageProvider()
    {
        return [
            [
                'body'    => [],
                'headers' => [],
                'params'  => [],
                'result'  => [
                    'body'    => [],
                    'headers' => [],
                    'params'  => [
                        'content_type'  => 'application/json',
                        'delivery_mode' => \PhpAmqpLib\Message\AMQPMessage::DELIVERY_MODE_PERSISTENT,
                    ],
                ],
            ],
            [
                'body'    => [],
                'headers' => [],
                'params'  => [
                    'message_id' => 123,
                ],
                'result'  => [
                    'body'    => [],
                    'headers' => [],
                    'params'  => [
                        'content_type'  => 'application/json',
                        'delivery_mode' => \PhpAmqpLib\Message\AMQPMessage::DELIVERY_MODE_PERSISTENT,
                        'message_id'    => 123,
                    ],
                ],
            ],
            [
                'body'    => [
                    'message' => 'Hello, world',
                ],
                'headers' => [],
                'params'  => [],
                'result'  => [
                    'body'    => [
                        'message' => 'Hello, world',
                    ],
                    'headers' => [],
                    'params'  => [
                        'content_type'  => 'application/json',
                        'delivery_mode' => \PhpAmqpLib\Message\AMQPMessage::DELIVERY_MODE_PERSISTENT,
                    ],
                ],
            ],
            [
                'body'    => [],
                'headers' => [],
                'params'  => [
                    'content_type'  => 'application/xml',
                    'delivery_mode' => \PhpAmqpLib\Message\AMQPMessage::DELIVERY_MODE_NON_PERSISTENT,
                ],
                'result'  => [
                    'body'    => [],
                    'headers' => [],
                    'params'  => [
                        'content_type'  => 'application/xml',
                        'delivery_mode' => \PhpAmqpLib\Message\AMQPMessage::DELIVERY_MODE_NON_PERSISTENT,
                    ],
                ],
            ],
            [
                'body'    => [],
                'headers' => [
                    'X-User' => 123,
                ],
                'params'  => [],
                'result'  => [
                    'body'    => [],
                    'headers' => [
                        'X-User' => 123,
                    ],
                    'params'  => [
                        'content_type'  => 'application/json',
                        'delivery_mode' => \PhpAmqpLib\Message\AMQPMessage::DELIVERY_MODE_PERSISTENT,
                    ],
                ],
            ],
        ];
    }
}
