<?php

declare(strict_types=1);

namespace App\UI\Admin\Report;

use App\Model\Database\Entity\Databot;
use App\Model\Database\Repository\DatabotRepository;
use App\Services\DatabotsResultService;
use App\UI\Base\SecuredPresenter;
use Doctrine\ORM\EntityManagerInterface;
use Nette\Application\Responses\TextResponse;

final class ReportPresenter extends SecuredPresenter
{
    /** @inject  */ public DatabotsResultService $databotsService;
    /** @inject  */ public EntityManagerInterface $entityManager;

    public function actionDatabotStatusRaw(): void
    {
        $path = '';
        $url = rtrim($this->appConfiguration->getDatabotBasePath(), '/').'/'.ltrim($path, '/');
        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'ignore_errors' => true,
            ],
        ]);

        $content = @file_get_contents($url, false, $context);

        if (false === $content) {
            $this->error("Unable fetch $url", 502);
        }

        $contentType = 'text/html; charset=UTF-8';
        if (null !== http_get_last_response_headers()) {
            foreach (http_get_last_response_headers() as $header) {
                if (0 === stripos($header, 'Content-Type:')) {
                    $contentType = trim(substr($header, strlen('Content-Type:')));
                    break;
                }
            }
        }

        $this->getHttpResponse()->setContentType($contentType);
        $this->sendResponse(new TextResponse($content));
    }

    public function actionDatabotStatus(): void
    {
        $path = '';
        $url = rtrim($this->appConfiguration->getDatabotBasePath(), '/').'/'.ltrim($path, '/');
        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'ignore_errors' => true,
            ],
        ]);

        $content = @file_get_contents($url, false, $context);

        if (false === $content) {
            $this->error("Unable fetch $url", 502);
        }

        $data = json_decode($content, true);

        if (null === $data) {
            $this->error("Invalid JSON from $url", 502);
        }

        $this->template->databotData = $data;
    }

    public function renderStats()
    {
        $this->template->sharpness = json_encode($this->databotsService->getStats('sharpness', 2));
        $this->template->contrast = json_encode($this->databotsService->getStats('contrast', 2));
        $this->template->clarity = json_encode($this->databotsService->getStats('clarity', 2));
        $this->template->resolution = json_encode($this->databotsService->getStats('resolution', 2));
        $this->template->brisque_score = json_encode($this->databotsService->getStats('brisque_score', 2));

        $this->template->databot = $this->entityManager->getRepository(Databot::class)->getByName(DatabotRepository::IMAGE_QUALITY);
    }
}
