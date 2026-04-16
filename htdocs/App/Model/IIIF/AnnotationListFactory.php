<?php declare(strict_types=1);

namespace App\Model\IIIF;

use App\Model\Database\Entity\Databot;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Repository\DatabotRepository;
use App\Services\EntityServices\PhotoService;
use App\Services\RepositoryConfiguration;
use Doctrine\ORM\EntityManagerInterface;
use IIIF\PresentationAPI\Parameters\DCType;
use IIIF\PresentationAPI\Parameters\ViewingDirection;
use IIIF\PresentationAPI\Resources\Annotation;
use IIIF\PresentationAPI\Resources\AnnotationList;
use IIIF\PresentationAPI\Resources\Content;
use Nette\Application\LinkGenerator;

class AnnotationListFactory
{

    protected Photos $photo;

    protected string $selfReferencingLink;

    public function __construct(protected readonly EntityManagerInterface $entityManager, protected readonly RepositoryConfiguration $repositoryConfiguration, protected readonly LinkGenerator $linkGenerator, protected readonly PhotoService $photoService)
    {
    }

    public function createList(Photos $photo, string $selfReferencingLink): AnnotationList
    {
        $this->photo = $photo;
        $this->selfReferencingLink = $selfReferencingLink;
        $annotationList = new AnnotationList(true);
        $annotationList
            ->addContext('http://www.w3.org/ns/anno.jsonld')
            ->setID($selfReferencingLink)
            ->setViewingDirection(ViewingDirection::LEFT_TO_RIGHT);

        $cetafDatabot = $this->entityManager->getRepository(Databot::class)->getByName(DatabotRepository::HESPI_SHEET);
        if ($cetafDatabot && $photo->getDatabotOkResultById($cetafDatabot)?->resultData) {
            $this->proceedDatabotResult($photo, $annotationList, $photo->getDatabotOkResultById($cetafDatabot)->resultData);
        }

        return $annotationList;
    }

    protected function proceedDatabotResult(Photos $photo, AnnotationList $annotationList, array $databotResult)
    {
        $categories = $databotResult['categories'];
        $segments = $databotResult['annotations'];

        foreach ($segments as $segment) {
            $annotationList->addAnnotation($this->createAnnotation($photo, $segment, $categories));
        }

    }

    protected function createAnnotation(Photos $photo, array $segment, array $categories): Annotation
    {
        $content = new Content();
        $content
            ->setID($this->repositoryConfiguration->getImageServerInfoUrl($photo) . '#' . DatabotRepository::HESPI_SHEET . '_segmentcontent_' . $segment['id'])
            ->setType(DCType::TEXT)
            ->setFormat('text/plain')
            ->setChars(DatabotRepository::HESPI_SHEET . ':' . $categories[$segment['category_id']]['name']);

        $bbox = implode(',', $segment['bbox']);
        $annotation = new Annotation();
        $annotation
            ->setID($this->repositoryConfiguration->getImageServerInfoUrl($photo) . '#' . DatabotRepository::HESPI_SHEET . '_segment_' . $segment['id'])
            ->setOn($this->repositoryConfiguration->getImageServerInfoUrl($photo) . '#canvas#xywh=' . $bbox)
            ->setMotivation('tagging')
            ->setContent($content);

        return $annotation;
    }

}
