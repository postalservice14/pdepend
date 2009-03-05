<?php
/**
 * This file is part of PHP_Depend.
 *
 * PHP Version 5
 *
 * Copyright (c) 2008-2009, Manuel Pichler <mapi@pdepend.org>.
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
 * @subpackage Code
 * @author     Manuel Pichler <mapi@pdepend.org>
 * @copyright  2008-2009 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.manuel-pichler.de/
 */

require_once 'PHP/Depend/Code/NodeI.php';

/**
 * Abstract base class for code item.
 *
 * @category   QualityAssurance
 * @package    PHP_Depend
 * @subpackage Code
 * @author     Manuel Pichler <mapi@pdepend.org>
 * @copyright  2008-2009 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.manuel-pichler.de/
 */
abstract class PHP_Depend_Code_AbstractItem implements PHP_Depend_Code_NodeI
{
    /**
     * The name for this item.
     *
     * @var string $name
     */
    protected $name = '';

    /**
     * The unique identifier for this function.
     *
     * @var PHP_Depend_Util_UUID $uuid
     */
    protected $uuid = null;

    /**
     * The line number where the item declaration starts.
     *
     * @var integer $startLine
     */
    protected $startLine = 0;

    /**
     * The line number where the item declaration ends.
     *
     * @var integer $endLine
     */
    protected $endLine = 0;

    /**
     * The source file for this item.
     *
     * @var PHP_Depend_Code_File $sourceFile
     */
    protected $sourceFile = null;

    /**
     * The comment for this type.
     *
     * @var string $docComment
     */
    protected $docComment = null;

    /**
     * Constructs a new item for the given <b>$name</b>.
     *
     * @param string $name The item name.
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->uuid = new PHP_Depend_Util_UUID();
    }

    /**
     * Returns the item name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns a uuid for this code node.
     *
     * @return string
     */
    public function getUUID()
    {
        return (string) $this->uuid;
    }

    /**
     * Returns the line number where the item declaration can be found.
     *
     * @return integer
     */
    public function getStartLine()
    {
        return $this->startLine;
    }

    /**
     * Sets the start line for this item.
     *
     * @param integer $startLine The start line for this item.
     *
     * @return void
     */
    public function setStartLine($startLine)
    {
        if ($this->startLine === 0) {
            $this->startLine = $startLine;
        }
    }

    /**
     * Returns the line number where the item declaration ends.
     *
     * @return integer The last source line for this item.
     */
    public function getEndLine()
    {
        return $this->endLine;
    }

    /**
     * Sets the end line for this item.
     *
     * @param integer $endLine The end line for this item
     *
     * @return void
     */
    public function setEndLine($endLine)
    {
        if ($this->endLine === 0) {
            $this->endLine = $endLine;
        }
    }

    /**
     * Returns the source file for this item.
     *
     * @return PHP_Depend_Code_File
     */
    public function getSourceFile()
    {
        return $this->sourceFile;
    }

    /**
     * Sets the source file for this item.
     *
     * @param PHP_Depend_Code_File $sourceFile The item source file.
     *
     * @return void
     */
    public function setSourceFile(PHP_Depend_Code_File $sourceFile)
    {
        if ($this->sourceFile === null || $this->sourceFile->getName() === null) {
            $this->sourceFile = $sourceFile;
        }
    }

    /**
     * Returns the doc comment for this item or <b>null</b>.
     *
     * @return string
     */
    public function getDocComment()
    {
        return $this->docComment;
    }

    /**
     * Sets the doc comment for this item.
     *
     * @param string $docComment The doc comment block.
     *
     * @return void
     */
    public function setDocComment($docComment)
    {
        $this->docComment = $docComment;
    }
}