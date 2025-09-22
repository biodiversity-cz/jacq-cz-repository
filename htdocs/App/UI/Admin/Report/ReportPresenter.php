<?php declare(strict_types = 1);

namespace App\UI\Admin\Report;

use App\UI\Base\SecuredPresenter;
use Nette\Application\Responses\TextResponse;

final class ReportPresenter extends SecuredPresenter
{

    public function actionDatabotStatus(): void
    {

        $path = '';
        $url = rtrim($this->appConfiguration->getDatabotBasePath(), '/') . '/' . ltrim($path, '/');
        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'ignore_errors' => true,
            ],
        ]);

        $content = @file_get_contents($url, false, $context);

        if ($content === false) {
            $this->error("Unable fetch $url", 502);
        }

        $contentType = 'text/html; charset=UTF-8';
        if (isset($http_response_header)) {
            foreach ($http_response_header as $header) {
                if (stripos($header, 'Content-Type:') === 0) {
                    $contentType = trim(substr($header, strlen('Content-Type:')));
                    break;
                }
            }
        }

        $this->getHttpResponse()->setContentType($contentType);
        $this->sendResponse(new TextResponse($content));
    }
}
