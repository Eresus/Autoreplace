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
<<<<<<< HEAD
	public $name = 'autoreplace';
	public $version = '2.00';
	public $kernel = '3.00a';
	public $title = 'Автозамена';
	public $description = 'Автозамена фрагментов страницы';
	public $type = 'client,content,ondemand';
	public $table = array (
		'name' => 'autoreplace',
		'key'=> 'id',
		'sortMode' => 'position',
		'sortDesc' => false,
		'columns' => array(
			array('name' => 'caption', 'caption' => 'Замена'),
		),
		'controls' => array (
			'delete' => '',
			'edit' => '',
			'position' => '',
			'toggle' => '',
		),
		'tabs' => array(
			'width'=>'180px',
			'items'=>array(
				array('caption'=>strAdd, 'name'=>'action', 'value'=>'create')
			),
		),
		'sql' => "(
			`id` int(10) unsigned NOT NULL auto_increment,
			`active` tinyint(1) unsigned NOT NULL default '1',
			`position` int(10) unsigned default NULL,
			`caption` varchar(255) default '',
			`src` varchar(255) default '',
			`dst` varchar(255) default '',
			`re` tinyint(1) default '0',
			PRIMARY KEY  (`id`),
			KEY `active` (`active`),
			KEY `position` (`position`)
		) ENGINE=MyISAM;",
	);
=======
    /**
     * Версия модуля
     *
     * @var string
     */
    public $version = '${product.version}';
>>>>>>> release/v2.01

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
        $evd->addListener('cms.client.render_page', array($this, 'applyReplaces'));
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
    public function applyReplaces(Eresus_Event_Render $event)
    {
        $table = ORM::getTable($this, 'Replace');
        /** @var AutoReplace_Entity_Replace[] $replaces */
        $replaces = $table->findAllBy(array('active' => true));
        if (count($replaces) > 0)
        {
            $text = $event->getText();
            foreach ($replaces as $replace)
            {
                if ($replace->re)
                {
                    $text = preg_replace($replace->src, $replace->dst, $text);
                }
                else
                {
                    $text = str_replace($replace->src, $replace->dst, $text);
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

