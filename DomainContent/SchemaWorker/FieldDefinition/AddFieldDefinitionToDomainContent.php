<?php
/**
 * Created by PhpStorm.
 * User: bdunogier
 * Date: 23/09/2018
 * Time: 23:45
 */

namespace BD\EzPlatformGraphQLBundle\DomainContent\SchemaWorker\FieldDefinition;

use BD\EzPlatformGraphQLBundle\DomainContent\FieldValueBuilder\FieldValueBuilder;
use BD\EzPlatformGraphQLBundle\DomainContent\SchemaWorker\BaseWorker;
use BD\EzPlatformGraphQLBundle\DomainContent\SchemaWorker\SchemaWorker;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;


class AddFieldDefinitionToDomainContent extends BaseWorker implements SchemaWorker
{
    /**
     * @var FieldValueBuilder[]
     */
    private $fieldValueBuilders;
    /**
     * @var FieldValueBuilder
     */
    private $defaultFieldValueBuilder;

    public function __construct(FieldValueBuilder $defaultFieldValueBuilder, array $fieldValueBuilders = [])
    {
        $this->fieldValueBuilders = $fieldValueBuilders;
        $this->defaultFieldValueBuilder = $defaultFieldValueBuilder;
    }

    public function work(array &$schema, array $args)
    {
        $fieldDefinition = $args['FieldDefinition'];
        $fieldDefinitionField = $this->getFieldDefinitionField($args['FieldDefinition']);
        $domainContentName = $this->getDomainContentName($args['ContentType']);

        $schema[$domainContentName]['config']['fields'][$fieldDefinitionField] = $this->getDefinition($fieldDefinition);

        $schema
            [$this->getNameHelper()->domainContentTypeName($args['ContentType'])]
            ['config']['fields']
            [$fieldDefinitionField] = [
                'type' => $this->getFieldDefinitionType($args['FieldDefinition']),
                'description' => $fieldDefinition->getDescriptions()['eng-GB'] ?? '',
                'resolve' => sprintf(
                    '@=value.getFieldDefinition("%s")',
                    $args['FieldDefinition']->identifier
                ),
            ];
    }

    private function getDefinition(FieldDefinition $fieldDefinition)
    {
        return isset($this->fieldValueBuilders[$fieldDefinition->fieldTypeIdentifier])
            ? $this->fieldValueBuilders[$fieldDefinition->fieldTypeIdentifier]->buildDefinition($fieldDefinition)
            : $this->defaultFieldValueBuilder->buildDefinition($fieldDefinition);
    }

    public function canWork(array $schema, array $args)
    {
        return
            isset($args['FieldDefinition'])
            && $args['FieldDefinition'] instanceof FieldDefinition
            & isset($args['ContentType'])
            && $args['ContentType'] instanceof ContentType
            && !$this->isFieldDefined($schema, $args);
    }

    /**
     * @param ContentType $contentType
     * @return string
     */
    protected function getDomainContentName(ContentType $contentType): string
    {
        return $this->getNameHelper()->domainContentName($contentType);
    }

    /**
     * @param FieldDefinition $fieldDefinition
     * @return string
     */
    protected function getFieldDefinitionField(FieldDefinition $fieldDefinition): string
    {
        return $this->getNameHelper()->fieldDefinitionField($fieldDefinition);
    }

    private function isFieldDefined($schema, $args)
    {
        return isset(
            $schema[$this->getDomainContentName($args['ContentType'])]
                   ['config']['fields']
                   [$this->getFieldDefinitionField($args['FieldDefinition'])]);
    }

    private function getFieldDefinitionType(FieldDefinition $fieldDefinition)
    {
        $map = [
            'ezbinaryfile' => 'BinaryFieldDefinition',
            'ezboolean' => 'CheckboxFieldDefinition',
            'ezcountry' => 'CountryFieldDefinition',
            'ezmediafile' => 'CheckboxFieldDefinition',
            'ezfloat' => 'FloatFieldDefinition',
            'ezimage' => 'BinaryFieldDefinition',
            'ezinteger' => 'IntegerFieldDefinition',
            'ezmedia' => 'MediaFieldDefinition',
            'ezobjectrelation' => 'RelationFieldDefinition',
            'ezobjectrelationlist' => 'RelationListFieldDefinition',
            'ezstring' => 'TextLineFieldDefinition',
            'ezselection' => 'SelectionFieldDefinition',
            'eztext' => 'TextBlockFieldDefinition',
        ];

        return isset($map[$fieldDefinition->fieldTypeIdentifier])
            ? $map[$fieldDefinition->fieldTypeIdentifier]
            : 'FieldDefinition';
    }
}