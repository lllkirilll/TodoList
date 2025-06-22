<?php
// src/Service/TaskService.php

namespace App\Service;

use App\DTO\TaskRequest;
use App\Entity\Task;
use App\Entity\User;
use App\Enum\TaskStatus;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TaskService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TaskRepository $taskRepository
    ) {}

    /**
     * Get a filtered and sorted list of tasks for a user.
     */
    public function getTasksForUser(User $user, array $filters, array $sorting): array
    {
        return $this->taskRepository->findByFilters($user, $filters, $sorting);
    }

    /**
     * Create a new task.
     */
    public function createTask(TaskRequest $dto, User $owner): Task
    {
        $task = new Task();
        $this->updateTaskFromDto($task, $dto);
        $task->setOwner($owner);

        if ($dto->parentId) {
            $parentTask = $this->findUserTask($dto->parentId, $owner);
            $task->setParent($parentTask);
        }

        $this->em->persist($task);
        $this->em->flush();
        return $task;
    }

    /**
     * Update an existing task.
     */
    public function updateTask(Task $task, TaskRequest $dto, User $user): Task
    {
        if ($task->getOwner() !== $user) {
            throw new AccessDeniedException('You cannot edit a task that does not belong to you');
        }

        $this->updateTaskFromDto($task, $dto);

        if ($dto->parentId) {
            if ($dto->parentId === $task->getId()) {
                throw new BadRequestHttpException('A task cannot be its own parent.');
            }
            $parentTask = $this->findUserTask($dto->parentId, $user);
            $task->setParent($parentTask);
        } else {
            $task->setParent(null);
        }

        $this->em->flush();
        return $task;
    }

    /**
     * Mark a task as complete.
     */
    public function completeTask(Task $task, User $user): Task
    {
        if ($task->getOwner() !== $user) {
            throw new AccessDeniedException('You cannot complete a task that does not belong to you');
        }

        foreach ($task->getSubtasks() as $subtask) {
            if ($subtask->getStatus() !== TaskStatus::DONE) {
                throw new BadRequestHttpException('Cannot complete a task with unfinished subtasks.');
            }
        }

        $task->setStatus(TaskStatus::DONE);
        $task->setCompletedAt(new \DateTimeImmutable());
        $this->em->flush();

        return $task;
    }

    /**
     * Delete a task.
     */
    public function deleteTask(Task $task, User $user): void
    {
        if ($task->getOwner() !== $user) {
            throw new AccessDeniedException('You cannot delete a task that does not belong to you');
        }

        if ($task->getStatus() === TaskStatus::DONE) {
            throw new BadRequestHttpException('Cannot delete a completed task');
        }

        $this->em->remove($task);
        $this->em->flush();
    }

    /**
     * Helper to find a task and verify ownership.
     */
    private function findUserTask(int $id, User $user): Task
    {
        $task = $this->taskRepository->find($id);
        if (!$task) {
            throw new NotFoundHttpException('Task not found');
        }
        if ($task->getOwner() !== $user) {
            throw new AccessDeniedException('Access denied to the requested task');
        }
        return $task;
    }

    /**
     * Helper to map DTO data to a Task entity.
     */
    private function updateTaskFromDto(Task $task, TaskRequest $dto): void
    {
        $task->setTitle($dto->title);
        $task->setDescription($dto->description);
        $task->setPriority($dto->priority);
    }
}
