<?php
/**
 * ���������� ���������� ��������
 *
 * @version 2.00
 *
 * @copyright 2008, Eresus Group, http://eresus.ru/
 * @license http://www.gnu.org/licenses/gpl.txt  GPL License 3
 * @author ������ ������������ <mihalych@vsepofigu.ru>
 *
 * ������ ��������� �������� ��������� ����������� ������������. ��
 * ������ �������������� �� �/��� �������������� � ������������ �
 * ��������� ������ 3 ���� (�� ������ ������) � ��������� ����� �������
 * ������ ����������� ������������ �������� GNU, �������������� Free
 * Software Foundation.
 *
 * �� �������������� ��� ��������� � ������� �� ��, ��� ��� ����� ���
 * ��������, ������ �� ������������� �� ��� ������� ��������, � ���
 * ����� �������� ��������� ��������� ��� ������� � ����������� ���
 * ������������� � ���������� �����. ��� ��������� ����� ���������
 * ���������� ������������ �� ����������� ������������ ��������� GNU.
 *
 * �� ������ ���� �������� ����� ����������� ������������ ��������
 * GNU � ���� ����������. ���� �� �� �� ��������, �������� �������� ��
 * <http://www.gnu.org/licenses/>
 */

class TAutoReplace extends TListContentPlugin
{
	public $name = 'autoreplace';
	public $version = '2.00a';
	public $kernel = '2.10';
	public $title = '����������';
	public $description = '���������� ���������� ��������';
	public $type = 'client,content,ondemand';
	public $table = array (
		'name' => 'autoreplace',
		'key'=> 'id',
		'sortMode' => 'position',
		'sortDesc' => false,
		'columns' => array(
			array('name' => 'caption', 'caption' => '������'),
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
		) TYPE=MyISAM;",
	);

	public function __construct()
	{
		global $Eresus;

		parent::__construct();
		$Eresus->plugins->events['clientOnPageRender'][] = $this->name;
		$Eresus->plugins->events['adminOnMenuRender'][] = $this->name;
	}
	//-----------------------------------------------------------------------------

	public function insert()
	{
		global $Eresus;

		$item['active'] = true;
		$item['position'] = $Eresus->db->count($this->table['name']);
		$item['caption'] = arg('caption', 'dbsafe');
		$item['src'] = arg('src', 'dbsafe');
		$item['dst'] = arg('dst', 'dbsafe');
		$item['re'] = arg('re', 'int');
		$Eresus->db->insert($this->table['name'], $item);
		HTTP::redirect(arg('submitURL'));
	}
	//-----------------------------------------------------------------------------

	public function update()
	{
	global $Eresus, $page;

		$item = $Eresus->db->selectItem($this->table['name'], "`id`='".arg('update', 'int')."'");
		$item['active'] = true;
		$item['caption'] = arg('caption', 'dbsafe');
		$item['src'] = arg('src', 'dbsafe');
		$item['dst'] = arg('dst', 'dbsafe');
		$item['re'] = arg('re', 'int');
		$Eresus->db->updateItem($this->table['name'], $item, "`id`='".$item['id']."'");
		HTTP::redirect(arg('submitURL'));
	}
	//-----------------------------------------------------------------------------

	public function adminAddItem()
	{
	global $page;

		$form = array(
			'name' => 'AddForm',
			'caption' => '�������� ����������',
			'width'=>'100%',
			'fields' => array (
				array ('type' => 'hidden', 'name' => 'action', 'value' => 'insert'),
				array ('type' => 'edit', 'name' => 'caption', 'label' => '��������', 'width' => '100%', 'maxlength' => '255'),
				array ('type' => 'edit', 'name' => 'src', 'label' => '��� ��������', 'width' => '100%', 'maxlength' => '255', 'pattern' => '/.+/', 'errormsg' => '�� ������ ������� ����� � ���� "��� ��������"'),
				array ('type' => 'checkbox', 'name' => 're', 'label' => '���������� ���������'),
				array ('type' => 'edit', 'name' => 'dst', 'label' => '�� ��� ��������', 'width' => '100%', 'maxlength' => '255'),
			),
			'buttons' => array('ok', 'cancel'),
		);

		$result = $page->renderForm($form);
		return $result;
	}
	//-----------------------------------------------------------------------------

	public function adminEditItem()
	{
	global $Eresus, $page;

		$item = $Eresus->db->selectItem($this->table['name'], "`id`='".arg('id')."'");
		$form = array(
			'name' => 'EditForm',
			'caption' => '������������� ����������',
			'width' => '500px',
			'fields' => array (
				array('type'=>'hidden','name'=>'update', 'value'=>$item['id']),
				array ('type' => 'edit', 'name' => 'caption', 'label' => '��������', 'width' => '100%', 'maxlength' => '255'),
				array ('type' => 'edit', 'name' => 'src', 'label' => '��� ��������', 'width' => '100%', 'maxlength' => '255', 'pattern' => '/.+/', 'errormsg' => '�� ������ ������� ����� � ���� "��� ��������"'),
				array ('type' => 'checkbox', 'name' => 're', 'label' => '���������� ���������'),
				array ('type' => 'edit', 'name' => 'dst', 'label' => '�� ��� ��������', 'width' => '100%', 'maxlength' => '255'),
			),
			'buttons' => array('ok', 'apply', 'cancel'),
		);
		$result = $page->renderForm($form, $item);
		return $result;
	}
	//-----------------------------------------------------------------------------

	public function adminRender()
	{
		return $this->adminRenderContent();
	}
	//-----------------------------------------------------------------------------

	public function clientOnPageRender($text)
	{
		global $Eresus;

		$items = $Eresus->db->select($this->table['name'], '`active`=1', $this->table['sortMode'], $this->table['sortDesc']);
		if (count($items)) foreach ($items as $item) {
			if ($item['re'])
				$text = preg_replace($item['src'], $item['dst'], $text);
			else
				$text = str_replace($item['src'], $item['dst'], $text);
		}
		return $text;
	}
	//-----------------------------------------------------------------------------

	public function adminOnMenuRender()
	{
		global $page;

		$page->addMenuItem('����������', array ("access"  => EDITOR, "link"  => $this->name, "caption"  => $this->title, "hint"  => $this->description));
	}
	//-----------------------------------------------------------------------------
}
