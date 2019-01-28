<?php

namespace EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface;

interface SearchCriterion
{
    public function map($value) : array;
}
