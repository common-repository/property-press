<?php

/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Photos
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Access.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Gdata_Extension
 */
require_once WP_PLUGIN_DIR.'/property-press/library/Zend/Gdata/Extension.php';

/**
 * @see Zend_Gdata_Photos
 */
require_once WP_PLUGIN_DIR.'/property-press/library/Zend/Gdata/Photos.php';

/**
 * Represents the gphoto:access element used by the API.
 * This determines the visibility for an album, and can be either
 * the strings 'private' or 'public'.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Photos
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Photos_Extension_Access extends Zend_Gdata_Extension
{

    protected $_rootNamespace = 'gphoto';
    protected $_rootElement = 'access';

    /**
     * Constructs a new Zend_Gdata_Photos_Extension_Access object.
     *
     * @param string $text (optional) The value to represent.
     */
    public function __construct($text = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Photos::$namespaces);
        parent::__construct();
        $this->setText($text);
    }

}
