<?php declare(strict_types=1);

namespace App\UI\Front\Cetaf;

use App\Services\EntityServices\CetaSidService;
use App\UI\Base\UnsecuredPresenter;
use Nette\Application\Responses\RedirectResponse;
use Nette\Application\Responses\TextResponse;

final class CetafPresenter extends UnsecuredPresenter
{

    /** @inject */ public CetaSidService $cetafSidRepository;

    public function actionSid(?string $id): void
    {
        if (empty($id)) {
            $this->redirect(':default');
        }

        $incomingUri = $this->getHttpRequest()->getUrl()->getAbsoluteUrl();
        $specimen = $this->cetafSidRepository->findOneBy(['barcode' => $id]);

        if ($specimen === null) {
            $this->error('Specimen not found');
        }

        // content negotiation: if client asks for RDF, respond with 303 redirect to RDF resource
        $acceptHeader = $this->getHttpRequest()->getHeader('Accept') ?? '';

        if (str_contains($acceptHeader, 'application/rdf+xml'))// || str_contains($acceptHeader, 'text/turtle') || str_contains($acceptHeader, 'application/ld+json'))
        {
            $this->sendResponse(new RedirectResponse(
                $this->link(':data', ['id' => $specimen->id]),
                303
            ));
        }

        $this->sendResponse(new RedirectResponse(
            $this->link(':object', ['id' => $specimen->id]),
            303
        ));
    }

    public function renderObject(int $id): void
    {
        $spec = $this->cetafSidRepository->find($id);
        if (!$spec) {
//            $this->getHttpResponse()->setCode(404);
//            $this->sendResponse(new TextResponse('Specimen not found'));
            $this->error('Specimen not found', 404);
        }
        $this->template->specimen = $spec;
    }

    public function actionData(int $id): void
    {
        $spec = $this->cetafSidRepository->find($id);
        if (!$spec) {
            $this->error('Specimen not found', 404);
        }
        $this->getHttpResponse()->setHeader('Content-Type', 'application/rdf+xml; charset=utf-8');
        echo $spec->toRdfXml();
        $this->terminate();
    }
}
