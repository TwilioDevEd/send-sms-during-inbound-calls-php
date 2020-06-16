<?php
use PHPUnit\Framework\TestCase;
use Twilio\Rest\Client;


class AppTest extends TestCase
{
    protected $app;

    protected function setUp(): void
    {
        $this->app = new TwilioDevEd\App();
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    public function testSendSms() {
        $mockClient = Mockery::mock(Client::class)->makePartial();
        $mockMessages = Mockery::mock();
        $mockClient->messages = $mockMessages;

        $mockMessages
            ->shouldReceive('create')
            ->with(
                'TO',
                [
                    'from' => "FROM",
                    'body' => "There's always money in the banana stand.",
                ]
            )
            ->once();

        $res = $this->app->sendSms("TO", "FROM", $mockClient);
        $this->assertSame($res, true);
    }
}
