<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';
use Twilio\TwiML\VoiceResponse;
use Twilio\Rest\Client;
use Twilio\Exceptions\RestException;

$app = new \Slim\App;
$app->map(['GET', 'POST'], '/answer', function (Request $request, Response $response, array $args) {
    $parsedBody = $request->getParsedBody();
    $caller = $parsedBody['From'];
    $twilioNumber = $parsedBody['To'];
    sendSms($caller, $twilioNumber);

    $twilioResponse = new VoiceResponse();
    $twilioResponse->say('Thanks for calling! We just sent you a text with a clue.', ['voice' => 'alice']);

    $response->getBody()->write($twilioResponse);
    return $response;
});

function sendSms($toNumber, $fromNumber) {
    $accountSid = getenv('ACCOUNT_SID');
    $authToken = getenv('AUTH_TOKEN');
    $client = new Client($accountSid, $authToken);

    try {
        $client->messages->create(
            $toNumber,
            array(
                'from' => $fromNumber,
                'body' => "There's always money in the banana stand.",
            )
        );
    } catch (RestException $e) {
        if ($e->getStatusCode() == 21614) {
            echo "Uh oh, looks like this caller can't receive SMS messages.";
        }
    }
}

$app->run();
?>
