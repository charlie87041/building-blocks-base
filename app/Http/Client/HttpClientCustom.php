<?php

namespace App\Http\Client;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Yethee\Tiktoken\EncoderProvider;

class HttpClientCustom
{
    public Response $response;
    protected $payload;
    public array $costs;

    /**
     * @param Response $response
     */
    public function __construct(Response $response, string $payload)
    {
        $this->response = $response;
        $this->payload = $payload;
        $this->costs = [];
    }

    public function logIfNotSuccessful($header) :static
    {
        if (!$this->response->successful()) {
            logger()->error($header, [
                'status' => $this->response->status(),
                'body' => $this->response->body(),
            ]);
        }
        return $this;
    }
    public function trackCost() :static
    {
        $provider = new EncoderProvider();
        $encoder = $provider->getForModel('gpt-4o');

        $request = $this->payload;
        $tokensRequest = $encoder->encode($request);

        $response = $this->response->body();
        $tokensResponse = $encoder->encode($response);

        $this->costs['request'] = count($tokensRequest);
        $this->costs['response'] = count($tokensResponse);

        return $this;
    }

    public function getCosts()
    {
        return $this->costs;
    }

    public function isSucessfull() :bool
    {
        return $this->response->successful();
    }

    public function jsonData($key=null) :array
    {
        return $this->response->json($key);
    }


    public static function post($url,array $payload,$timeout = 60, $headers = [], $method = 'post')
    {


        if (!in_array($method, ['post', 'put', 'get', 'delete']))
            $method = 'post';
       $r = Http::withHeaders($headers)
            ->timeout($timeout)
            ->$method($url, $payload);
       return new static($r, self::extractMessageFromPayload($payload));
    }

    protected static function extractMessageFromPayload( array $payload) :string
    {
        $messages = array_map(fn($row)=>$row['content'], $payload['messages']);
        return implode('. ', $messages);
    }

}
