<?php

namespace App\Service;

use App\Entity\Publication;
use App\Entity\React;
use App\Entity\Users;
use App\Entity\PublicationView;
use Doctrine\ORM\EntityManagerInterface;

class RecommendationService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function generateRecommendations(Users $user): array
    {
        // Get the types of the publications that the user has liked
        $likedPublicationTypes = [];
        $userLikes = $this->entityManager->getRepository(React::class)->findBy([
            'id_user' => $user,
            'likeCount' => 1, // Only consider likes
        ]);
        foreach ($userLikes as $like) {
            $likedPublicationTypes[] = $like->getIdPub()->getType();
        }

        // Get the types of the publications that the user has viewed
        $viewedPublicationTypes = [];
        $userViews = $this->entityManager->getRepository(PublicationView::class)->findBy([
            'id_user' => $user,
        ]);
        foreach ($userViews as $view) {
            $viewedPublicationTypes[] = $view->getIdPub()->getType();
        }

        // Count the occurrences of each viewed type
        $viewedTypeCounts = array_count_values($viewedPublicationTypes);

        // Filter types that have been viewed twice
        $relevantTypes = [];
        foreach ($viewedTypeCounts as $type => $count) {
            if ($count >= 2) {
                $relevantTypes[] = $type;
            }
        }

        // If there are no relevant types based on views, use liked types
        if (empty($relevantTypes)) {
            $relevantTypes = array_unique($likedPublicationTypes);
        }

        // If there are no relevant types at all, return empty recommendations
        if (empty($relevantTypes)) {
            return [];
        }

        // Get publications of the relevant types, excluding already interacted publications
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->select('p')
            ->from(Publication::class, 'p')
            ->where($queryBuilder->expr()->in('p.type', $relevantTypes))
            ->andWhere($queryBuilder->expr()->notIn('p.id', $this->getInteractedPublicationIds($user)))
            ->orderBy('p.id', 'DESC');

        $recommendedPublications = $queryBuilder->getQuery()->getResult();

        return $recommendedPublications;
    }

    private function getInteractedPublicationIds(Users $user): array
    {
        // Get the IDs of the publications that the user has interacted with
        $interactedPublicationIds = [];

        // Get the IDs of the publications that the user liked
        $userLikes = $this->entityManager->getRepository(React::class)->findBy([
            'id_user' => $user,
            'likeCount' => 1, // Only consider likes
        ]);
        foreach ($userLikes as $like) {
            $interactedPublicationIds[] = $like->getIdPub()->getId();
        }

        // Get the IDs of the publications that the user viewed
        $userViews = $this->entityManager->getRepository(PublicationView::class)->findBy([
            'id_user' => $user,
        ]);
        foreach ($userViews as $view) {
            $interactedPublicationIds[] = $view->getIdPub()->getId();
        }

        return $interactedPublicationIds;
    }
}
