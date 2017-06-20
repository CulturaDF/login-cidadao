<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\PhoneVerificationBundle\Tests\Service;

use LoginCidadao\PhoneVerificationBundle\Entity\SentVerification;
use LoginCidadao\PhoneVerificationBundle\Service\SmsStatusUpdater;

class SmsStatusUpdaterTest extends \PHPUnit_Framework_TestCase
{
    public function testNoPendingUpdate()
    {
        $repo = $this->getRepository([]);
        $updater = $this->getSmsStatusUpdater($this->getDispatcher(), $repo);
        $updater->updateSentVerificationStatus($this->getIo(), $this->getEntityManager());
    }

    public function testOnePendingUpdate()
    {
        $date = new \DateTime();

        $dispatcher = $this->getDispatcher();
        $dispatcher->expects($this->atMost(1))
            ->method('dispatch')
            ->willReturnCallback(
                function ($eventName, $event) use ($date) {
                    $event->setDeliveryStatus('Entregue')
                        ->setDeliveredAt($date)
                        ->setSentAt($date);
                }
            );

        $sentVerification = new SentVerification();
        $sentVerification->setTransactionId('0123456');

        $items = [
            [
                $sentVerification,
            ],
        ];

        $repo = $this->getRepository($items);
        $updater = $this->getSmsStatusUpdater($dispatcher, $repo);
        $updater->updateSentVerificationStatus($this->getIo(), $this->getEntityManager());

        $this->assertEquals($date, $sentVerification->getActuallySentAt());
        $this->assertEquals($date, $sentVerification->getDeliveredAt());
        $this->assertTrue($sentVerification->isFinished());
    }

    public function testOnePendingUpdateNoListener()
    {
        $dispatcher = $this->getDispatcher();

        $sentVerification = new SentVerification();
        $sentVerification->setTransactionId('0123456');

        $items = [
            [
                $sentVerification,
            ],
        ];

        $repo = $this->getRepository($items);
        $updater = $this->getSmsStatusUpdater($dispatcher, $repo);
        $updater->updateSentVerificationStatus($this->getIo(), $this->getEntityManager());

        $this->assertFalse($sentVerification->isFinished());
    }

    public function testAverageTime()
    {
        $sentVerification1 = new SentVerification();
        $sentVerification1->setSentAt(new \DateTime())
            ->setDeliveredAt(new \DateTime('+30 seconds'))
            ->setActuallySentAt(new \DateTime('+5 seconds'));

        $sentVerification2 = new SentVerification();
        $sentVerification2->setSentAt(new \DateTime())
            ->setDeliveredAt(new \DateTime('+30 seconds'))
            ->setActuallySentAt(new \DateTime('+5 seconds'));

        $verifications = [
            $sentVerification1,
            $sentVerification2,
        ];

        $repo = $this->getRepository();
        $repo->expects($this->once())->method('getLastDeliveredVerifications')
            ->willReturn($verifications);

        $updater = $this->getSmsStatusUpdater($this->getDispatcher(), $repo);
        $avg = $updater->getAverageDeliveryTime(2);

        $this->assertEquals(0.5, $avg);
    }

    public function testNoAverageTime()
    {
        $repo = $this->getRepository();
        $repo->expects($this->once())->method('getLastDeliveredVerifications')
            ->willReturn([]);

        $updater = $this->getSmsStatusUpdater($this->getDispatcher(), $repo);
        $avg = $updater->getAverageDeliveryTime(2);

        $this->assertEquals(0, $avg);
    }

    private function getSmsStatusUpdater($dispatcher, $repo)
    {
        $updater = new SmsStatusUpdater($dispatcher, $repo);

        return $updater;
    }

    private function getDispatcher()
    {
        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        return $dispatcher;
    }

    private function getQuery($items = [])
    {
        $query = $this->getMockBuilder('\Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->getMock();
        $query->expects($this->atMost(1))->method('iterate')
            ->willReturn(new \ArrayIterator($items));

        return $query;
    }

    private function getRepository($items = null)
    {
        $repo = $this->getMockBuilder('LoginCidadao\PhoneVerificationBundle\Entity\SentVerificationRepository')
            ->disableOriginalConstructor()
            ->getMock();

        if ($items !== null) {
            $repo->expects($this->atMost(1))
                ->method('getPendingUpdateSentVerificationQuery')
                ->willReturn($this->getQuery($items));

            $repo->expects($this->atMost(1))
                ->method('countPendingUpdateSentVerification')
                ->willReturn(count($items));
        }

        return $repo;
    }

    private function getEntityManager()
    {
        $em = $this->getMock('Doctrine\ORM\EntityManagerInterface');

        return $em;
    }

    private function getIo()
    {
        $io = $this->getMockBuilder('Symfony\Component\Console\Style\SymfonyStyle')
            ->disableOriginalConstructor()
            ->getMock();

        return $io;
    }
}