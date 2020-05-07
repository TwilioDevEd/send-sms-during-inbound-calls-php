<?php
namespace TwilioDevEd;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Factory\AppFactory;
use Twilio\TwiML\VoiceResponse;
use Twilio\Rest\Client;
use Twilio\Exceptions\RestException;

class App
{
  /**
   * Stores an instance of the Slim application.
   *
   * @var \Slim\App
   */
  private $app;

  public function __construct() {
    $app = AppFactory::create();
    $app->post('/answer', function (Request $request, Response $response, array $args) {
      $parsedBody = $request->getParsedBody();
      $caller = $parsedBody['From'];
      $twilioNumber = $parsedBody['To'];
      $this->sendSms($caller, $twilioNumber);

      $twilioResponse = new VoiceResponse();
      $twilioResponse->say('Thanks for calling! We just sent you a text with a clue.', ['voice' => 'alice']);

      $response->getBody()->write(strval($twilioResponse));
      return $response;
    });

    $app->get('/', function (Request $request, Response $response, array $args) {
      $response->getBody()->write("Please configure your Twilio number to use the /answer endpoint");
      return $response;
    });


    $this->app = $app;
  }

  public function sendSms(string $toNumber, string $fromNumber, Client $client = null): bool {
      $accountSid = getenv('ACCOUNT_SID');
      $authToken = getenv('AUTH_TOKEN');
      $client = $client ?? new Client($accountSid, $authToken);

      try {
          $client->messages->create(
              $toNumber,
              [
                  'from' => $fromNumber,
                  'body' => "There's always money in the banana stand.",
              ]
          );
      } catch (RestException $e) {
          if ($e->getStatusCode() == 21614) {
              echo "Uh oh, looks like this caller can't receive SMS messages.";
              return false;
          }
      }
      return true;
  }


  /**
   * Get an instance of the application.
   *
   * @return \Slim\App
   */
  public function get()
  {
      return $this->app;
  }
}