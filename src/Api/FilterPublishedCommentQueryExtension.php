<?php
namespace App\Api;

use App\Entity\Comment;
use Doctrine\ORM\QueryBuilder;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;

class FilterPublishedCommentQueryExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context=[]):void
    {
        if ($resourceClass === Comment::class) {
            $queryBuilder->andWhere(sprintf("%s.state= 'published'", $queryBuilder->getRootAliases()[0]));
        }
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, Operation $operation = null, array $context=[]): void
    {
        if ($resourceClass === Comment::class) {
            $queryBuilder->andWhere(sprintf("%s.state= 'published'", $queryBuilder->getRootAliases()[0]));
        }
    }
}