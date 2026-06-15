<?php

declare(strict_types=1);

namespace App\Console\Admin;

use App\Facades\CuratorFacade;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosStatus;
use App\Security\Identity;
use App\Services\EntityServices\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Nette\Security\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BatchDelete extends Command
{
    /**
     * For development purpose only, let's delete images with status 100.
     */
    public function __construct(protected readonly EntityManagerInterface $entityManager, protected readonly CuratorFacade $curatorFacade, protected User $user, protected readonly UserService $userService, ?string $name = null)
    {
        //        exit; // disabled just for sure
        parent::__construct($name);
    }

    /**
     * @return Photos[]
     */
    public function getPhotos(): array
    {
        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata('App\Model\Database\Entity\Photos', 'p');
        $query = $this->entityManager->createNativeQuery('SELECT p.* FROM photos p WHERE status_id = ? ORDER BY id asc', $rsm);
        $query->setParameter(1, PhotosStatus::DEVELOP_PROCEED);

        return $query->execute();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startTime = microtime(true);
        $photos = $this->getPhotos();
        $output->writeln(count($photos).' files will be affected.');
        $user = $this->userService->find(1);
        foreach ($photos as $photo) {
            $this->user->login(new Identity($user));
            $this->curatorFacade->deletePhoto($this->user, $photo);
        }

        $output->writeln(sprintf("\n Execution time: %.2f sec", microtime(true) - $startTime));

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this->setName('admin:batchDelete');
        $this->setDescription('delete items');
    }
}
