<?php
namespace EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use EzSystems\EzPlatformGraphQL\Schema;
use EzSystems\EzPlatformGraphQL\Schema\Builder\Input;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\BaseWorker;

class DefineDomainContentMutation extends BaseWorker implements Schema\Worker, Schema\Initializer
{
    const MUTATION_TYPE = 'DomainContentMutation';

    public function init(Schema\Builder $schema)
    {
        $schema->addType(new Input\Type(
            self::MUTATION_TYPE,
            'object',
            ['inherits' => ['PlatformMutation']]
        ));
    }

    public function work(Schema\Builder $schema, array $args)
    {
        $contentType = $args['ContentType'];

        // ex: ArticleContentCreateInput
        $schema->addType(new Input\Type($this->getCreateInputName($contentType), 'input-object'));

        // ex: ArticleContentUpdateInput
        $schema->addType(new Input\Type($this->getUpdateInputName($contentType), 'input-object'));

        // ex: createArticle
        $schema->addFieldToType(self::MUTATION_TYPE,
            new Input\Field(
                $this->getCreateField($contentType),
                $this->getNameHelper()->domainContentName($contentType) . '!',
                [
                    'resolve' => sprintf(
                        '@=mutation("CreateDomainContent", [args["input"], "%s", args["parentLocationId"], args["language"]])',
                        $contentType->identifier
                )]
            )
        );

        $schema->addArgToField(
            self::MUTATION_TYPE,
            $this->getCreateField($contentType),
            new Input\Arg('input', $this->getCreateInputName($contentType) . '!')
        );

        $schema->addArgToField(
            self::MUTATION_TYPE,
            $this->getCreateField($contentType),
            $this->buildLanguageFieldInput()
        );

        $schema->addArgToField(
            self::MUTATION_TYPE,
            $this->getCreateField($contentType),
            new Input\Arg('parentLocationId', 'Int!')
        );

        // Update mutation field
        $schema->addFieldToType(
            self::MUTATION_TYPE,
            new Input\Field(
                $this->getUpdateField($contentType),
                $this->getNameHelper()->domainContentName($contentType) . '!',
                ['resolve' => '@=mutation("UpdateDomainContent", [args["input"], args, args["versionNo"], args["language"]])']
            )
        );

        $schema->addArgToField(
            self::MUTATION_TYPE,
            $this->getUpdateField($contentType),
            new Input\Arg('input', $this->getUpdateInputName($contentType) . '!')
        );

        $schema->addArgToField(
            self::MUTATION_TYPE,
            $this->getUpdateField($contentType),
            $this->buildLanguageFieldInput()
        );

        $schema->addArgToField(
            self::MUTATION_TYPE,
            $this->getUpdateField($contentType),
            new Input\Arg('id', 'ID', ['description' => 'ID of the content item to update'])
        );

        $schema->addArgToField(
            self::MUTATION_TYPE,
            $this->getUpdateField($contentType),
            new Input\Arg('contentId', 'Int', ['description' => 'Repository content ID of the content item to update'])
        );

        $schema->addArgToField(
            self::MUTATION_TYPE,
            $this->getUpdateField($contentType),
            new Input\Arg('versionNo', 'Int', ['description' => 'Optional version number to update. If it is a draft, it is saved, not published. If it is archived, it is used as the source version for the update, to complete missing fields.'])
        );
    }

    public function canWork(Schema\Builder $schema, array $args)
    {
        return isset($args['ContentType'])
               && $args['ContentType'] instanceof ContentType
               && !isset($args['FieldDefinition']);
    }

    /**
     * @param $contentType
     * @return string
     */
    protected function getCreateInputName($contentType): string
    {
        return $this->getNameHelper()->domainContentCreateInputName($contentType);
    }

    /**
     * @param $contentType
     * @return string
     */
    protected function getUpdateInputName($contentType): string
    {
        return $this->getNameHelper()->domainContentUpdateInputName($contentType);
    }

    /**
     * @param $contentType
     * @return string
     */
    protected function getCreateField($contentType): string
    {
        return $this->getNameHelper()->domainMutationCreateContentField($contentType);
    }

    /**
     * @param $contentType
     * @return string
     */
    protected function getUpdateField($contentType): string
    {
        return $this->getNameHelper()->domainMutationUpdateContentField($contentType);
    }

    /**
     * @return Input\Arg
     */
    private function buildLanguageFieldInput(): Input\Arg
    {
        return new Input\Arg('language', 'String', ['defaultValue' => 'eng-GB']);
    }
}