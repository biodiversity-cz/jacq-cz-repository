<?php declare(strict_types=1);

namespace App\Services;

use App\Model\Database\Entity\DatabotResult;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Enums\DatabotResultStatus;
use App\Model\Database\Repository\DatabotResultRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nette\Utils\Html;

class DatabotsService
{

    public const string DATABOT = 'no-ref-image-metrics';
    protected DatabotResultRepository $repository;

    public function __construct(protected EntityManagerInterface $entityManager)
    {
        $this->repository = $this->entityManager->getRepository(DatabotResult::class);
    }


    public function getQualityEvaluation(Photos $photo): Html
    {
        $element = Html::el('span');
        $databotResult = $this->repository->findLatestByPhotoAndDatabotName($photo, self::DATABOT);
        if ($databotResult?->getStatus() !== DatabotResultStatus::OK) {
            return $element
                ->addHtml(
                    Html::el('i')->class('fa-solid fa-question text-secondary')
                )
                ->addText(' unknown');
        }

        $metrics = [
            ['name' => 'sharpness', 'thresholdPercentile' => 30, 'higherIsBetter' => true],
            ['name' => 'contrast', 'thresholdPercentile' => 30, 'higherIsBetter' => true],
            ['name' => 'clarity', 'thresholdPercentile' => 30, 'higherIsBetter' => true],
            ['name' => 'resolution', 'thresholdPercentile' => 30, 'higherIsBetter' => true],
            ['name' => 'brisque_score', 'thresholdPercentile' => 90, 'higherIsBetter' => false],
        ];
        foreach ($metrics as $metric) {
            if (!$this->isOkMetric($databotResult, $photo, $metric['name'], $metric['thresholdPercentile'], $metric['higherIsBetter'])) {
                return $element
                    ->addHtml(
                        Html::el('i')->class('fa-regular fa-star text-warning')
                    )
                    ->addText(' to be checked');
            }
        }

        return $element
            ->addHtml(
                Html::el('i')->class('fa-solid fa-star text-success')
            )
            ->addText(' good');


    }

    protected function isOkMetric(DatabotResult $databotResult, Photos $photo, string $name, int $thresholdPercentile, bool $higherIsBetter = true): bool
    {
        $percentil = $this->repository->getPercentilOfMetric($databotResult->getDatabot()->getId(), $name, $photo);
        if ($higherIsBetter) {
            return $percentil >= $thresholdPercentile;
        } else {
            return $percentil < $thresholdPercentile;
        }
    }

    public function databotResultsToArray(Photos $photo): array
    {
        $value = [];
        if (empty($photo->getDatabotResults())) {
            return $value;
        }

        foreach ($photo->getDatabotResults() as $databotRecord) {
            $identikit = $databotRecord->getDatabot()->getName() . ' v' . $databotRecord->getDatabot()->getVersion() . ' - ' . $databotRecord->getCreatedAt()->format('j.n.Y H:i');
            $databot['name'] = $databotRecord->getDatabot()->getName();
            $databot['version'] = $databotRecord->getDatabot()->getVersion();
            $databot['computedAt'] = $databotRecord->getCreatedAt()->format('j.n.Y H:i');
            $databot['description'] = $databotRecord->getDatabot()->getDescription();
            if ($databotRecord->getStatus()->value == 'ok') {
                foreach ($databotRecord->getResultData() as $score) {
                    $databot['data'][$score['name']] = $score['value'];
                    if ($databotRecord->getDatabot()->getName() === "no-ref-image-metrics") {
                        $databot['percentile_all'][$score['name']] = $this->repository->getPercentilOfMetric($databotRecord->getDatabot()->getId(), $score['name'], $photo);
                        $databot['percentile_' . $photo->getHerbarium()->getAcronym()][$score['name']] = $this->repository->getPercentilOfMetric($databotRecord->getDatabot()->getId(), $score['name'], $photo, false);
                    }
                }
            } else {
                $databot['message'] = $databotRecord->getMessage();
            }
            $value[$identikit] = $databot;
        }


        return $value;
    }


}
