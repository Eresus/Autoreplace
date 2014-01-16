<?php
/**
 * Автозамена фрагментов страницы
 *
 * @version ${product.version}
 *
 * @copyright 2008, Михаил Красильников <m.krasilnikov@yandex.ru>
 * @license http://www.gnu.org/licenses/gpl.txt  GPL License 3
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
 * Основной класс модуля
 */
class AutoReplace extends ContentPlugin
{
    /**
     * Версия модуля
     *
     * @var string
     */
    public $version = '${product.version}';

    /**
     * Требуемая версия CMS
     * @var string
     */
    public $kernel = '3.01a';

    /**
     * Название модуля
     *
     * @var string
     */
    public $title = 'Автозамена';

    /**
     * Описание модуля
     *
     * @var string
     */
    public $description = 'Автозамена фрагментов страницы';

    /**
     * Конструктор модуля
     */
    public function __construct()
    {
        parent::__construct();
        $evd = Eresus_Kernel::app()->getEventDispatcher();
        $evd->addListener('cms.client.render_page', array($this, 'clientOnPageRender'));
        $evd->addListener('cms.admin.start', array($this, 'adminOnMenuRender'));
    }

    /**
     * Действия при установке модуля
     */
    public function install()
    {
        parent::install();
        $driver = ORM::getManager()->getDriver();
        $driver->createTable(ORM::getTable($this, 'Replace'));
    }

    /**
     * Действия при удалении модуля
     */
    public function uninstall()
    {
        $driver = ORM::getManager()->getDriver();
        $driver->dropTable(ORM::getTable($this, 'Replace'));
        parent::install();
    }

    /**
     * Орисовывает АИ
     *
     * @param Eresus_CMS_Request $request
     *
     * @return string|Eresus_HTTP_Response
     */
    public function adminRender(Eresus_CMS_Request $request)
    {
        $controller = new AutoReplace_Controller_Admin($this);
        return $controller->getHtml($request);
    }

    /**
     * Проводит замены
     *
     * @param Eresus_Event_Render $event
     */
    public function clientOnPageRender(Eresus_Event_Render $event)
    {
        $db = Eresus_CMS::getLegacyKernel()->db;
        $items = $db->select($this->table['name'], '`active`=1', $this->table['sortMode'],
            $this->table['sortDesc']);
        if (count($items))
        {
            $text = $event->getText();
            foreach ($items as $item)
            {
                if ($item['re'])
                {
                    $text = preg_replace($item['src'], $item['dst'], $text);
                }
                else
                {
                    $text = str_replace($item['src'], $item['dst'], $text);
                }
            }
            $event->setText($text);
        }
    }

    /**
     * Добавляет пункт в меню «Расширения»
     */
    public function adminOnMenuRender()
    {
        /* @var TAdminUI $page */
        $page = Eresus_Kernel::app()->getPage();
        $page->addMenuItem('Расширения', array("access" => EDITOR, "link" => $this->getName(),
            "caption" => $this->title, "hint" => $this->description));
    }

    /**
     * Возвращает замену по идентификатору
     *
     * @param int $id
     *
     * @return AutoReplace_Entity_Replace
     *
     * @throws Eresus_CMS_Exception_NotFound
     *
     * @since 2.01
     */
    private function findReplace($id)
    {
        $table = ORM::getTable($this, 'Replace');
        $replace = $table->find(arg('update', 'int'));
        if (is_null($replace))
        {
            throw new Eresus_CMS_Exception_NotFound;
        }
        return $replace;
    }
}

