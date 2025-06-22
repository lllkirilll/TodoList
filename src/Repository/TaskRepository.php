<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * Finds tasks for a user based on filters and sorting criteria.
     *
     * @param User $user The owner of the tasks.
     * @param array $filters Contains 'status', 'priority', and 'search' keys.
     * @param array $sorting Contains 'sortBy' and 'sortOrder' keys.
     * @return Task[] Returns an array of Task objects.
     */
    public function findByFilters(User $user, array $filters, array $sorting): array
    {
        $qb = $this->createQueryBuilder('t');

        $qb->where('t.owner = :user')
            ->andWhere('t.parent IS NULL')
            ->setParameter('user', $user);

        if (!empty($filters['status'])) {
            $qb->andWhere('t.status = :status')
                ->setParameter('status', $filters['status']);
        }
        if (!empty($filters['priority'])) {
            $qb->andWhere('t.priority = :priority')
                ->setParameter('priority', $filters['priority']);
        }

        if (!empty($filters['title'])) {
            $qb->andWhere($qb->expr()->like('t.title', ':title'))
                ->setParameter('title', '%' . $filters['title'] . '%');
        }
        if (!empty($filters['description'])) {
            $qb->andWhere($qb->expr()->like('t.description', ':description'))
                ->setParameter('description', '%' . $filters['description'] . '%');
        }

        // Apply sorting
        // Example: ?sort=priority,desc,createdAt,asc
        if (!empty($sorting['sort'])) {
            $sortPairs = explode(',', $sorting['sort']);
            for ($i = 0; $i < count($sortPairs); $i += 2) {
                $field = $sortPairs[$i];
                $direction = $sortPairs[$i + 1] ?? 'asc';

                if (in_array($field, ['priority', 'createdAt', 'completedAt'])) {
                    $qb->addOrderBy('t.' . $field, $direction);
                }
            }
        } else {
            $qb->orderBy('t.createdAt', 'DESC');
        }

        return $qb->getQuery()->getResult();
    }
}
