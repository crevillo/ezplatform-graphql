<?php

namespace EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

class ContentTypeId implements SearchCriterion
{
    public function map($value): array
    {
        return [new Query\Criterion\ContentTypeId($value)];
    }

}