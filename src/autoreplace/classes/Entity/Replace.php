<?php
/**
 * Замена
 *
 * @version ${product.version}
 *
 * @copyright 2014, Михаил Красильников <m.krasilnikov@yandex.ru>
 * @license http://www.gnu.org/licenses/gpl.txt	GPL License 3
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
 * Замена
 *
 * @property int    $id        идентификатор
 * @property bool   $active    вкл/выкл
 * @property int    $position  порядковый номер
 * @property string $caption   подпись
 * @property string $src       исходная строка
 * @property string $dst       замена
 * @property bool   $re        если true, то $src — регулярное выражение
 */
class AutoReplace_Entity_Replace extends ORM_Entity
{
    /**
     * Вызывается перед изменением в БД
     *
     * @param ezcQuery|ezcQueryInsert|ezcQueryUpdate $query  запрос
     *
     * @return ezcQuery
     *
     * @since x.xx
     */
    public function beforeSave(ezcQuery $query)
    {
        if ($this->getEntityState() == ORM_Entity::IS_NEW)
        {
            $this->position = $this->getTable()->count();
            $query->set('position',
                $query->bindValue($this->position, ':position', PDO::PARAM_INT));
        }
        return $query;
    }
}

