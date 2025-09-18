<?php declare(strict_types=1);

namespace App\Console\Admin;

use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosStatus;
use App\Services\RepositoryConfiguration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class dbUpdateThumbs1280 extends Command
{


    public function __construct(protected readonly EntityManagerInterface $entityManager, protected RepositoryConfiguration $repositoryConfiguration, ?string $name = null)
    {
        parent::__construct($name);
    }

    /**
     * @return Photos[]
     */
    public function getListOfPhotos(): ?array
    {
        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata('App\Model\Database\Entity\Photos', 'p');
        $query = $this->entityManager->createNativeQuery('SELECT p.* FROM photos p WHERE status_id IN (?) AND databot_thumb_filename IS NULL ORDER BY id asc', $rsm);
        $query->setParameter(1, PhotosStatus::PASSED);

        return $query->execute();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startTime = microtime(true);
        $i = 0;
        foreach ($this->getListOfPhotos() as $photo) {
            $i++;
            $photo->setDatabotThumbFilename($this->repositoryConfiguration->createS3DatabotThumbName($photo));
            if ($i === 500) {
                $i = 0;
                $this->entityManager->flush();
            }
        }
        $this->entityManager->flush();

        $output->writeln(sprintf("\n Execution time: %.2f sec", (microtime(true) - $startTime)));

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this->setName('admin:dbUpdate1280Thumbs');
        $this->setDescription('fix database content');
    }

}
