<?php
/**
 * Таблица замен
 *
 * @version ${product.version}
 *
 * @copyright 2014, Михаил Красильников <m.krasilnikov@yandex.ru>
 * @license http://www.gnu.org/licenses/gpl.txt    GPL License 3
 * @license Только для внутреннего пользования
 * @author Михаил Красильников <m.krasilnikov@yandex.ru>
 *
 * Данная программа является свободным программным обеспечением. Вы
 * вправе распространять ее и/или модифицировать в соответствии с
 * условиями версии 3 либо (по вашему выбору) с условиями более поздней
 * версии Стандартной Общественной Лицензии GNU, опубликованной Free
 * Software Foundation.
 *
 * Мы распространяем эту программу в надежде на то, что она будет вам
 * полезной, однако НЕ ПРЕДОСТАВЛЯЕМ НА НЕЕ НИКАКИХ ГАРАНТИЙ, в том
 * числе ГАРАНТИИ ТОВАРНОГО СОСТОЯНИЯ ПРИ ПРОДАЖЕ и ПРИГОДНОСТИ ДЛЯ
 * ИСПОЛЬЗОВАНИЯ В КОНКРЕТНЫХ ЦЕЛЯХ. Для получения более подробной
 * информации ознакомьтесь со Стандартной Общественной Лицензией GNU.
 *
 * Вы должны были получить копию Стандартной Общественной Лицензии
 * GNU с этой программой. Если Вы ее не получили, смотрите документ на
 * <http://www.gnu.org/licenses/>
 */


/**
 * Таблица замен
 */
class AutoReplace_Entity_Table_Replace extends ORM_Table
{
    protected function setTableDefinition()
    {
        $this->setTableName($this->getPlugin()->getName());
        $this->hasColumns(array(
            'id' => array(
                'type' => 'integer',
                'unsigned' => true,
                'autoincrement' => true,
            ),
            'active' => array(
                'type' => 'boolean',
                'default' => '1',
            ),
            'position' => array(
                'type' => 'integer',
                'unsigned' => true,
            ),
            'caption' => array(
                'type' => 'string',
                'length' => 255,
                'default' => '',
            ),
            'src' => array(
                'type' => 'string',
                'length' => 255,
                'default' => '',
            ),
            'dst' => array(
                'type' => 'string',
                'length' => 255,
                'default' => '',
            ),
            're' => array(
                'type' => 'boolean',
                'default' => '0',
            ),
        ));
        $this->index('active', array('fields' => array('active')));
        $this->index('position', array('fields' => array('position')));
        $this->setOrdering('position');
    }
}

