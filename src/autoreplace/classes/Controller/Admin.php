<?php
/**
 * Контроллер АИ
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
 * Контроллер АИ
 *
 * @since x.xx
 */
class AutoReplace_Controller_Admin extends Eresus_Plugin_Controller_Admin_Content
{
    /**
     * Возвращает разметку списка замен
     *
     * @return string
     *
     * @since x.xx
     */
    protected function actionIndex()
    {
        $provider = new ORM_UI_List_DataProvider(ORM::getTable($this->getPlugin(), 'Replace'));
        $list = new UI_List($this->getPlugin(), $provider);
        $vars = array('list' => $list);

        /** @var TAdminUI $page */
        $page = Eresus_Kernel::app()->getPage();
        $vars['actionAdd'] = $page->url(array('action' => 'add'));

        $tmpl = $this->getPlugin()->templates()->admin('List.html');
        $html = $tmpl->compile($vars);

        return $html;
    }

    /**
     * Добавление замены
     *
     * @param Eresus_CMS_Request $request
     *
     * @return string|Eresus_HTTP_Response
     *
     * @since x.xx
     */
    protected function actionAdd(Eresus_CMS_Request $request)
    {
        $args = $request->getMethod() == 'POST' ? $request->request : $request->query;
        if ($request->getMethod() == 'POST')
        {
            $replace = new AutoReplace_Entity_Replace();
            $replace->caption = $args->get('caption');
            $replace->src = $args->get('src');
            $replace->dst = $args->get('dst');
            $replace->re = $args->getInt('re');
            $replace->active = true;
            $replace->getTable()->persist($replace);
            $url = Eresus_Kernel::app()->getPage()->url();
            return new Eresus_HTTP_Redirect($url);
        }
        $tmpl = $this->getPlugin()->templates()->admin('ReplaceDialog.html');
        $html = $tmpl->compile(array('mod' => $args->get('mod')));
        return $html;
    }
}

