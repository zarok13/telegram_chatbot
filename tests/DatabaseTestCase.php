<?php

namespace App\Tests;

use Doctrine\ORM\Tools\SchemaTool;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DatabaseTestCase extends KernelTestCase
{
    protected $entityManager;
    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        if ($kernel->getEnvironment() !== 'test') {
            throw new LogicException('Execution only in Test environment possible!');
        }
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $metaData = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->updateSchema($metaData);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if(!empty($this->entityManager)) {
            $this->entityManager->close();
            $this->entityManager = null;
        }
    }
}
