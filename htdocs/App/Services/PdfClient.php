<?php declare(strict_types=1);

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class PdfClient
{

    private string $baseUrl = 'http://chrome-pdf:3000/pdf';
    private ?string $token;

    public function __construct(private Client $httpClient)
    {
        $this->httpClient = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 30,
        ]);
    }

    public function setToken(string $token): PdfClient
    {
        $this->token = $token;
        return $this;
    }

    public function generatePdfToFile(string $url, string $filepath, array $options = []): void
    {
        $pdf = $this->generatePdf($url, $options);
        file_put_contents($filepath, $pdf);
    }

    public function generatePdf(string $url, array $options = []): string
    {
        $headers = [];

        if ($this->token !== null) {
            $headers['Authorization'] = 'Bearer ' . $this->token;
        }

        try {
            $response = $this->httpClient->post('/pdf', [
                'headers' => $headers,
                'query' => ['token'=>$this->token],
                'json' => [
                    'url' => $url,
                    'options' => array_merge([
                        'printBackground' => true,
                        'format' => 'A4',
                    ], $options),
                ],
            ]);

            return $response->getBody()->getContents();

        } catch (RequestException $e) {
            throw new \RuntimeException('PDF generation failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }
}
