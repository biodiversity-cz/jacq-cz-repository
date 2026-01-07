<?php declare(strict_types=1);

namespace App\Model\ImportStages;

use App\Model\ImportStages\Exceptions\ConvertStageException;
use League\Pipeline\StageInterface;

class ConvertStage extends BaseStage implements StageInterface
{

    public function __invoke(mixed $payload): mixed
    {
        $this->item = $payload;
        try {
            $this->getLargestPageFromMaster();
            $this->createJp2File();
            $payload->setJp2FileSize(filesize($this->getIiifTempPath()));
        } catch (\Throwable $exception) {
            throw new ConvertStageException('unable convert to JP2 (' . $exception->getMessage() . '): ' . $payload->id);
        }

        return $payload;
    }

    protected function getLargestPageFromMaster(): void
    {
        $imagick = $this->imagickService->createImagick($this->getMasterTempPath());
        $imagick->setImageFormat('tiff');
        $imagick->writeImage($this->getMasterSinglePageTempPath());
        $imagick->clear();
        unset($imagick);
    }

    protected function createJp2File(): void
    {
        /**
         * https://github.com/uoregon-libraries/rais-image-server/wiki/How-To-Encode-JP2s
         * A rate [-r] of 20.250 is equivalent to a graphicsmagick "quality" of 70, which in JP2-land is about the same as a JPEG of quality 90-95 (very good). JP2 -r 10 ≈ JPEG 80–85%
         * -n 6 specifies that there are six resolution levels. This can be optimized based on the image's size if desired, but 6 is the default. Typically 6 will be fine, but a decent guideline is to start at 6 levels for a 16-megapixel image and add a resolution level each time the number of megapixels quadruples. e.g., 16mp = -n 6, 64mp = -n 7, 256mp = -n 8, etc.
         */
        $cmd = [
            'opj_compress',
            '-i', $this->getMasterSinglePageTempPath(),
            '-o', $this->getIiifTempPath(),
            '-t', "1024,1024",
            '-r', "10",
            '-n', "7",
        ];

        $descriptors = [
            1 => ['pipe', 'w'], // stdout
            2 => ['pipe', 'w'], // stderr
        ];

        $process = proc_open($cmd, $descriptors, $pipes);

        if (!is_resource($process)) {
            throw new ConvertStageException('Unable to start JP2 process');
        }

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

    }

}
