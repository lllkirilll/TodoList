<?php
// src/DataFixtures/TaskFixtures.php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use App\Enum\TaskPriority;
use App\Enum\TaskStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TaskFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $user1 = $this->getReference(UserFixtures::USER_1_REFERENCE, User::class);

        $task1 = new Task();
        $task1->setTitle('Buy groceries');
        $task1->setDescription('Milk, bread, cheese, and fruits.');
        $task1->setPriority(TaskPriority::LEVEL_4);
        $task1->setOwner($user1);
        $manager->persist($task1);

        $subtask1_1 = new Task();
        $subtask1_1->setTitle('Buy milk');
        $subtask1_1->setPriority(TaskPriority::LEVEL_5);
        $subtask1_1->setOwner($user1);
        $subtask1_1->setParent($task1);
        $manager->persist($subtask1_1);

        $task2 = new Task();
        $task2->setTitle('Finish the report');
        $task2->setDescription('Final review of the Q2 financial report.');
        $task2->setPriority(TaskPriority::LEVEL_5);
        $task2->setStatus(TaskStatus::DONE);
        $task2->setCompletedAt(new \DateTimeImmutable('-1 day'));
        $task2->setOwner($user1);
        $manager->persist($task2);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
