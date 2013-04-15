<?php
/**
 * Автозамена фрагментов страницы
 *
 * @version 2.00
 *
 * @copyright 2008, Eresus Group, http://eresus.ru/
 * @license http://www.gnu.org/licenses/gpl.txt  GPL License 3
 * @author Михаил Красильников <mihalych@vsepofigu.ru>
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

	public function __construct()
	{
		parent::__construct();
		$plugins = Eresus_CMS::getLegacyKernel()->plugins;
		$plugins->events['clientOnPageRender'][] = $this->name;
		$plugins->events['adminOnMenuRender'][] = $this->name;
	}

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

	public function update()
	{
		$db = Eresus_CMS::getLegacyKernel()->db;
		$item = $db->selectItem($this->table['name'], "`id`='".arg('update', 'int')."'");
		$item['active'] = true;
		$item['caption'] = arg('caption', 'dbsafe');
		$item['src'] = arg('src', 'dbsafe');
		$item['dst'] = arg('dst', 'dbsafe');
		$item['re'] = arg('re', 'int');
		$db->updateItem($this->table['name'], $item, "`id`='".$item['id']."'");
		HTTP::redirect(arg('submitURL'));
	}

	public function adminAddItem()
	{
		$form = array(
			'name' => 'AddForm',
			'caption' => 'Добавить автозамену',
			'width'=>'100%',
			'fields' => array (
				array ('type' => 'hidden', 'name' => 'action', 'value' => 'insert'),
				array ('type' => 'edit', 'name' => 'caption', 'label' => 'Название', 'width' => '100%',
					'maxlength' => '255'),
				array ('type' => 'edit', 'name' => 'src', 'label' => 'Что заменять', 'width' => '100%',
					'maxlength' => '255', 'pattern' => '/.+/',
					'errormsg' => 'Вы должны указать текст в поле "Что заменять"'),
				array ('type' => 'checkbox', 'name' => 're', 'label' => 'Регулярное выражение'),
				array ('type' => 'edit', 'name' => 'dst', 'label' => 'На что заменять', 'width' => '100%',
					'maxlength' => '255'),
			),
			'buttons' => array('ok', 'cancel'),
		);

		/** @var TAdminUI $page */
		$page = Eresus_Kernel::app()->getPage();
		$result = $page->renderForm($form);
		return $result;
	}

	public function adminEditItem()
	{
		$db = Eresus_CMS::getLegacyKernel()->db;
		$item = $db->selectItem($this->table['name'], "`id`='".arg('id')."'");
		$form = array(
			'name' => 'EditForm',
			'caption' => 'Редактировать автозамену',
			'width' => '500px',
			'fields' => array (
				array('type'=>'hidden','name'=>'update', 'value'=>$item['id']),
				array ('type' => 'edit', 'name' => 'caption', 'label' => 'Название', 'width' => '100%',
					'maxlength' => '255'),
				array ('type' => 'edit', 'name' => 'src', 'label' => 'Что заменять', 'width' => '100%',
					'maxlength' => '255', 'pattern' => '/.+/',
					'errormsg' => 'Вы должны указать текст в поле "Что заменять"'),
				array ('type' => 'checkbox', 'name' => 're', 'label' => 'Регулярное выражение'),
				array ('type' => 'edit', 'name' => 'dst', 'label' => 'На что заменять', 'width' => '100%',
					'maxlength' => '255'),
			),
			'buttons' => array('ok', 'apply', 'cancel'),
		);

		/** @var TAdminUI $page */
		$page = Eresus_Kernel::app()->getPage();
		$result = $page->renderForm($form, $item);
		return $result;
	}

	public function adminRender()
	{
		return $this->adminRenderContent();
	}

	public function clientOnPageRender($text)
	{
		$db = Eresus_CMS::getLegacyKernel()->db;
		$items = $db->select($this->table['name'], '`active`=1', $this->table['sortMode'],
			$this->table['sortDesc']);
		if (count($items)) 
		{
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
		}
		return $text;
	}

	public function adminOnMenuRender()
	{
		/* @var TAdminUI $page */
		$page = Eresus_Kernel::app()->getPage();
		$page->addMenuItem('Расширения', array ("access"  => EDITOR, "link"  => $this->name,
			"caption"  => $this->title, "hint"  => $this->description));
	}
}

