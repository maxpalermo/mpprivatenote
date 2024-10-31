<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Massimiliano Palermo <maxx.palermo@gmail.com>
 * @copyright Since 2016 Massimiliano Palermo
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/vendor/autoload.php';

use MpSoft\MpPrivateNotes\Install\InstallMenu;
use MpSoft\MpPrivateNotes\Install\InstallTable;
use MpSoft\MpPrivateNotes\Models\ModelCustomerNote;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class MpPrivateNotes extends Module implements WidgetInterface
{
    protected $controller_name;

    public function __construct()
    {
        $this->name = 'mpprivatenotes';
        $this->tab = 'administration';
        $this->version = '2.0.1';
        $this->author = 'Massimiliano Palermo';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '8.0', 'max' => _PS_VERSION_];
        $this->bootstrap = true;
        $this->module_key = '';

        parent::__construct();

        $this->displayName = $this->l('Note private cliente');
        $this->description = $this->l('gestisce le note private ai clienti');
        $this->confirmUninstall = $this->l('Sei sicuro di voler disinstallare questo modulo?');
        $this->controller_name = 'AdminMpPrivateNotes';
    }

    public function install()
    {
        $installMenu = new InstallMenu($this);
        $installTable = new InstallTable($this);

        $hooks = [
            'displayAfterDescriptionShort',
            'actionAdminControllerSetMedia',
            'displayAdminOrder',
            'displayAdminOrderCreateExtraButtons',
            'displayAdminOrderSide',
            'displayAdminOrderTop',
            'displayAdminOrderBottom',
            'actionGetAdminToolbarButtons',
        ];
        $res = parent::install() && $this->registerHook($hooks);

        $menu = $installMenu->installMenu(
            $this->controller_name,
            'Messaggi privati clienti',
            'AdminParentCustomer',
            'mail_lock',
        );
        $table = $installTable->installFromSqlFile('table');

        return $res && $menu && $table;
    }

    public function uninstall()
    {
        $installMenu = new InstallMenu($this);

        return parent::uninstall() && $installMenu->uninstallMenu($this->controller_name);
    }

    public function hookActionAdminControllerSetMedia($params)
    {
        $path = 'module:mpprivatenote/views/css/button-style.css';
        $this->context->controller->addCss($path, 'all');
    }

    public function hookActionGetAdminToolbarButtons(&$params)
    {
        $currentUrl = $_SERVER['REQUEST_URI'];
        if (strpos($currentUrl, '/sell/orders/') === false) {
            return;
        }
        if (Tools::strtoupper($this->context->controller->controller_name) == 'ADMINORDERS') {
            // Get the collection of toolbar buttons
            $toolbar = $params['toolbar_extra_buttons_collection'];

            // Create a new button
            $newButton = new \PrestaShop\PrestaShop\Core\Action\ActionsBarButton(
                'btn-secondary', // CSS class for the button
                ['href' => 'javascript:showModalPrivateNotes();'], // Link where the button redirects
                $this->trans('Messaggi privati') // Text displayed on the button
            );

            // Add the new button to the collection
            $toolbar->add($newButton);
        }
    }

    public function renderWidget($hookName, array $configuration)
    {
        switch ($hookName) {
            case 'displayAdminOrder':
                $tpl_file = 'module:mpprivatenotes/views/templates/admin/modal-panel.tpl';
                $tpl = $this->context->smarty->createTemplate($tpl_file);
                $id_order = (int) $configuration['id_order'];
                $order = new Order($id_order);
                if (!Validate::isLoadedObject($order)) {
                    return '';
                }
                $tpl->assign(
                    [
                        'id_order' => $order->id,
                        'id_customer' => $order->id_customer,
                        'id_employee' => $this->context->employee->id,
                        'admin_link' => $this->context->link->getAdminLink('AdminMpPrivateNotes'),
                        'customerNotes' => ModelCustomerNote::getCustomerMessages($order->id_customer),
                    ]
                );

                if (!$tpl->isCached($tpl_file, $tpl->getCacheId('mpprivatenotes'))) {
                    return $tpl->fetch();
                } else {
                    return $tpl->fetch($tpl_file, $tpl->getCacheId('mpprivatenotes'));
                }
            case 'displayAdminOrderCreateExtraButtons':
                break;
            case 'displayAdminOrderBottom':
                break;
            default:
                return '';
        }
    }

    public function getWidgetVariables($hookName, array $configuration)
    {
        switch ($hookName) {
            case 'displayAdminOrder':
                $vars = $this->hookDispatchVariables((int) $configuration['id_product']);

                return $vars;
            default:
                return [];
        }
    }

    public function hookDispatchVariables($id_product)
    {
        return '';
    }
}
