<?php
/**
 * This file is part of PHP_Depend.
 *
 * PHP Version 5
 *
 * Copyright (c) 2008-2012, Manuel Pichler <mapi@pdepend.org>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Manuel Pichler nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   QualityAssurance
 * @package    PHP_Depend
 * @subpackage Metrics
 * @author     Manuel Pichler <mapi@pdepend.org>
 * @copyright  2008-2012 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://pdepend.org/
 */

use \PHP\Depend\AST\ASTClass;

/**
 * This analyzer calculates dependency metrics for packages.
 *
 * The metrics calculated by this analyzer are described in Robert C. Martin's
 * whitepaper "OO Design Quality Metrics - An Analysis of Dependencies". You can
 * find the original whitepaper here:
 *
 * - http://objectmentor.com/resources/articles/oodmetrc.pdf
 *
 * @category   QualityAssurance
 * @package    PHP_Depend
 * @subpackage Metrics
 * @author     Manuel Pichler <mapi@pdepend.org>
 * @copyright  2008-2012 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://pdepend.org/
 *
 * @todo 2.0 Implement NodeAware interface
 */
class PHP_Depend_Metrics_Dependency_Analyzer
    extends PHP_Depend_Metrics_AbstractAnalyzer
   implements PHP_Depend_Metrics_Analyzer
{
    /**
     * Type of this analyzer class.
     */
    const CLAZZ = __CLASS__;

    /**
     * Metrics provided by the analyzer implementation.
     */
    const M_NUMBER_OF_CLASSES        = 'tc',
        M_NUMBER_OF_CONCRETE_CLASSES = 'cc',
        M_NUMBER_OF_ABSTRACT_CLASSES = 'ac',
        M_AFFERENT_COUPLING          = 'ca',
        M_EFFERENT_COUPLING          = 'ce',
        M_ABSTRACTION                = 'a',
        M_INSTABILITY                = 'i',
        M_DISTANCE                   = 'd';

    /**
     * Template array structure of node metrics generated by the dependency
     * analyzer.
     *
     * @var array
     */
    private $metricTemplate = array(
        self::M_NUMBER_OF_CLASSES           => 0,
        self::M_NUMBER_OF_CONCRETE_CLASSES  => 0,
        self::M_NUMBER_OF_ABSTRACT_CLASSES  => 0,
        self::M_AFFERENT_COUPLING           => array(),
        self::M_EFFERENT_COUPLING           => array(),
        self::M_ABSTRACTION                 => 0,
        self::M_INSTABILITY                 => 0,
        self::M_DISTANCE                    => 0
    );

    /**
     * Hash with all calculated node metrics.
     *
     * <code>
     * array(
     *     '0375e305-885a-4e91-8b5c-e25bda005438'  =>  array(
     *         'loc'    =>  42,
     *         'ncloc'  =>  17,
     *         'cc'     =>  12
     *     ),
     *     'e60c22f0-1a63-4c40-893e-ed3b35b84d0b'  =>  array(
     *         'loc'    =>  42,
     *         'ncloc'  =>  17,
     *         'cc'     =>  12
     *     )
     * )
     * </code>
     *
     * @var array(string=>array) $_nodeMetrics
     */
    private $metrics = null;

    private $nodeSet = array();

    private $_efferentNodes = array();

    private $_afferentNodes = array();

    /**
     * This method will return an <b>array</b> with all generated metric values
     * for the given node or node identifier. If there are no metrics for the
     * requested node, this method will return an empty <b>array</b>.
     *
     * <code>
     * array(
     *     'noc'  =>  23,
     *     'nom'  =>  17,
     *     'nof'  =>  42
     * )
     * </code>
     *
     * @param PHP_Depend_AST_Node|string $node The context node instance.
     *
     * @return array
     * @todo 2.0 Refactor this into getNodeMetrics
     */
    public function getStats($node)
    {
        $nodeId = (string) is_object($node) ? $node->getId() : $node;

        if (isset($this->metrics[$nodeId])) {

            return $this->metrics[$nodeId];
        }
        return array();
    }

    /**
     * Returns an array of all afferent nodes.
     *
     * @param PHP_Depend_AST_Node $node The context node instance.
     *
     * @return PHP_Depend_AST_Node[]
     */
    public function getAfferents(PHP_Depend_AST_Node $node)
    {
        $afferent = array();
        if (isset($this->_afferentNodes[$node->getId()])) {

            $afferent = $this->_afferentNodes[$node->getId()];
        }
        return $afferent;
    }

    /**
     * Returns an array of all efferent nodes.
     *
     * @param PHP_Depend_AST_Node $node The context node instance.
     *
     * @return PHP_Depend_AST_Node[]
     */
    public function getEfferents(PHP_Depend_AST_Node $node)
    {
        $efferent = array();
        if (isset($this->_efferentNodes[$node->getId()])) {

            $efferent = $this->_efferentNodes[$node->getId()];
        }

        return $efferent;
    }

    /**
     * Returns an array of nodes that build a cycle for the requested node or it
     * returns <b>null</b> if no cycle exists .
     *
     * @param PHP_Depend_AST_Node $node The context node instance.
     *
     * @return PHP_Depend_AST_Node[]
     */
    public function getCycle(PHP_Depend_AST_Node $node)
    {
        if (array_key_exists($node->getId(), $this->_collectedCycles)) {

            return $this->_collectedCycles[$node->getId()];
        }

        $list = array();
        if ($this->collectCycle($list, $node)) {

            $this->_collectedCycles[$node->getId()] = $list;
        } else {

            $this->_collectedCycles[$node->getId()] = null;
        }

        return $this->_collectedCycles[$node->getId()];
    }

    /**
     * This method will be called when all nodes were processed. This analyzer
     * uses this method to calculate the final metrics from the collected values
     *
     * @return void
     */
    public function afterTraverse()
    {
        $this->calculateCoupling();

        $this->calculateAbstractness();
        $this->calculateInstability();
        $this->calculateDistance();
    }

    /**
     * Visits a namespace before it's child statements get visited. This method
     * returns a metric data container for the given namespace.
     *
     * @param PHP_Depend_AST_Namespace $namespace
     *
     * @return array
     */
    public function visitNamespaceBefore(PHP_Depend_AST_Namespace $namespace)
    {
        if (false === isset($this->metrics[$namespace->getId()])) {

            $this->metrics[$namespace->getId()] = $this->metricTemplate;
        }

        return $this->metrics[$namespace->getId()];
    }

    /**
     * Visits a namespace after it's child statements were visited. This method
     * aggregates the metrics for one namespace.
     *
     * @param PHP_Depend_AST_Namespace $namespace
     * @param array $data
     *
     * @return void
     */
    public function visitNamespaceAfter(PHP_Depend_AST_Namespace $namespace, $data)
    {
        $this->metrics[$namespace->getId()] = $data;

        return null;
    }

    /**
     * Visits a class and updates the efferent coupling metric in the given
     * <b>$data</b> array.
     *
     * @param \PHP\Depend\AST\ASTClass $class
     * @param array $data
     *
     * @return array
     */
    public function visitASTClassBefore(ASTClass $class, $data)
    {
        ++$data[self::M_NUMBER_OF_CLASSES];

        if ($class->isAbstract()) {

            ++$data[self::M_NUMBER_OF_ABSTRACT_CLASSES];
        } else {

            ++$data[self::M_NUMBER_OF_CONCRETE_CLASSES];
        }

        if ($parentClass = $class->getParentClass()) {

            $data = $this->updateEfferent($parentClass, $data);
        }

        foreach ($class->getInterfaces() as $interface) {

            $data = $this->updateEfferent($interface, $data);
        }
        return $data;
    }

    /**
     * Visits an interface and updates the efferent coupling metric in the given
     * <b>$data</b> array.
     *
     * @param PHP_Depend_AST_Interface $interface
     * @param array $data
     *
     * @return array
     */
    public function visitInterfaceBefore(PHP_Depend_AST_Interface $interface, $data)
    {
        ++$data[self::M_NUMBER_OF_CLASSES];
        ++$data[self::M_NUMBER_OF_ABSTRACT_CLASSES];

        foreach ($interface->getInterfaces() as $parentInterface) {

            $data = $this->updateEfferent($parentInterface, $data);
        }
        return $data;
    }

    /**
     * Visits a method and updates the efferent coupling metric in the given
     * <b>$data</b> array.
     *
     * @param PHP_Depend_AST_Method $method
     * @param array $data
     *
     * @return array
     */
    public function visitMethodBefore(PHP_Depend_AST_Method $method, $data)
    {
        if ($returnType = $method->getReturnType()) {

            $data = $this->updateEfferent($returnType, $data);
        }

        foreach ($method->getThrownExceptions() as $exception) {

            $data = $this->updateEfferent($exception, $data);
        }
        return $data;
    }

    /**
     * Visits a type reference and updates the efferent coupling metric in the
     * given <b>$data</b> array.
     *
     * @param PHP_Depend_AST_TypeRef $typeRef
     * @param array $data
     *
     * @return array
     */
    public function visitTypeRefBefore(PHP_Depend_AST_TypeRef $typeRef, $data)
    {
        return $this->updateEfferent($typeRef, $data);
    }

    /**
     * Updates the efferent coupling metric in <b>$data</b>.
     *
     * @param PHP_Depend_AST_Type $type
     * @param array $data
     *
     * @return array
     */
    private function updateEfferent(PHP_Depend_AST_Type $type, $data)
    {
        $namespace = $type->getNamespace();
        if ($namespace->isUserDefined()) {

            $data[self::M_EFFERENT_COUPLING][] = $namespace->getId();
        }
        return $data;
    }

    /**
     * Post processes all analyzed nodes.
     *
     * @return void
     */
    private function calculateCoupling()
    {
        foreach ($this->metrics as $uuid => $metric) {

            $efferent = array_filter(
                array_unique($metric[self::M_EFFERENT_COUPLING]),
                function($efferentId) use ($uuid)
                {
                    return ($efferentId !== $uuid);
                }
            );

            foreach ($efferent as $id) {

                if (isset($this->metrics[$id])) {

                    $this->metrics[$id][self::M_AFFERENT_COUPLING][] = $uuid;
                }
            }
        }

        foreach ($this->metrics as $id => $metric) {

            $efferent = count(array_unique($metric[self::M_EFFERENT_COUPLING]));
            $afferent = count(array_unique($metric[self::M_AFFERENT_COUPLING]));

            $this->metrics[$id][self::M_EFFERENT_COUPLING] = $efferent;
            $this->metrics[$id][self::M_AFFERENT_COUPLING] = $afferent;
        }
    }

    /**
     * Calculates the abstractness for all analyzed nodes.
     *
     * @return void
     */
    protected function calculateAbstractness()
    {
        foreach ($this->metrics as $uuid => $metrics) {

            if ($metrics[self::M_NUMBER_OF_CLASSES] !== 0) {

                $this->metrics[$uuid][self::M_ABSTRACTION] = (
                    $metrics[self::M_NUMBER_OF_ABSTRACT_CLASSES] /
                        $metrics[self::M_NUMBER_OF_CLASSES]
                );
            }
        }
    }

    /**
     * Calculates the instability for all analyzed nodes.
     *
     * @return void
     */
    protected function calculateInstability()
    {
        foreach ($this->metrics as $uuid => $metrics) {

            $total = (
                $metrics[self::M_AFFERENT_COUPLING] +
                    $metrics[self::M_EFFERENT_COUPLING]
            );

            if ($total !== 0) {

                $this->metrics[$uuid][self::M_INSTABILITY] = (
                    $metrics[self::M_EFFERENT_COUPLING] / $total
                );
            }
        }
    }

    /**
     * Calculates the distance to an optimal value.
     *
     * @return void
     */
    protected function calculateDistance()
    {
        foreach ($this->metrics as $uuid => $metrics) {

            $this->metrics[$uuid][self::M_DISTANCE] = abs(
                $metrics[self::M_ABSTRACTION] +
                    $metrics[self::M_INSTABILITY] - 1
            );
        }
    }

    /**
     * Collects a single cycle that is reachable by this package. All packages
     * that are part of the cycle are stored in the given <b>$list</b> array.
     *
     * @param PHP_Depend_AST_Package[] &$list Already visited packages.
     * @param PHP_Depend_AST_Package $package The context code package.
     *
     * @return boolean If this method detects a cycle the return value is
     *         <b>true</b> otherwise this method will return <b>false</b>.
     */
    protected function collectCycle(array &$list, PHP_Depend_AST_Package $package)
    {
        if (in_array($package, $list, true)) {
            $list[] = $package;
            return true;
        }

        $list[] = $package;

        foreach ($this->getEfferents($package) as $efferent) {
            if ($this->collectCycle($list, $efferent)) {
                return true;
            }
        }

        if (is_int($idx = array_search($package, $list, true))) {
            unset($list[$idx]);
        }
        return false;
    }
}
