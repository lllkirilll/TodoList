<?php
// src/DTO/TaskRequest.php

namespace App\DTO;

use App\Enum\TaskPriority;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "TaskRequest",
    title: "TaskRequest",
    description: "Data for creating or updating a task."
)]
class TaskRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    #[OA\Property(description: "The title of the task.", example: "My First Task")]
    public ?string $title = null;

    #[Assert\Length(max: 10000)]
    #[OA\Property(description: "A detailed description for the task.", example: "Remember to buy milk.")]
    public ?string $description = null;

    #[Assert\NotBlank]
    #[Assert\Type(type: TaskPriority::class)]
    #[OA\Property(description: "The priority of the task (1-5).", type: "integer", enum: [1, 2, 3, 4, 5], example: 3)]
    public ?TaskPriority $priority = null;

    #[OA\Property(description: "The ID of the parent task, if this is a subtask.", type: "integer", example: 1, nullable: true)]
    public ?int $parentId = null;
}
