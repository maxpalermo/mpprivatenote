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

use MpSoft\MpPrivateNotes\Models\ModelCustomerNote;

class MpPrivateNoteAjaxDispatcherModuleFrontController extends ModuleFrontController
{
    public function __construct()
    {
        $this->ajax = true;
    }

    public function display()
    {
        if (Tools::isSubmit('action')) {
            $action = Tools::getValue('action');
            if (method_exists($this, $action)) {
                $this->response($this->{$action}());
            }
        }

        $tpl = 'module:mpprivatenotes/views/templates/front/denied.tpl';
        $this->setTemplate($tpl);

        return parent::display();
    }

    protected function response($params)
    {
        header('Content-Type: application/json; charset=utf-8');
        ob_clean();
        exit(json_encode($params));
    }

    public function ajaxProcessDeleteCustomerNote()
    {
        $id_note = (int) Tools::getValue('id_note');
        $note = new CustomerMessages($id_note);
        $res = (int) $note->delete();
        die(Tools::jsonEncode(['result' => $res]));
    }

    public function ajaxProcessGetCustomerMessages()
    {
        $id_customer = (int) Tools::getValue('id_customer');
        $messages = MpCustomerNote::getCustomerMessages($id_customer);

        return ['result' => $messages];
    }

    public function ajaxProcessImportCustomerNote()
    {
        $id_employee = Context::getContext()->employee->id;
        $id_order = (int) Tools::getValue('id_order');
        $order = new Order($id_order);
        $id_customer = (int) $order->id_customer;
        $customer = new Customer($id_customer);
        $note = trim($customer->note);
        if ($note) {
            $message = new CustomerMessages();
            $message->id_employee = $id_employee;
            $message->id_customer = $id_customer;
            $message->message = $note;
            $message->date_add = '1970-01-01 00:00:00';
            $res = $message->add();
            if ($res) {
                die(Tools::jsonEncode(['result' => 1]));
            } else {
                die(Tools::jsonEncode(['result' => 0, 'error' => Db::getInstance()->getMsgError()]));
            }
        }
        die(Tools::jsonEncode(['result' => 0, 'error' => $this->l('No note to import.')]));
    }

    public function ajaxProcessSaveCustomerMessage()
    {
        $id_employee = (int) Tools::getValue('id_employee');
        $id_customer = (int) Tools::getValue('id_customer');
        $message = Tools::getValue('message');
        $obj = new CustomerMessages();
        $obj->id_employee = $id_employee;
        $obj->id_customer = $id_customer;
        $obj->message = $message;
        $obj->date_add = date('Y-m-d H:i:s');
        $res = (int) $obj->add();
        if ($res) {
            return ['result' => true];
        }

        return ['result' => false];
    }

    public function saveCustomerMessage()
    {
        $id_employee = (int) Tools::getValue('id_employee');
        $id_customer = (int) Tools::getValue('id_customer');
        $message = Tools::getValue('message');
        $obj = new ModelCustomerNote();
        $obj->id_employee = $id_employee;
        $obj->id_customer = $id_customer;
        $obj->message = $message;
        $obj->date_add = date('Y-m-d H:i:s');

        try {
            $res = (int) $obj->add();
        } catch (\Throwable $th) {
            $res = false;
            $error = $th->getMessage();
        }
        if ($res) {
            return ['result' => true];
        }

        return ['result' => false, 'error' => $error];
    }
}
