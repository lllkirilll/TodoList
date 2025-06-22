<?php
namespace App\DTO;

use App\Entity\Task;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "TaskResponse",
    title: "TaskResponse",
    description: "Represents a task in the API response."
)]
class TaskResponse
{
    #[OA\Property(description: "The unique identifier of the task.", example: 1)]
    public int $id;

    #[OA\Property(description: "The title of the task.", example: "Buy groceries")]
    public string $title;

    #[OA\Property(description: "The description of the task.", example: "Milk, bread, cheese.")]
    public ?string $description;

    #[OA\Property(description: "The current status of the task.", example: "todo")]
    public string $status;

    #[OA\Property(description: "The priority of the task.", example: 4)]
    public int $priority;

    #[OA\Property(description: "The creation date of the task.", example: "2023-10-27 10:00:00")]
    public string $createdAt;

    #[OA\Property(description: "The completion date of the task.", example: "2023-10-28 12:00:00", nullable: true)]
    public ?string $completedAt;

    #[OA\Property(description: "The ID of the parent task.", example: null, nullable: true)]
    public ?int $parentId;

    /**
     * @var TaskResponse[]
     */
    #[OA\Property(description: "A list of subtasks.", type: 'array', items: new OA\Items(ref: '#/components/schemas/TaskResponse'))]
    public array $subtasks = [];

    public static function fromEntity(Task $task): self
    {
        $response = new self();
        $response->id = $task->getId();
        $response->title = $task->getTitle();
        $response->description = $task->getDescription();
        $response->status = $task->getStatus()->value;
        $response->priority = $task->getPriority()->value;
        $response->createdAt = $task->getCreatedAt()->format('Y-m-d H:i:s');
        $response->completedAt = $task->getCompletedAt()?->format('Y-m-d H:i:s');
        $response->parentId = $task->getParent()?->getId();

        foreach ($task->getSubtasks() as $subtask) {
            $response->subtasks[] = self::fromEntity($subtask);
        }

        return $response;
    }
}
