<?php declare(strict_types=1);

namespace App\UI\Front\Cetaf;

use App\Services\EntityServices\CetafSidService;
use App\UI\Base\UnsecuredPresenter;
use Nette\Application\Responses\RedirectResponse;

final class CetafPresenter extends UnsecuredPresenter
{

    /** @inject */ public CetafSidService $cetafSidRepository;

    /**
     * service used for detection if specimen exists + returns link to CETAF
     */
    public function renderExists(string $id, int $herbariumId): void
    {
        $specimen = $this->cetafSidRepository->findOneBy(['barcode' => $id, 'herbarium' => $herbariumId]);

        if ($specimen === null) {
            $this->error('Specimen not found');
        }

        $this->sendJson(['cetaf_sid' => $this->link('//:sid', $specimen->id)]);

    }

    /**
     * CETAF endpoint
     */
    public function actionSid(string $id): void
    {
        $specimen = $this->cetafSidRepository->find((int) $id);

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
            $this->error('Specimen not found', 404);
        }
        $this->template->specimen = $spec;
    }

    /**
     *
     * curl -H "Accept: application/rdf+xml" http://localhost/cetaf/data/2
    */
    public function actionData(int $id): void
    {
        $spec = $this->cetafSidRepository->find($id);
        if (!$spec) {
            $this->error('Specimen not found', 404);
        }
        $this->getHttpResponse()->setHeader('Content-Type', 'application/rdf+xml; charset=utf-8');
        echo $spec->toRdfXml($this->link('//this'));
        $this->terminate();
    }
}
