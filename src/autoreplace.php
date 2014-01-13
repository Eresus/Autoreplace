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
class TAutoReplace extends TListContentPlugin
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
     * Таблица БД
     * @var array
     * @deprecated
     */
    public $table = array(
        'name' => 'autoreplace',
        'key' => 'id',
        'sortMode' => 'position',
        'sortDesc' => false,
        'columns' => array(
            array('name' => 'caption', 'caption' => 'Замена'),
        ),
        'controls' => array(
            'delete' => '',
            'edit' => '',
            'position' => '',
            'toggle' => '',
        ),
        'tabs' => array(
            'width' => '180px',
            'items' => array(
                array('caption' => strAdd, 'name' => 'action', 'value' => 'create')
            ),
        )
    );

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
        $driver->createTable(ORM::getTable($this, 'Autoreplace'));
    }

    /**
     * Действия при удалении модуля
     */
    public function uninstall()
    {
        $driver = ORM::getManager()->getDriver();
        $driver->dropTable(ORM::getTable($this, 'Autoreplace'));
        parent::install();
    }

    /**
     * Добавляет запись в БД
     */
    public function insert()
    {
        $db = Eresus_CMS::getLegacyKernel()->db;
        $item['active'] = true;
        $item['position'] = $db->count($this->table['name']);
        $item['caption'] = arg('caption', 'dbsafe');
        $item['src'] = arg('src', 'dbsafe');
        $item['dst'] = arg('dst', 'dbsafe');
        $item['re'] = arg('re', 'int');
        $db->insert($this->table['name'], $item);
        HTTP::redirect(arg('submitURL'));
    }

    /**
     * Обновляет запись в БД
     */
    public function update()
    {
        $db = Eresus_CMS::getLegacyKernel()->db;
        $item = $db->selectItem($this->table['name'], "`id`='" . arg('update', 'int') . "'");
        $item['active'] = true;
        $item['caption'] = arg('caption', 'dbsafe');
        $item['src'] = arg('src', 'dbsafe');
        $item['dst'] = arg('dst', 'dbsafe');
        $item['re'] = arg('re', 'int');
        $db->updateItem($this->table['name'], $item, "`id`='" . $item['id'] . "'");
        HTTP::redirect(arg('submitURL'));
    }

    /**
     * Диалог добавления автозамены
     *
     * @return string
     */
    public function adminAddItem()
    {
        $form = array(
            'name' => 'AddForm',
            'caption' => 'Добавить автозамену',
            'width' => '100%',
            'fields' => array(
                array('type' => 'hidden', 'name' => 'action', 'value' => 'insert'),
                array('type' => 'edit', 'name' => 'caption', 'label' => 'Название', 'width' => '100%',
                    'maxlength' => '255'),
                array('type' => 'edit', 'name' => 'src', 'label' => 'Что заменять', 'width' => '100%',
                    'maxlength' => '255', 'pattern' => '/.+/',
                    'errormsg' => 'Вы должны указать текст в поле "Что заменять"'),
                array('type' => 'checkbox', 'name' => 're', 'label' => 'Регулярное выражение'),
                array('type' => 'edit', 'name' => 'dst', 'label' => 'На что заменять', 'width' => '100%',
                    'maxlength' => '255'),
            ),
            'buttons' => array('ok', 'cancel'),
        );

        /** @var TAdminUI $page */
        $page = Eresus_Kernel::app()->getPage();
        $result = $page->renderForm($form);
        return $result;
    }

    /**
     * Диалог изменения автозамены
     * @return string
     */
    public function adminEditItem()
    {
        $db = Eresus_CMS::getLegacyKernel()->db;
        $item = $db->selectItem($this->table['name'], "`id`='" . arg('id') . "'");
        $form = array(
            'name' => 'EditForm',
            'caption' => 'Редактировать автозамену',
            'width' => '500px',
            'fields' => array(
                array('type' => 'hidden', 'name' => 'update', 'value' => $item['id']),
                array('type' => 'edit', 'name' => 'caption', 'label' => 'Название', 'width' => '100%',
                    'maxlength' => '255'),
                array('type' => 'edit', 'name' => 'src', 'label' => 'Что заменять', 'width' => '100%',
                    'maxlength' => '255', 'pattern' => '/.+/',
                    'errormsg' => 'Вы должны указать текст в поле "Что заменять"'),
                array('type' => 'checkbox', 'name' => 're', 'label' => 'Регулярное выражение'),
                array('type' => 'edit', 'name' => 'dst', 'label' => 'На что заменять', 'width' => '100%',
                    'maxlength' => '255'),
            ),
            'buttons' => array('ok', 'apply', 'cancel'),
        );

        /** @var TAdminUI $page */
        $page = Eresus_Kernel::app()->getPage();
        $result = $page->renderForm($form, $item);
        return $result;
    }

    /**
     * Орисовывает интерфейс
     *
     * @return string
     */
    public function adminRender()
    {
        return $this->adminRenderContent();
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
}

