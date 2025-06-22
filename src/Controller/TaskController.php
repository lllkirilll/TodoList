<?php

namespace App\Controller;

use App\DTO\TaskRequest;
use App\DTO\TaskResponse;
use App\Entity\Task;
use App\Entity\User;
use App\Service\TaskService;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use InvalidArgumentException;
use OpenApi\Attributes as OA;

#[Route('/api/tasks')]
#[OA\Tag(name: 'Tasks', description: 'Operations related to user tasks')]
class TaskController extends AbstractController
{
    public function __construct(
        private readonly TaskService $taskService,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator
    ) {}

    /**
     * Retrieves a list of top-level tasks for the current user.
     */
    #[Route('', name: 'api_tasks_list', methods: ['GET'])]
    #[OA\Get(
        description: 'Retrieves a list of tasks for the authenticated user. You can filter by status, priority, title, and description. Sorting by multiple fields is also supported.',
        summary: 'List user tasks with filters and sorting'
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of tasks for the current user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: TaskResponse::class))
        )
    )]
    #[OA\Parameter(name: 'filter[status]', description: "Filter by status ('todo' or 'done')", in: 'query', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'filter[priority]', description: 'Filter by priority (1-5)', in: 'query', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'filter[title]', description: 'Search for a substring in the task title', in: 'query', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'filter[description]', description: 'Search for a substring in the task description', in: 'query', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'sort[sort]', description: 'Sort by fields. Format: `field1,order1,field2,order2`', in: 'query', schema: new OA\Schema(type: 'string'))]
    #[Security(name: 'Bearer')]
    public function list(Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $filters = $request->query->all('filter');
        $sorting = $request->query->all('sort');
        $tasks = $this->taskService->getTasksForUser($user, $filters, $sorting);
        $responseDtos = array_map(fn(Task $task) => TaskResponse::fromEntity($task), $tasks);
        return $this->json($responseDtos);
    }

    /**
     * Creates a new task.
     * @throws ExceptionInterface
     */
    #[Route('', name: 'api_tasks_create', methods: ['POST'])]
    #[OA\Response(response: 201, description: 'Task created successfully', content: new OA\JsonContent(ref: new Model(type: TaskResponse::class)))]
    #[OA\Response(response: 400, description: 'Invalid input')]
    #[OA\RequestBody(
        description: 'Task data to create',
        required: true,
        content: new OA\JsonContent(ref: new Model(type: TaskRequest::class))
    )]
    #[Security(name: 'Bearer')]
    public function create(Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $taskRequest = $this->serializer->deserialize($request->getContent(), TaskRequest::class, 'json');
        $this->validateDto($taskRequest);
        $task = $this->taskService->createTask($taskRequest, $user);
        return $this->json(TaskResponse::fromEntity($task), Response::HTTP_CREATED);
    }

    /**
     * Retrieves a single task by its ID.
     */
    #[Route('/{id}', name: 'api_tasks_show', methods: ['GET'])]
    #[OA\Response(response: 200, description: 'Returns the task details', content: new OA\JsonContent(ref: new Model(type: TaskResponse::class)))]
    #[OA\Response(response: 403, description: 'Access denied')]
    #[OA\Response(response: 404, description: 'Task not found')]
    #[Security(name: 'Bearer')]
    public function show(Task $task, #[CurrentUser] User $user): JsonResponse
    {
        if ($task->getOwner() !== $user) {
            throw $this->createAccessDeniedException('You are not allowed to see this task');
        }
        return $this->json(TaskResponse::fromEntity($task));
    }

    /**
     * Updates an existing task.
     * @throws ExceptionInterface
     */
    #[Route('/{id}', name: 'api_tasks_update', methods: ['PUT'])]
    #[OA\Response(response: 200, description: 'Task updated successfully', content: new OA\JsonContent(ref: new Model(type: TaskResponse::class)))]
    #[OA\Response(response: 400, description: 'Invalid input')]
    #[OA\Response(response: 404, description: 'Task not found')]
    #[OA\RequestBody(description: 'Task data to update', required: true, content: new OA\JsonContent(ref: new Model(type: TaskRequest::class)))]
    #[Security(name: 'Bearer')]
    public function update(Task $task, Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $taskRequest = $this->serializer->deserialize($request->getContent(), TaskRequest::class, 'json');
        $this->validateDto($taskRequest);
        $updatedTask = $this->taskService->updateTask($task, $taskRequest, $user);
        return $this->json(TaskResponse::fromEntity($updatedTask));
    }

    /**
     * Marks a task as complete.
     */
    #[Route('/{id}/complete', name: 'api_tasks_complete', methods: ['POST'])]
    #[OA\Response(response: 200, description: 'Task marked as complete', content: new OA\JsonContent(ref: new Model(type: TaskResponse::class)))]
    #[OA\Response(response: 400, description: 'Cannot complete task with unfinished subtasks')]
    #[OA\Response(response: 404, description: 'Task not found')]
    #[Security(name: 'Bearer')]
    public function complete(Task $task, #[CurrentUser] User $user): JsonResponse
    {
        $completedTask = $this->taskService->completeTask($task, $user);
        return $this->json(TaskResponse::fromEntity($completedTask));
    }

    /**
     * Deletes a task.
     */
    #[Route('/{id}', name: 'api_tasks_delete', methods: ['DELETE'])]
    #[OA\Response(response: 204, description: 'Task deleted successfully')]
    #[OA\Response(response: 400, description: 'Cannot delete a completed task')]
    #[OA\Response(response: 404, description: 'Task not found')]
    #[Security(name: 'Bearer')]
    public function delete(Task $task, #[CurrentUser] User $user): Response
    {
        $this->taskService->deleteTask($task, $user);
        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    private function validateDto(object $dto): void
    {
        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            throw new InvalidArgumentException((string)$errors);
        }
    }
}
