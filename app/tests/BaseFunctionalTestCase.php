<?php

declare(strict_types=1);

namespace App\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class BaseFunctionalTestCase extends WebTestCase
{
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        parent::setUp();

        $this->em = self::getContainer()->get(EntityManagerInterface::class);

        // begin transaction
        if (!$this->em->getConnection()->isTransactionActive()) {
            $this->em->beginTransaction();
        }
    }

    protected function tearDown(): void
    {
        // rollback transaction
        if ($this->em->getConnection()->isTransactionActive()) {
            $this->em->rollback();
        }

        unset($this->em);

        parent::tearDown();
    }

}
