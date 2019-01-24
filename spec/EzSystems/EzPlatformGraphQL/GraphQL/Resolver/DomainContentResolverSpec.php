<?php

namespace spec\EzSystems\EzPlatformGraphQL\GraphQL\Resolver;

use eZ\Publish\Core\FieldType\RichText\Converter;
use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\SearchQueryMapper;
use EzSystems\EzPlatformGraphQL\GraphQL\Resolver\DomainContentResolver;
use eZ\Publish\API\Repository\Repository;
use Overblog\GraphQLBundle\Resolver\TypeResolver;
use PhpSpec\ObjectBehavior;

class DomainContentResolverSpec extends ObjectBehavior
{
    function let(
        Repository $repository,
        TypeResolver $typeResolver,
        SearchQueryMapper $searchQueryMapper,
        Converter $richTextConverter
    ) {
        $this->beConstructedWith($repository, $typeResolver, $searchQueryMapper, $richTextConverter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DomainContentResolver::class);
    }
}
