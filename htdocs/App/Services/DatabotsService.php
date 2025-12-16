<?php declare(strict_types=1);

namespace App\Services;

use App\Model\Database\Entity\Databot;
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
        if ($databotResult?->status !== DatabotResultStatus::OK) {
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
        $percentil = $this->repository->getPercentilOfMetric($databotResult->databot->id, $name, $photo);
        if ($higherIsBetter) {
            return $percentil >= $thresholdPercentile;
        } else {
            return $percentil < $thresholdPercentile;
        }
    }

    public function databotResultsToArray(Photos $photo): array
    {
        $value = [];
        if (empty($photo->databotResults)) {
            return $value;
        }

        foreach ($photo->databotResults as $databotRecord) {
            $identikit = $databotRecord->getDatabot()->name . ' v' . $databotRecord->getDatabot()->version . ' - ' . $databotRecord->createdAt->format('j.n.Y H:i');
            $databot['name'] = $databotRecord->getDatabot()->name;
            $databot['version'] = $databotRecord->getDatabot()->version;
            $databot['computedAt'] = $databotRecord->createdAt->format('j.n.Y H:i');
            $databot['description'] = $databotRecord->getDatabot()->description;
            if ($databotRecord->status->value == 'ok') {
                foreach ($databotRecord->getResultData() as $score) {
                    $databot['data'][$score['name']] = $score['value'];
                    if ($databotRecord->getDatabot()->name === "no-ref-image-metrics") {
                        $databot['percentile_all'][$score['name']] = $this->repository->getPercentilOfMetric($databotRecord->getDatabot()->id, $score['name'], $photo);
                        $databot['percentile_' . $photo->herbarium->acronym][$score['name']] = $this->repository->getPercentilOfMetric($databotRecord->getDatabot()->id, $score['name'], $photo, false);
                    }
                }
            } else {
                $databot['message'] = $databotRecord->getMessage();
            }
            $value[$identikit] = $databot;
        }


        return $value;
    }


    public function getStats(string $variableName, int $databotId): array
    {
        $values = [];
        $sql = "SELECT percentile_cont(0.25) WITHIN GROUP (ORDER BY (elem->>'value')::float) AS q1,
                percentile_cont(0.5)  WITHIN
                GROUP (ORDER BY (elem->>'value'):: float) AS median,
                    percentile_cont(0.75) WITHIN
                GROUP (ORDER BY (elem->>'value'):: float) AS q3,
                        MIN ((elem->>'value'):: float) AS min_val,
                        MAX ((elem->>'value'):: float) AS max_val
                    FROM databots.databot_results, LATERAL jsonb_array_elements(result_data) AS elem
                WHERE elem->>'name' = :variableName AND databot_id = :databotId ";

        $result = $this->entityManager->getConnection()->executeQuery($sql, ['variableName' => $variableName, 'databotId' => $databotId]);
        $values['all'] = $result->fetchNumeric();

        $sql = "SELECT
                h.acronym,
                percentile_cont(0.25) WITHIN GROUP (ORDER BY (elem->>'value')::float) AS q1,
                percentile_cont(0.5)  WITHIN GROUP (ORDER BY (elem->>'value')::float) AS median,
                percentile_cont(0.75) WITHIN GROUP (ORDER BY (elem->>'value')::float) AS q3,
                MIN((elem->>'value')::float) AS min_val,
                MAX((elem->>'value')::float) AS max_val
            FROM databots.databot_results AS r
            JOIN photos AS p ON p.id = r.photo_id
                JOIN herbaria h ON p.herbarium_id = h.id
            CROSS JOIN LATERAL jsonb_array_elements(r.result_data) AS elem
            WHERE elem->>'name' = :variableName
              AND r.databot_id = :databotId
            GROUP BY h.acronym
            ORDER BY h.acronym";
        $result = $this->entityManager->getConnection()->executeQuery($sql, ['variableName' => $variableName, 'databotId' => $databotId]);
        $herbaria = $result->fetchAllNumeric();
        foreach ($herbaria as $h) {
            $acronym = $h[0];
            unset($h[0]);
            $values[$acronym] = array_values($h);
        }
        return $values;
    }

    public function getDatabot(int $databotId): Databot
    {
        return $this->entityManager->getRepository(Databot::class)->find($databotId);
    }

}
