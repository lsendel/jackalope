<?php
namespace jackalope\tests\JackalopeObjects;

use \PHPUnit_Framework_Constraint_IsType;

require_once(dirname(__FILE__) . '/../inc/JackalopeObjectsCase.php');

/**
 * Test the Node Type.
 * TODO this needs some cleanup and we should verify if its complete
 * TODO: tests for ItemDefinition, PropertyDefinition and NodeDefinition probably missing or incomplete inside NodeType
 * @covers NodeType
 */
class NodeType extends \jackalope\JackalopeObjectsCase {

    public function testNodeTypeMethods() {
        $ntm = $this->getNodeTypeManager();
        $nt = $ntm->getNodeType('nt:configuration');
        $this->assertSame(array($ntm->getNodeType('mix:versionable'),$ntm->getNodeType('mix:referenceable'),$ntm->getNodeType('mix:simpleVersionable'), $ntm->getNodeType('nt:base')),$nt->getSupertypes());
        $this->assertSame(array($ntm->getNodeType('mix:versionable'), $ntm->getNodeType('nt:base')),$nt->getDeclaredSupertypes());
        $declaredSubTypes = $nt->getDeclaredSubtypes();
        $this->assertType('\jackalope\NodeType\NodeTypeIterator', $declaredSubTypes);
        $this->assertSame(0, $declaredSubTypes->getSize());
        $subTypes = $nt->getSubtypes();
        $this->assertType('\jackalope\NodeType\NodeTypeIterator', $subTypes);
        $this->assertSame(0, $subTypes->getSize());
        $this->assertSame(true,$nt->isNodeType('nt:configuration'));
        $this->assertSame(true,$nt->isNodeType('nt:base'));
        $this->assertSame(true,$nt->isNodeType('mix:simpleVersionable'));
        $this->assertSame(false,$nt->isNodeType('notanodetype'));
        $expectedProperties = array('jcr:root', 'jcr:predecessors', 'jcr:configuration', 'jcr:activity', 'jcr:mergeFailed', 'jcr:versionHistory', 'jcr:baseVersion', 'jcr:uuid', 'jcr:isCheckedOut', 'jcr:mixinTypes', 'jcr:primaryType');
        $this->assertSame(count($expectedProperties), count($nt->getPropertyDefinitions()));
        $i = 0;
        foreach ($nt->getPropertyDefinitions() as $propDef) {
            $this->assertType('\jackalope\NodeType\PropertyDefinition', $propDef);
            $this->assertSame($expectedProperties[$i], $propDef->getName());
            $i++;
        }
        $this->assertSame(array(),$nt->getChildNodeDefinitions());

        $nt = $ntm->getNodeType('nt:hierarchyNode');
        $declaredSubTypes = $nt->getDeclaredSubtypes();
        $this->assertType('\jackalope\NodeType\NodeTypeIterator', $declaredSubTypes);
        $this->assertSame(5, $declaredSubTypes->getSize());
        $subnode = $declaredSubTypes->nextNodeType();
        $this->assertType('\jackalope\NodeType\NodeType', $subnode);
        $this->assertSame('nt:file', $subnode->getName());
        $subTypes = $nt->getSubtypes();
        $this->assertType('\jackalope\NodeType\NodeTypeIterator', $subTypes);
        $this->assertSame(7, $subTypes->getSize());
        $subTypes->skip(4);
        $subnode = $subTypes->nextNodeType();
        $this->assertType('\jackalope\NodeType\NodeType', $subnode);
        $this->assertSame('rep:Group', $subnode->getName());

        $nt = $ntm->getNodeType('rep:PrincipalAccessControl');
        $expectedChildNodes = array('rep:policy', '*', '*');
        $this->assertSame(count($expectedChildNodes), count($nt->getChildNodeDefinitions()));
        $i = 0;
        foreach ($nt->getChildNodeDefinitions() as $childNode) {
            $this->assertType('\jackalope\NodeType\NodeDefinition', $childNode);
            $this->assertSame($expectedChildNodes[$i], $childNode->getName());
            $i++;
        }
    }

    public function testGetDefinedChildNodesAndNodeDefinitions() {
        $ntm = $this->getNodeTypeManager();
        $nt = $ntm->getNodeType('nt:folder');
        $nodes = $nt->getDeclaredChildNodeDefinitions();
        $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $nodes);
        $this->assertEquals(1, count($nodes));
        $node = $nodes[0];
        $this->assertType('\jackalope\NodeType\NodeDefinition', $node);
        $this->assertSame('*', $node->getName());
        $this->assertSame(array($ntm->getNodeType('nt:hierarchyNode')), $node->getRequiredPrimaryTypes());
        $this->assertSame(array('nt:hierarchyNode'), $node->getRequiredPrimaryTypeNames());
        $this->assertSame(null, $node->getDefaultPrimaryTypeName());
        $this->assertSame(null, $node->getDefaultPrimaryType());
        $this->assertSame(false, $node->allowsSameNameSiblings());

        $nt = $ntm->getNodeType('nt:file');
        $nodes = $nt->getDeclaredChildNodeDefinitions();
        $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $nodes);
        $this->assertEquals(1, count($nodes));
        $node = $nodes[0];
        $this->assertType('\jackalope\NodeType\NodeDefinition', $node);
        $this->assertSame('jcr:content', $node->getName());
        $this->assertSame(array($ntm->getNodeType('nt:base'), $ntm->getNodeType('nt:folder')), $node->getRequiredPrimaryTypes());
        $this->assertSame(array('nt:base', 'nt:folder'), $node->getRequiredPrimaryTypeNames());
        $this->assertSame(null, $node->getDefaultPrimaryTypeName());
        $this->assertSame(null, $node->getDefaultPrimaryType());

        //Test defaultPrimaryType
        $nt = $ntm->getNodeType('nt:nodeType');
        $nodes = $nt->getDeclaredChildNodeDefinitions();
        $this->assertEquals(2, count($nodes));
        $node = $nodes[0];
        $this->assertSame('nt:childNodeDefinition', $node->getDefaultPrimaryTypeName());
        $this->assertSame($ntm->getNodeType('nt:childNodeDefinition'), $node->getDefaultPrimaryType());
        $this->assertSame(true, $node->allowsSameNameSiblings());
    }

    public function testGetDefinedPropertysAndPropertyDefinition() {
        $ntm = $this->getNodeTypeManager();
        $nt = $ntm->getNodeType('nt:file');
        $properties = $nt->getDeclaredPropertyDefinitions();
        $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $properties);
        $this->assertEquals(0, count($properties));

        $nt = $ntm->getNodeType('mix:created');
        $this->assertType('\jackalope\NodeType\NodeType', $nt);
        $this->assertSame('mix:created', $nt->getName());
        $this->assertSame(array(), $nt->getDeclaredSupertypeNames());
        $this->assertSame(false, $nt->isAbstract());
        $this->assertSame(true, $nt->isMixin());
        $this->assertSame(false, $nt->hasOrderableChildNodes());
        $this->assertSame(true, $nt->isQueryable());
        $this->assertSame(null, $nt->getPrimaryItemName());

        //ItemDefinition
        $properties = $nt->getDeclaredPropertyDefinitions();
        $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $properties);
        $this->assertEquals(2, count($properties));
        $property = $properties[0];
        $this->assertSame($nt, $property->getDeclaringNodeType());
        $this->assertSame('jcr:createdBy',$property->getName());
        $this->assertSame(true,$property->isAutoCreated());
        $this->assertSame(false,$property->isMandatory());
        $this->assertSame(\PHPCR_Version_OnParentVersionAction::COPY,$property->getOnParentVersion());
        $this->assertSame(true,$property->isProtected());
        $this->assertSame(array(),$property->getDefaultValues());

        //PropertyDefinition
        $this->assertSame(\PHPCR_PropertyType::STRING, $property->getRequiredType());
        $this->assertSame(array(), $property->getValueConstraints());
        $this->assertSame(false, $property->isMultiple());
        $this->assertSame(array('jcr.operator.equal.to', 'jcr.operator.not.equal.to', 'jcr.operator.greater.than', 'jcr.operator.greater.than.or.equal.to', 'jcr.operator.less.than', 'jcr.operator.less.than.or.equal.to', 'jcr.operator.like'), $property->getAvailableQueryOperators());
        $this->assertSame(true, $property->isFullTextSearchable());
        $this->assertSame(true, $property->isQueryOrderable());

        $nt = $ntm->getNodeType('mix:versionable');
        $properties = $nt->getDeclaredPropertyDefinitions();
        $property = $properties[0];
        $this->assertSame(array('nt:version'), $property->getValueConstraints());

        $nt = $ntm->getNodeType('mix:simpleVersionable');
        $properties = $nt->getDeclaredPropertyDefinitions();
        $property = $properties[0];
        $defaultValues = $property->getDefaultValues();
        $this->assertEquals(1, count($defaultValues));
        $this->assertType('\jackalope\Value', $defaultValues[0]);
        $this->assertSame('true', $defaultValues[0]->getString());
        $this->assertSame(true, $defaultValues[0]->getBoolean());
    }

    /**
     * @covers jackalope\NodeType\NodeTypeTemplate::__construct
     */
    public function testCreateNodeTypeTemplateEmpty() {
        $ntm = $this->getNodeTypeManager();

        $ntt = $ntm->createNodeTypeTemplate();

        // is empty as defined by doc
        $this->assertNull($ntt->getName());
        $this->assertEquals(array('nt:base'), $ntt->getDeclaredSupertypeNames());
        $this->assertFalse($ntt->isAbstract());
        $this->assertFalse($ntt->isMixin());
        $this->assertFalse($ntt->hasOrderableChildNodes());
        $this->assertFalse($ntt->isQueryable());
        $this->assertNull($ntt->getPrimaryItemName());
        $this->assertNull($ntt->getDeclaredPropertyDefinitions());
        $this->assertNull($ntt->getDeclaredChildNodeDefinitions());
    }

    /**
     * @covers jackalope\NodeType\NodeDefinitionTemplate::__construct
     */
    public function testCreateNodeDefinitionTemplateEmpty() {
        $ntm = $this->getNodeTypeManager();

        $ndt = $ntm->createNodeDefinitionTemplate();

        // is empty as defined by doc
        $this->assertNull($ndt->getName());
        $this->assertFalse($ndt->isAutoCreated());
        $this->assertFalse($ndt->isMandatory());
        $this->assertSame(\PHPCR_Version_OnParentVersionAction::COPY, $ndt->getOnParentVersion());
        $this->assertFalse($ndt->isProtected());
        $this->assertNull($ndt->getRequiredPrimaryTypeNames());
        $this->assertNull($ndt->getDefaultPrimaryTypeName());
        $this->assertFalse($ndt->allowsSameNameSiblings());
    }

    /**
     * @covers jackalope\NodeType\PropertyDefinitionTemplate::__construct
     */
    public function testCreatePropertyDefinitionTemplateEmpty() {
        $ntm = $this->getNodeTypeManager();

        $ndt = $ntm->createPropertyDefinitionTemplate();

        // is empty as defined by doc
        $this->assertNull($ndt->getName());
        $this->assertFalse($ndt->isAutoCreated());
        $this->assertFalse($ndt->isMandatory());
        $this->assertSame(\PHPCR_Version_OnParentVersionAction::COPY, $ndt->getOnParentVersion());
        $this->assertFalse($ndt->isProtected());
        $this->assertSame(\PHPCR_PropertyType::STRING, $ndt->getRequiredType());
        $this->assertNull($ndt->getValueConstraints());
        $this->assertNull($ndt->getDefaultValues());
        $this->assertFalse($ndt->isMultiple());
        $this->assertFalse($ndt->isFullTextSearchable());
        $this->assertFalse($ndt->isQueryOrderable());
    }
}

